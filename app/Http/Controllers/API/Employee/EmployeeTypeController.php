<?php

namespace App\Http\Controllers\API\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\EmployeeType;
use Illuminate\Http\Request;

class EmployeeTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Employee Type";
        if ($user->can($permission)) {
            return EmployeeType::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Employee Type";
        if ($user->can($permission)) {
            $request->validate([
                "employee_type" => "required",
            ]);


            $employee = new EmployeeType;
            $employee->employee_Type = $request->employee_type;

            if ($employee->save()) {
                return ResponseMessage::success("Employee Type Created Successfully!");
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
        $permission = "Update Employee Type";
        if ($user->can($permission)) {
            $request->validate([
                "employee_type" => "required",
            ]);
            $employee = EmployeeType::find($id);
            if ($employee != null) {
                $employee->employee_Type = $request->employee_type;

                if ($employee->save()) {
                    return ResponseMessage::success("Employee Type Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Employee Type!");
                }
            } else
                return ResponseMessage::fail("Employee Type Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Employee Type";
        if ($user->can($permission)) {
            $employee = EmployeeType::find($id);
            if ($employee != null) {
                if ($employee->delete()) {
                    return ResponseMessage::success("Employee Type Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Employee Type!");
                }
            } else {
                return ResponseMessage::fail("Employee Type Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
