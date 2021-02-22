<?php

namespace App\Http\Controllers\API\Messages;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Employee;
use App\Models\EmployeeMessage;
use Illuminate\Http\Request;

class EmployeeMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Employee Message";
        if ($user->can($permission) || $user->user_type == "teacher" || $user->user_type == "student") {
            if ($request->employee_id) {
                return EmployeeMessage::where("employee_messages.employee_id", $request->employee_id)->leftJoin("employee","employee.id",'=','employee_messages.employee_id')->get(["title","content","employee_messages.id","employee.employee_id as employee_identifier","employee.employee_name"]);
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Employee Message";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required|string",
                "employee_id" => 'required'
            ]);

            $msg = new EmployeeMessage();

            $msg->title = $request->title;
            $msg->content = $request->content;
            $msg->employee_id = $request->employee_id;


            if ($msg->save()) {
                return ResponseMessage::success("Employee Message Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Create Employee Message!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request,$id)
    {
        $user = $request->user();
        $permission = "Create Employee Message";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required|string",
            ]);

            $msg = EmployeeMessage::find($id);

            $msg->title = $request->title;
            $msg->content = $request->content;

            if ($msg->save()) {
                return ResponseMessage::success("Employee Message Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Updated Employee Message!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Employee Message";
        if ($user->can($permission)) {
            $msg = EmployeeMessage::find($id);
            if ($msg != null) {
                if ($msg->delete()) {
                    return ResponseMessage::success("Employee Message Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Employee Message!");
                }
            } else {
                return ResponseMessage::fail("Employee Message Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
