<?php

namespace App\Http\Controllers\API\Students;

use App\Models\StudentsAttendance;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\Session;
use App\Models\StudentAttendanceTime;
use App\Models\Students;
use Illuminate\Support\Facades\DB;

class StudentsAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Student Attendance";
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $request->validate(["date" => "required|date"]);
            $from = $request->date;
            $to = $request->date;
            $query = [];
            if ($request->session_id) {
                $query["class_has_students.session_id"] = $request->session_id;

                if ($request->class_id) {
                    $query["class_has_students.class_id"] = $request->class_id;

                    if ($request->department_id) {
                        $query["class_has_students.department_id"] = $request->department_id;
                    }
                }
            }

            if ($request->student_id) {
                $student_id = Students::where("student_id", $request->student_id)->first();
                if ($student_id)
                    $query["class_has_students.student_id"] = $student_id->id;
            }

            if (count($query) != 0) {
                $start_time = StudentAttendanceTime::where(["session_id" => $request->session_id, "class_id" => $request->class_id])->first();
                $start_time = $start_time != null ? $start_time->start_time : "23:59:00";
                $students =  ClassHasStudents::where($query);
                $students = $students->leftJoin("class", "class_has_students.class_id", "=", "class.id")->leftJoin("session", "class_has_students.session_id", "=", "session.id")->leftJoin("department", "class_has_students.department_id", "=", "department.id")->leftJoin("students", "class_has_students.student_id", "=", "students.id");
                $students = $students->leftJoin("students_attendance", function ($join) use ($from, $to) {
                    $join->on("students_attendance.student_id", "=", "students.id")->whereBetween("students_attendance.date", [$from, $to]);
                });
                if ($request->order_by == "Student")
                    $students =  $students->orderBy('role', 'asc')->orderBy('students.student_id', 'asc');
                else
                    $students =  $students->orderBy('role', 'asc')->orderBy('students_attendance.date', 'asc');
                if ($request->group) {
                    $students = $students->groupBy("class_has_students.student_id");
                    $students->selectRaw("class_has_students.*, class.name as class, session.*, department.name as department, students.student_name, students.student_id as student_identifier,students_attendance.date,students_attendance.access_time,case when students_attendance.access_time is not null then case when students_attendance.access_time> '$start_time' then 'Late' else 'Present' end  else 'Absent' end as attendance_status,group_concat(access_time,'\n') as access_time_group");
                    return $students->get();
                }
                $students->selectRaw("class_has_students.*, class.name as class, session.*, department.name as department, students.student_name, students.student_id as student_identifier,students_attendance.date,students_attendance.access_time,case when students_attendance.access_time is not null then case when students_attendance.access_time> '$start_time' then 'Late' else 'Present' end  else 'Absent' end as attendance_status");
                return $students->get();
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function getMonthlyAttendance(Request $request)
    {

        $user = $request->user();
        $permission = "View Student Attendance";
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $request->validate(["month" => "required", "year" => "required"]);
            $year = Session::find($request->year)->session;

            $query = [];
            if ($request->session_id) {
                $query["class_has_students.session_id"] = $request->session_id;

                if ($request->class_id) {
                    $query["class_has_students.class_id"] = $request->class_id;

                    if ($request->department_id) {
                        $query["class_has_students.department_id"] = $request->department_id;
                    }
                }
            }

            if ($request->student_id) {
                $student_id = Students::where("student_id", $request->student_id)->first();
                if ($student_id)
                    $query = ["class_has_students.student_id" => $student_id->id];
            }

            if (count($query) != 0) {
                $start_time = StudentAttendanceTime::where(["session_id" => $request->session_id, "class_id" => $request->class_id])->first();
                $start_time = $start_time != null ? $start_time->start_time : "23:59:00";
                $students =  ClassHasStudents::where($query);

                $students = $students->leftJoin("class", "class_has_students.class_id", "=", "class.id");
                $students = $students->leftJoin("session", "class_has_students.session_id", "=", "session.id");
                $students = $students->leftJoin("department", "class_has_students.department_id", "=", "department.id");
                $students = $students->leftJoin("students", "class_has_students.student_id", "=", "students.id");
                $students = $students->get(["class_has_students.student_id", "students.student_id as student_identifier", "students.student_name"]);
                $data = [];
                foreach ($students as $student) {
                    $std_att = StudentsAttendance::where('student_id', $student->student_id)->whereYear('date', '=', $year)->whereMonth('date', '=', $request->month);
                    $std_att = $std_att->groupBy("date");
                    $std_att = $std_att->selectRaw("date,case when students_attendance.access_time is not null then case when TIMEDIFF(students_attendance.access_time, '$start_time')>0 then 'Late' else 'Present' end  else 'Absent' end as attendance_status,group_concat(access_time,'\n') as access_time_group")->get();

                    $att = [];
                    foreach ($std_att as $student_attendance) {
                        $d = explode('-', $student_attendance->date);
                        $d = $d[2] . '/' . $d[1] . '/' . $d[0];
                        $att[$d] = $student_attendance->attendance_status . "
" . $student_attendance->access_time_group;
                    }
                    $data[$student->student_identifier . '
' . $student->student_name] = $att;
                }
                return $data;
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function markAttendance(Request $request)
    {

        $user = $request->user();
        $permission = "Assign Student Attendance";
        if ($user->can($permission)) {

            $request->validate([
                "ids" => "required",
                "date" => "required|date",
            ]);
            if ($request->absent) {

                if (StudentsAttendance::whereIn("student_id", $request->ids)->where("date", $request->date)->where("manual", true)->delete()) {
                    return ResponseMessage::success("Student Attendance Manually Absent Assigned!");
                } else {
                    return ResponseMessage::fail("Couldn't Manually Assign Absent Student Attendance!");
                }
            }

            $data = [];
            foreach ($request->ids as $id) {
                array_push($data, ["date" => $request->date, "student_id" => $id, "access_time" => date('H:i:s'), "manual" => true]);
            }
            if (StudentsAttendance::insert($data)) {
                return ResponseMessage::success("Student Attendance Manually Assigned!");
            } else {
                return ResponseMessage::fail("Couldn't Manually Assign Student Attendance!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function getManualAttendance(Request $request)
    {

        $user = $request->user();
        $permission = "View Student Attendance";
        if ($user->can($permission)) {
            $request->validate(["date" => "required|date"]);
            $from = $request->date;
            $to = $request->date;
            $query = [];
            if ($request->session_id) {
                $query["class_has_students.session_id"] = $request->session_id;

                if ($request->class_id) {
                    $query["class_has_students.class_id"] = $request->class_id;

                    if ($request->department_id) {
                        $query["class_has_students.department_id"] = $request->department_id;
                    }
                }
            }
            if (count($query) > 0) {
                $students = ClassHasStudents::where($query)->leftJoin("students_attendance",  function ($join) use ($from, $to) {
                    $join->on('students_attendance.student_id', '=', 'class_has_students.student_id')->where("students_attendance.manual", true)->whereBetween("students_attendance.date", [$from, $to]);
                });
                $students = $students->leftJoin("class", "class.id", "=", "class_has_students.class_id");
                $students = $students->leftJoin("department", "department.id", "=", "class_has_students.department_id");
                $students = $students->leftJoin("session", "session.id", "=", "class_has_students.session_id");
                $students = $students->leftJoin("students", "students.id", "=", "class_has_students.student_id");
                $students = $students->orderBy("role");
                return $students->selectRaw("students.student_name,class.name as class,department.name as department,session.session,case when students_attendance.access_time is not null then 'Present' else 'Absent' end as attendance_status,date,role,students.student_id as student_identifier,students.id")->get();
            } else return [];
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Assign Student Attendance";
        if ($user->can($permission)) {
            $request->validate([
                "student_id" => "required|numeric",
                "date" => "required|date",
                "access_time" => "required|string"
            ]);
            $attendance = new StudentsAttendance;
            $attendance->student_id = $request->student_id;
            $attendance->date = $request->date;
            $attendance->access_time = $request->access_time;

            if (Students::find($request->student_id) != null) {
                if ($attendance->save()) {
                    return ResponseMessage::success("Student Access Time Assigned!");
                }
            } else {
                return ResponseMessage::fail("Student Not Found!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {

        $user = $request->user();
        $permission = "Delete Student Attendance";
        if ($user->can($permission)) {
            $attendance = StudentsAttendance::find($id);
            if ($attendance != null) {
                if ($attendance->delete()) {
                    return ResponseMessage::success("Student Access Time Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't delete record!");
                }
            } else {
                return ResponseMessage::fail("Attendance Record doesn't exist!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
}
