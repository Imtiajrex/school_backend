<?php

namespace App\Http\Controllers\API\Employee;

use App\Models\EmployeeAttendance;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Employee;

class EmployeeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Employee Attendance";
        if ($user->can($permission)) {
            $request->validate([
                "from" => "required|date",
                "to" => "required|date",
                "employee_id" => "required|json"
            ]);
            $employee_ids = json_decode($request->employee_id, true);
            $from = $request->from;
            $to = $request->to;
            return EmployeeAttendance::whereBetween("date", [$from, $to])->whereIn('employee_id', $employee_ids)->get();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Assign Employee Attendance";
        if ($user->can($permission)) {
            $request->validate([
                "employee_id" => "required|numeric",
                "date" => "required|date",
                "access_time" => "required|string"
            ]);
            $attendance = new EmployeeAttendance;
            $attendance->employee_id = $request->employee_id;
            $attendance->date = $request->date;
            $attendance->access_time = $request->access_time;

            if (Employee::find($request->employee_id) != null) {
                if ($attendance->save()) {
                    return ResponseMessage::success("Employee Access Time Assigned!");
                }
            } else {
                return ResponseMessage::fail("Employee Not Found!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {

        $user = $request->user();
        $permission = "Delete Employee Attendance";
        if ($user->can($permission)) {
            $attendance = EmployeeAttendance::find($id);
            if ($attendance != null) {
                if ($attendance->delete()) {
                    return ResponseMessage::success("Employee Access Time Deleted!");
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
