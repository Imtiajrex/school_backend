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
        if ($user->can($permission)) {
            if ($request->exam_id != null)
                return Exam::find($request->exam_id);
            $query = [];
            if ($request->class_id != null) {
                $query["class_id"] = $request->class_id;
            }
            if ($request->department_id != null) {
                $query["department_id"] = $request->department_id;
            }
            if ($request->session_id != null) {
                $query["session_id"] = $request->session_id;
            }
            if (count($query) > 0)
                return Exam::where([[$query]])->get();

            return Exam::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Exam";
        if ($user->can($permission)) {
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
        if ($user->can($permission)) {
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
                $toDelete = [];//array_diff($prev_subject, $subjects);

                $exam->subjects = $subjects;


                if ($exam->save()) {
                    echo count($toDelete);
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
        if ($user->can($permission)) {
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
