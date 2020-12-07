<?php

namespace App\Http\Controllers\API\Exams;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Exam;
use App\Models\Marks;
use App\Models\Session;
use App\Models\Students;
use App\Models\Subjects;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Marks";
        if ($user->can($permission)) {
            $request->validate([
                "exam_id" => "required|numeric"
            ]);
            $query = [];

            $query["exam_id"] = $request->exam_id;

            if ($request->student_id != null) {
                $query["student_id"] = $request->student_id;
            }

            if (count($query) > 0)
                return Marks::where([[$query]])->get();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Add Marks";
        if ($user->can($permission)) {
            $request->validate([
                "student_info" => "required|json",
                "exam_id" => "required|numeric",
                "subject_id" => "required|numeric"
            ]);
            $student_info = json_decode($request->student_info);
            $exam_id = $request->exam_id;
            $subject_id = $request->subject_id;
            $data = [];

            if (Exam::find($exam_id) == null)
                return ResponseMessage::fail("Exam Doesn't Exist!");
            if (Subjects::find($subject_id) == null)
                return ResponseMessage::fail("Subject Doesn't Exist!");

            foreach ($student_info as $std_info) {
                if ($std_info->student_id == null && $std_info->subject_type == null && $std_info->marks == null)
                    return ResponseMessage::fail("Invalid Data!");
                $student_id = $std_info->student_id;
                $subject_type = $std_info->subject_type;

                if (Students::find($student_id) == null)
                    return ResponseMessage::fail("Some Students Don't Exist!");

                $student_marks = Marks::where(["exam_id" => $exam_id, "student_id" => $student_id, "subject_id" => $subject_id])->first();
                if ($student_marks != null) {
                    $student_marks->exam_id = $exam_id;
                    $student_marks->student_id = $student_id;
                    $student_marks->subject_id = $subject_id;
                    $student_marks->marks = $std_info->marks;
                    if ($student_marks->save())
                        continue;
                    else
                        return ResponseMessage::fail("Something Went Wrong Couldn't Update!");
                }
                array_push($data, ["exam_id" => $exam_id, "subject_id" => $subject_id, "student_id" => $student_id, "subject_type" => $subject_type, "marks" => json_encode(
                    $std_info->marks
                )]);
            }
            if (Marks::insert($data)) {
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
