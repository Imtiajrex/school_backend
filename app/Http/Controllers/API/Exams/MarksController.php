<?php

namespace App\Http\Controllers\API\Exams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\Exam;
use App\Models\Gpa;
use App\Models\Marks;
use App\Models\Session;
use App\Models\Students;
use App\Models\Subjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Marks";
        if ($user->can($permission) || $user->user_type == "teacher" || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $request->validate([
                "exam_id" => "required|numeric"
            ]);
            $query = [];

            $query["exam_id"] = $request->exam_id;


            if (count($query) > 0) {
                $marks = Marks::where([$query]);
                $marks = $marks->leftJoin("class_has_students", "class_has_students.id", "=", "marks.student_id");
                $marks = $marks->leftJoin("students", "students.id", "=", "class_has_students.student_id");
                $marks = $marks->orderBy("class_has_students.role", 'asc');
                $marks = $marks->get(["class_has_students.role", "class_has_students.student_identifier", "students.student_name", "marks.*"]);
                return $marks;
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function getMarks(Request $request)
    {

        $user = $request->user();
        $permission = "View Marks";
        if ($user->can($permission) || $user->user_type == "teacher" || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $request->validate([
                "class_id" => "required|numeric",
                "session_id" => "required|numeric",
                "department_id" => "required|numeric",
                "exam_id" => "required|numeric",
                "subject_id" => "required|numeric",
            ]);
            $exam_id = $request->exam_id;
            $subject_id = $request->subject_id;
            $query = ["class_id" => $request->class_id, "session_id" => $request->session_id, "department_id" => $request->department_id];
            $students = ClassHasStudents::where($query);
            $students = $students->leftJoin("students", "class_has_students.student_id", '=', 'students.id');
            $students = $students->leftJoin("marks", function ($join) use ($exam_id, $subject_id) {
                $join->on("class_has_students.id", "=", "marks.student_id")->where(["exam_id" => $exam_id, "subject_id" => $subject_id]);
            });
            $students = $students->orderBy("role", 'asc');
            return $students->get(["students.student_name", "class_has_students.student_identifier", "marks.marks", "class_has_students.id as student_id", "marks.id", "class_has_students.role"]);
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function getMarkStructure(Request $request)
    {
        $user = $request->user();
        $permission = "View Marks";
        if ($user->can($permission) || $user->user_type == "teacher" || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $request->validate([
                "exam_id" => "required|numeric",
            ]);
            if ($request->exam) {
                $exam_id = $request->exam_id;
                $exam_subjects = json_decode(Exam::find($exam_id)->subjects);
                $subs = Subjects::whereIn("subjects.id", $exam_subjects);
                $subs = $subs->leftJoin("marks_structure", function ($join) use ($exam_id) {
                    $join->on("marks_structure.subject_id", "=", "subjects.id")->where("exam_id", $exam_id);
                });
                $subs = $subs->orderBy("subject_id")->get(["marks_structure.*", "subjects.*"]);
                return $subs;
            }
            if ($request->subject_id) {
                $query = ["exam_id" => $request->exam_id, "subject_id" => $request->subject_id];
                $mark_structure = DB::table("marks_structure")->where($query)->first();
                return [$mark_structure];
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Add Marks";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate([
                "class_id" => "required|numeric",
                "session_id" => "required|numeric",
                "department_id" => "required|numeric",
                "exam_id" => "required|numeric",
                "subject_id" => "required|numeric",
                "subject_id" => "required|numeric",
                "total_exam_mark" => "required|numeric",
                "mark_structure" => "required",
                "mark_data" => "required"
            ]);
            $mark_data = $request->mark_data;

            $exam_id = $request->exam_id;
            $subject_id = $request->subject_id;
            $mark_structure = json_encode($request->mark_structure);

            if (Exam::find($exam_id) == null)
                return ResponseMessage::fail("Exam Doesn't Exist!");
            if (Subjects::find($subject_id) == null)
                return ResponseMessage::fail("Subject Doesn't Exist!");
            $marks = [];
            $gpa_arr = Gpa::get();
            // $to_find = [];
            // $to_update = [];
            $err = 0;
            foreach ($mark_data as $m_d) {
                $m_d["exam_id"] = $exam_id;
                $m_d["subject_id"] = $subject_id;
                $gpa = 0;
                $mark = ($m_d["total_mark"] / $request->total_exam_mark) * 100;
                foreach ($gpa_arr as $g) {
                    if ($mark >= $g["starting_number"] && $mark <= $g["ending_number"]) {
                        $gpa = $g["gpa"];
                        break;
                    }
                }
                $m_d["gpa"] = $gpa;
                $m_d["marks"] = json_encode($m_d["marks"]);

                $std_spc_mark = Marks::where(["exam_id" => $exam_id, "student_id" => $m_d["student_id"], "subject_id" => $subject_id])->first();
                if($std_spc_mark!=null)
                    $mark_op = Marks::where(["id"=>$std_spc_mark->id])->update(["absent" => $m_d["absent"], "total_mark" => $m_d["total_mark"], "gpa" => $gpa, "subject_type" => $m_d["subject_type"], "marks" => $m_d["marks"]]);
                else
                    $mark_op = Marks::insert(["exam_id" => $exam_id, "student_id" => $m_d["student_id"], "subject_id" => $subject_id,"absent" => $m_d["absent"], "total_mark" => $m_d["total_mark"], "gpa" => $gpa, "subject_type" => $m_d["subject_type"], "marks" => $m_d["marks"]]);
                
                if(!$mark_op) $err++;
                // array_push($to_find, ["exam_id" => $exam_id, "student_id" => $m_d["student_id"], "subject_id" => $subject_id]);
                // array_push($marks, $m_d);
            }
            // $i = 0;
            // while ($i < count($to_find)) {

            //     Marks::updateOrInsert($to_find[$i], $to_update[$i]);
            //     $i++;
            // }
            $mark_struct = DB::table('marks_structure')->where(["exam_id" => $exam_id, "subject_id" => $subject_id])->first();
            if($mark_struct != null)
            DB::table('marks_structure')->where(["exam_id" => $exam_id, "subject_id" => $subject_id])->update(["exam_id" => $exam_id, "subject_id" => $subject_id, "total_exam_mark" => $request->total_exam_mark, "structure" => $mark_structure]);
            else DB::table('marks_structure')->insert(["exam_id" => $exam_id, "subject_id" => $subject_id,"exam_id" => $exam_id, "subject_id" => $subject_id, "total_exam_mark" => $request->total_exam_mark, "structure" => $mark_structure]);

            if($err > 0) return ResponseMessage::fail("Failed To Add Marks!");
            

            return ResponseMessage::success("Marks Added!");
        }
    }


    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Marks";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $exam = Marks::find($id);
            if ($exam != null) {
                if ($exam->delete()) {
                    return ResponseMessage::success("Mark Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Mark!");
                }
            } else {
                return ResponseMessage::fail("Mark Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
