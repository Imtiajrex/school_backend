<?php

namespace App\Http\Controllers\API\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Employee;
use App\Models\EmployeePost;
use Illuminate\Http\Request;

class EmployeePostController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Employee Post";
        if ($user->can($permission)) {
            return EmployeePost::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Employee Post";
        if ($user->can($permission)) {
            $request->validate([
                "employee_post" => "required",
            ]);


            $employee = new EmployeePost;
            $employee->employee_post = $request->employee_post;

            if ($employee->save()) {
                return ResponseMessage::success("Employee Post Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Create Employee!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Employee Post";
        if ($user->can($permission)) {
            $request->validate([
                "employee_post" => "required",
            ]);
            $employee = EmployeePost::find($id);
            if ($employee != null) {
                $employee->employee_post = $request->employee_post;

                if ($employee->save()) {
                    return ResponseMessage::success("Employee Post Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Employee Post!");
                }
            } else
                return ResponseMessage::fail("Employee Post Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Employee Post";
        if ($user->can($permission)) {
            $employee = EmployeePost::find($id);
            if ($employee != null) {
                if ($employee->delete()) {
                    return ResponseMessage::success("Employee Post Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Employee Post!");
                }
            } else {
                return ResponseMessage::fail("Employee Post Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
