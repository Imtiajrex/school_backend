<?php

namespace App\Http\Controllers\API\Settings;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasSubjects;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\StudentAttendanceTime;
use Illuminate\Http\Request;

class StudentsAttendanceTimeController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Attendance Time";
        $user = $request->user();
        if ($user->can($permission)) {
            $query = [];
            if ($request->class_id != null && $request->session_id != null)
                $query = ["class_id" => $request->class_id, "session_id" => $request->session_id];
            if (count($query) > 0) {
                return StudentAttendanceTime::where($query)->leftJoin("class",'class.id','=','student_attendance_times.class_id')->leftJoin("session",'session.id','=','student_attendance_times.session_id')->get(["class.name as class",'session.session','start_time','end_time']);
            }else{
                return StudentAttendanceTime::leftJoin("class",'class.id','=','student_attendance_times.class_id')->leftJoin("session",'session.id','=','student_attendance_times.session_id')->get(["class.name as class",'session.session','start_time','end_time']);
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Attendance Time";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "start_time" => "required",
                "end_time" => "required",
                "class_id" => "required|numeric",
                "session_id" => "required|numeric"
            ]);
            if (SchoolClass::find($request->class_id) != null) {
                if (Session::find($request->session_id) != null) {
                    $student_att_time = new StudentAttendanceTime;
                    $student_att_time->class_id = $request->class_id;
                    $student_att_time->session_id = $request->session_id;
                    $student_att_time->start_time = $request->start_time;
                    $student_att_time->end_time = $request->end_time;
                    if ($student_att_time->save())
                        return ResponseMessage::success("Attendance Time Createed!");
                    else {
                        return ResponseMessage::fail("Couldn't Create Attendance Time!");
                    }
                } else {
                    return ResponseMessage::fail("Session Doesn't Exist!");
                }
            } else {
                return ResponseMessage::fail("Class Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {

        $permission = "Delete Attendance Time";
        $user = $request->user();
        if ($user->can($permission)) {
            if (ClassHasSubjects::destroy($id))
                return ResponseMessage::success("Attendance Time Deleted");
            else
                return ResponseMessage::fail("Couldn't Delte Attendance Time");
        }
    }
}
