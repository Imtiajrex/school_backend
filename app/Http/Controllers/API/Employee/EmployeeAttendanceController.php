<?php

namespace App\Http\Controllers\API\Employee;

use App\Models\EmployeeAttendance;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Employee;
use App\Models\EmployeeAttendanceTime;
use App\Models\Session;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Employee Attendance";
        if ($user->can($permission) || $user->user_type == "teacher") {
            if ($request->date) {
                $date = $request->date;
            } else {
                return [];
            }
            $query = [];
            if ($request->employee_type) {
                $query["employee.employee_type"] = $request->employee_type;
            }
            if ($request->employee_id) {
                $query = ["employee.employee_id" => $request->employee_id];
            }

            if (count($query) != 0) {
                $start_time = EmployeeAttendanceTime::find(1)->start_time;

                $employee =  Employee::where($query);
                $employee = $employee->leftJoin("employee_attendance", function ($join) use ($date) {
                    $join->on("employee_attendance.employee_id", "=", "employee.id")->where("employee_attendance.date", $date);
                });

                if ($request->group) {
                    $employee = $employee->groupBy("employee.employee_id");
                    $employee = $employee->selectRaw("employee.employee_name,employee.employee_type, employee.employee_id,employee_attendance.date,employee_attendance.access_time,case when employee_attendance.access_time is not null then case when TIMEDIFF(employee_attendance.access_time, '$start_time')>0 then 'Late' else 'Present' end else 'Absent' end as attendance_status,group_concat(access_time,'\n') as access_time_group");
                    return $employee->get();
                }
                $employee =  $employee->orderBy('employee_attendance.date', 'asc');
                $employee->selectRaw("employee.employee_name,employee.employee_type, employee.employee_id,employee_attendance.date,employee_attendance.access_time,case when employee_attendance.access_time is not null then case when TIMEDIFF(employee_attendance.access_time, '$start_time')>0 then 'Late' else 'Present' end else 'Absent' end as attendance_status");
                return $employee->get();
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function getMonthlyAttendance(Request $request)
    {

        $user = $request->user();
        $permission = "View Employee Attendance";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate(["month" => "required", "year" => "required"]);
            $year = Session::find($request->year)->session;
            $query = [];
            if ($request->employee_type) {
                $query["employee.employee_type"] = $request->employee_type;
            }
            if ($request->employee_id) {
                $query = ["employee.employee_id" => $request->employee_id];
            }
            if (count($query) != 0) {
                $start_time = EmployeeAttendanceTime::find(1)->start_time;
                $employee =  Employee::where($query)->get();
                $data = [];
                foreach ($employee as $employee) {
                    $em_att = EmployeeAttendance::where('employee_id', $employee->id)->whereYear('date', '=', $year)->whereMonth('date', '=', $request->month);
                    $em_att = $em_att->groupBy("date");
                    $em_att = $em_att->selectRaw("date,case when employee_attendance.access_time is not null then case when TIMEDIFF(employee_attendance.access_time, '$start_time')>0 then 'Late' else 'Present' end else 'Absent' end as attendance_status,group_concat(access_time,'\n') as access_time_group")->get();

                    $att = [];
                    foreach ($em_att as $employee_attendance) {
                        $d = explode('-', $employee_attendance->date);
                        $d = $d[2] . '/' . $d[1] . '/' . $d[0];
                        $att[$d] = $employee_attendance->attendance_status . "
" . $employee_attendance->access_time_group;
                    }
                    $data[$employee->employee_id . '
' . $employee->employee_name] = $att;
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
        $permission = "Assign Employee Attendance";
        if ($user->can($permission)) {

            $request->validate([
                "ids" => "required",
                "date" => "required|date",
            ]);
            if ($request->absent) {

                if (EmployeeAttendance::whereIn("employee_id", $request->ids)->where("date", $request->date)->where("manual", true)->delete()) {
                    return ResponseMessage::success("Student Attendance Manually Absent Assigned!");
                } else {
                    return ResponseMessage::fail("Couldn't Manually Assign Absent Student Attendance!");
                }
            }

            $data = [];
            foreach ($request->ids as $id) {
                array_push($data, ["date" => $request->date, "employee_id" => $id, "access_time" => date('H:i:s'), "manual" => true]);
            }
            if (EmployeeAttendance::insert($data)) {
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
        $permission = "View Employee Attendance";
        if ($user->can($permission)) {
            $request->validate(["date" => "required|date"]);
            $from = $request->date;
            $to = $request->date;
            $query = [];
            if ($request->employee_type) {
                $query["employee_type"] = $request->employee_type;
            }
            if (count($query) > 0) {
                $employee = Employee::where($query)->leftJoin("employee_attendance",  function ($join) use ($from, $to) {
                    $join->on('employee_attendance.employee_id', '=', 'employee.id')->where("employee_attendance.manual", true)->whereBetween("employee_attendance.date", [$from, $to]);
                });
                return $employee->selectRaw("employee.employee_name,case when employee_attendance.access_time is not null then 'Present' else 'Absent' end as attendance_status,date,employee.employee_id,employee.id")->get();
            } else return [];
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Assign Employee Attendance";
        if ($user->can($permission)) {
            $request->validate([
                "employee_id" => "required|numeric",
                "date" => "required|date",
                "access_time" => "required|string"
            ]);
            $attendance = new EmployeeAttendance;
            $attendance->employee_id = $request->employee_id;
            $attendance->date = $request->date;
            $attendance->access_time = $request->access_time;

            if (Employee::find($request->employee_id) != null) {
                if ($attendance->save()) {
                    return ResponseMessage::success("Employee Access Time Assigned!");
                }
            } else {
                return ResponseMessage::fail("Employee Not Found!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {

        $user = $request->user();
        $permission = "Delete Employee Attendance";
        if ($user->can($permission)) {
            $attendance = EmployeeAttendance::find($id);
            if ($attendance != null) {
                if ($attendance->delete()) {
                    return ResponseMessage::success("Employee Access Time Deleted!");
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
