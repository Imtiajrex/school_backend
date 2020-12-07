<?php

namespace App\Http\Controllers\API\Students;

use App\Models\StudentsAttendance;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Students;

class StudentsAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Student Attendance";
        if ($user->can($permission)) {
            $request->validate([
                "from" => "required|date",
                "to" => "required|date",
                "student_id" => "required|json"
            ]);
            $student_ids = json_decode($request->student_id, true);
            $from = $request->from;
            $to = $request->to;
            return StudentsAttendance::whereBetween("date", [$from, $to])->whereIn('student_id', $student_ids)->get();
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
