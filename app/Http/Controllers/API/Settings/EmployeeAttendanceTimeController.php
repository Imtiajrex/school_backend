<?php

namespace App\Http\Controllers\API\Settings;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;

use App\Models\EmployeeAttendanceTime;
use Illuminate\Http\Request;

class EmployeeAttendanceTimeController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Attendance Time";
        $user = $request->user();
        if ($user->can($permission)) {
            return EmployeeAttendanceTime::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $permission = "Update Attendance Time";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "start_time" => "required",
                "end_time" => "required",
            ]);
            $employee_att_time = EmployeeAttendanceTime::find($id);
            $employee_att_time->start_time = $request->start_time;
            $employee_att_time->end_time = $request->end_time;
            if ($employee_att_time->save())
                return ResponseMessage::success("Attendance Time Updated!");
            else {
                return ResponseMessage::fail("Couldn't Update Attendance Time!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
