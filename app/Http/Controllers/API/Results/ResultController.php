<?php

namespace App\Http\Controllers\API\Results;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Marks;
use App\Models\ResultHasExam;
use App\Models\Results;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\StudentResultReport;
use App\Models\Students;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Result";
        if ($user->can($permission)) {
            $request->validate([
                "result_id" => "required|numeric"
            ]);

            return Results::find($request->result_id);
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Result";
        if ($user->can($permission)) {
            $request->validate([
                "result_name" => "required|string",
                "exams" => "required|json",
                "class_id" => "required|numeric",
                "department_id" => "required|numeric",
                "session_id" => "required|numeric",
            ]);

            if (SchoolClass::find($request->class_id) == null)
                return ResponseMessage::fail("Class Doesn't Exist!");

            if (Department::find($request->department_id) == null)
                return ResponseMessage::fail("Department Doesn't Exist!");

            if (Session::find($request->session_id) == null)
                return ResponseMessage::fail("Session Doesn't Exist!");




            $result = new Results;
            $result->result_name = $request->result_name;
            $result->class_id = $request->class_id;
            $result->department_id = $request->department_id;
            $result->session_id = $request->session_id;

            if ($result->save()) {
                $exams = json_decode($request->exams);
                $result_has_exams = [];
                foreach ($exams as $exam) {
                    $exam_query = Exam::find($exam->exam_id);
                    if ($exam_query == null) {
                        if ($exam_query->class_id != $request->class_id && $exam_query->department_id != $request->department_id && $exam_query->session_id != $request->session_id)
                            return ResponseMessage::fail("Some Exams Don't Exist!");
                    }
                    array_push($result_has_exams, ["result_id" => $result->id, "exam_id" => $exam->exam_id, "exam_percentage" => $exam->exam_percentage]);
                }

                $students = ClassHasStudents::select('student_id')->where(["class_id" => $request->class_id, "department_id" => $request->department_id])->get();
                $student_result_report = [];
                foreach ($students as $student) {
                    array_push($student_result_report, ["result_id" => $result->id, "student_id" => $student->student_id, "result_status" => 0, "result_remarks" => ""]);
                }
                if (ResultHasExam::insert($result_has_exams)) {
                    if (StudentResultReport::insert($student_result_report))
                        return ResponseMessage::success("Result Added Successfully!");
                    else
                        return ResponseMessage::success("Failed To Add Student Result Report!");
                } else
                    return ResponseMessage::success("Failed To Add Exam To Result!");
            } else {
                return ResponseMessage::fail("Failed To Add Result!");
            }
        }
    }


    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Marks";
        if ($user->can($permission)) {
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
