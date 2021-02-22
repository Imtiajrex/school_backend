<?php

namespace App\Http\Controllers\API\Exams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Marks;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Subjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Exam";
        if ($user->can($permission) || $user->user_type == "teacher") {
            if ($request->exam_id != null) {
                $xm =  Exam::find($request->exam_id);
                if($request->admit_card){
                    $xm["subjects"] = Subjects::whereIn("id",json_decode($xm['subjects'],true));
                }
                return $xm;
            }
            $query = [];
            if ($request->class_id != null && $request->department_id != null && $request->session_id != null) {
                $query["exam.class_id"] = $request->class_id;
                $query["exam.department_id"] = $request->department_id;
                $query["exam.session_id"] = $request->session_id;
            }
            if (count($query) > 0) {
                $exam_data = Exam::where([[$query]]);
                if ($request->result) {
                    $exam_data = $exam_data->get(['exam.id', 'exam.exam_name as name']);
                } else {
                    $exam_data = $exam_data->leftJoin('class', "class.id", '=', 'exam.class_id');
                    $exam_data = $exam_data->leftJoin('department', "department.id", '=', 'exam.department_id');
                    $exam_data = $exam_data->leftJoin('session', "session.id", '=', 'exam.session_id');
                    $exam_data = $exam_data->get(['exam.*', 'session.session', 'department.name as department', 'class.name as class']);

                    foreach ($exam_data as $exm) {
                        $subject_names = [];
                        foreach (json_decode($exm->subjects) as $sub) {
                            array_push($subject_names, Subjects::find($sub)->subject_name);
                        }
                        $exm["subject_names"] = implode(", ", $subject_names);
                    }
                }
                return $exam_data;
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function getExamSubjects(Request $request)
    {
        $user = $request->user();
        $permission = "View Exam";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate(["exam_id" => "required", "class_id" => "required", "department_id" => "required", "session_id" => "required"]);
            $exam_data = Exam::find($request->exam_id);
            $exam_subject = [];
            $class = SchoolClass::find($request->class_id);
            $session = Session::find($request->session_id);
            $department = Department::find($request->department_id);
            $s = json_decode($exam_data->subjects);
            foreach ($s as $sub) {
                $subject = Subjects::find($sub);
                if ($subject != null)
                    array_push($exam_subject, ["subject" => $subject->subject_name, "subject_id" => $sub, "exam" => $exam_data->exam_name, "exam_id" => $exam_data->id, "class" => $class->name, "session" => $session->session, "department" => $department->name, "class_id" => $request->class_id, "department_id" => $request->department_id, "session_id" => $request->session_id]);
            }
            return $exam_subject;
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Exam";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate([
                "exam_name" => "required|string",
                "class_id" => "required|numeric",
                "department_id" => "required|numeric",
                "session_id" => "required|numeric",
                "subjects" => "required|json",
            ]);


            $exam = new Exam;
            $exam->exam_name = $request->exam_name;
            $exam->class_id = $request->class_id;
            $exam->department_id = $request->department_id;
            $exam->session_id = $request->session_id;
            $subjects = json_decode($request->subjects);
            foreach ($subjects as $subject) {
                if (Subjects::find($subject) == null)
                    return ResponseMessage::fail("Some Subjects Don't Exist!");
            }

            $exam->subjects = json_encode($subjects);

            if (SchoolClass::find($request->class_id) == null)
                return ResponseMessage::fail("Class Doesn't Exist!");

            if (Department::find($request->department_id) == null)
                return ResponseMessage::fail("Department Doesn't Exist!");

            if (Session::find($request->session_id) == null)
                return ResponseMessage::fail("Session Doesn't Exist!");


            if ($exam->save()) {
                return ResponseMessage::success("Exam Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Create Exam!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Exam";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate([
                "exam_name" => "required|string",
                "subjects" => "required|json",
            ]);
            $exam = Exam::find($id);
            if ($exam != null) {
                $exam->exam_name = $request->exam_name;
                $subjects = json_decode($request->subjects);
                $prev_subject = json_decode($exam->subjects);
                foreach ($subjects as $subject) {
                    if (Subjects::find($subject) == null)
                        return ResponseMessage::fail("Some Subjects Don't Exist!");
                }
                $toDelete = array_diff($prev_subject, $subjects);

                $exam->subjects = $subjects;


                if ($exam->save()) {
                    if (count($toDelete) > 0) {
                        DB::table('marks')->whereIn("subject_id", $toDelete)->where("exam_id", $id)->delete();
                    }
                    return ResponseMessage::success("Exam Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Exam!");
                }
            } else
                return ResponseMessage::fail("Exam Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Exam";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $exam = Exam::find($id);
            if ($exam != null) {
                $marks = Marks::where("exam_id", $id)->get();
                if ($marks != null) {
                    Marks::where("exam_id", $id)->delete();
                }

                if ($exam->delete()) {
                    return ResponseMessage::success("Exam Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Exam!");
                }
            } else {
                return ResponseMessage::fail("Exam Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
