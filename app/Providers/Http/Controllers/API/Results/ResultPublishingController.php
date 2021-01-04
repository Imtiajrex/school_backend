<?php

namespace App\Http\Controllers\API\Exams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Marks;
use App\Models\Results;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Students;
use Illuminate\Http\Request;

class ResultPublishingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Result";
        if ($user->can($permission)) {
            $request->validate([
                "result_id" => "required|numeric"
            ]);

            return Results::find($request->result_id)->get();
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

            if (Session::find($request->session) == null)
                return ResponseMessage::fail("Session Doesn't Exist!");

            $result = new Results;
            $result->result_name = $request->result_name;
            $result->class_id = $request->class_id;
            $result->department_id = $request->department_id;
            $result->session_id = $request->session_id;

            if ($result->save()) {
                $exams = json_decode($request->exams);
                foreach ($exams as $exam) {
                    if (Exam::find($exam->exam_id) == null)
                        return ResponseMessage::fail("Some Exams Don't Exist!");
                }
                return ResponseMessage::success("Marks Added!");
            } else {
                return ResponseMessage::fail("Failed To Add Marks!");
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
