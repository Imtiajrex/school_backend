<?php

namespace App\Http\Controllers\API\Results;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\StudentResultReport;
use Illuminate\Http\Request;

class ResultPublishingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Result Publication Status";
        if ($user->can($permission)) {
            $request->validate([
                "result_id" => "required|numeric"
            ]);

            $result =  StudentResultReport::where("result_id", $request->result_id);
            $result = $result->leftJoin("results", "results.id", "=", "student_result_report.result_id");
            $result = $result->leftJoin("students", "students.id", "=", "student_result_report.student_id");
            $result = $result->selectRaw("student_result_report.*,students.student_name,students.student_id as student_identifier,results.result_name,(CASE WHEN student_result_report.result_status = 0 THEN 'Unpublished' ELSE 'Published' END) AS result_status");
            return $result->get();
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
                "ids" => "required",
            ]);
            $value = 1;
            $stat = "Published!";
            if ($request->unpublish) {
                $value = 0;
                $stat = "Unpublished!";
            }
            if (StudentResultReport::whereIn('id', $request->ids)->update(["result_status" => $value]))
                return ResponseMessage::success("Results " . $stat);
            else
                return ResponseMessage::success("Results Are Already " . $stat);
        }
    }
}
