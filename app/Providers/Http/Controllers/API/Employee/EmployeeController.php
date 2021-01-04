<?php

namespace App\Http\Controllers\API\Employee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\Employee;
use App\Models\EmployeePost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Employee";
        if ($user->can($permission)) {
            return Employee::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Employee";
        if ($user->can($permission)) {
            $request->validate([
                "employee_name" => "required",
                "employee_type" => "required",
                "employee_post" => "required",
                "employee_age" => "required",
                "employee_gender" => "required",
                "employee_religion" => "required",
                "employee_primary_phone" => "required",
            ]);


            $current_year = date('Y') - 2000;
            $total_employee = Employee::all()->count();
            $employee_id = ($current_year * 10000) + $total_employee + 1;
            while (Employee::where("employee_id", $employee_id)->first() != null) {
                $employee_id++;
            }
            $employee_id = "EMP" . $employee_id;

            if (EmployeePost::where("employee_post", $request->employee_post) == null)
                return ResponseMessage::fail("Employee Post Doesn't Exist");

            $employee = new Employee;
            $employee->employee_id = $employee_id;

            $employee->employee_name = $request->employee_name;
            $employee->employee_type = $request->employee_type;
            $employee->employee_post = $request->employee_post;
            $employee->employee_age = $request->employee_age;
            $employee->employee_gender = $request->employee_gender;
            $employee->employee_religion = $request->employee_religion;
            $employee->employee_primary_phone = $request->employee_primary_phone;


            if ($request->employee_email != null)
                $employee->employee_email = $request->employee_email;

            if ($request->secondary_phone != null)
                $employee->employee_secondary_phone = $request->employee_secondary_phone;

            if ($request->employee_extended_info != null)
                $employee->employee_extended_info = $request->employee_extended_info;

            $employee->job_status = 'employee';

            $employee_image = "default.jpg";

            if ($request->hasFile("employee_image")) {
                $image_res = FileUploader::upload($request->file("employee_image"), 500);
                if (array_key_exists('error', $image_res)) {
                    return ResponseMessage::fail($image_res["error"]);
                } else if (array_key_exists('image_name', $image_res)) {
                    $employee_image = $image_res["image_name"];
                }
            }
            $employee->employee_image = $employee_image;


            if ($employee->save()) {
                if (!($request->employee_type == "teacher") || $this->createUserAccount($employee->employee_id, $request->employee_name)) {
                    return ResponseMessage::success("Employee Created Successfully!");
                } else {
                    return ResponseMessage::success("Employee Created! But No Employee User Account!");
                }
            } else {
                if ($employee_image != "default.jpg")
                    Storage::delete("public/images/" . $employee_image);
                return ResponseMessage::fail("Couldn't Create Employee!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Employee";
        if ($user->can($permission)) {
            $request->validate([
                "employee_name" => "required",
                "employee_type" => "required",
                "employee_post" => "required",
                "employee_age" => "required",
                "employee_gender" => "required",
                "employee_religion" => "required",
                "employee_primary_phone" => "required",
                "job_status" => "required"
            ]);
            $employee = Employee::find($id);
            if ($employee != null) {
                if (EmployeePost::where("employee_post", $request->employee_post) == null)
                    return ResponseMessage::fail("Employee Post Doesn't Exist");
                $employee->employee_name = $request->employee_name;
                $employee->employee_type = $request->employee_type;
                $employee->employee_post = $request->employee_post;
                $employee->employee_age = $request->employee_age;
                $employee->employee_gender = $request->employee_gender;
                $employee->employee_religion = $request->employee_religion;
                $employee->employee_primary_phone = $request->employee_primary_phone;

                if ($request->employee_email != null)
                    $employee->employee_email = $request->employee_email;

                if ($request->secondary_phone != null)
                    $employee->employee_secondary_phone = $request->employee_secondary_phone;

                if ($request->employee_extended_info != null)
                    $employee->employee_extended_info = $request->employee_extended_info;

                $employee->job_status = $request->job_status;

                $prev_image = $employee->employee_image;

                if ($request->hasFile("employee_image")) {
                    $image_res = FileUploader::upload($request->file("employee_image"), 500);
                    if (array_key_exists('error', $image_res)) {
                        return ResponseMessage::fail($image_res["error"]);
                    } else if (array_key_exists('image_name', $image_res)) {
                        $employee_image = $image_res["image_name"];
                    }
                    $employee->employee_image = $employee_image;
                }

                if ($employee->save()) {
                    if ($prev_image != $employee->employee_image && $prev_image != "default.jpg")
                        Storage::delete("public/images/" . $prev_image);
                    return ResponseMessage::success("Employee Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Employee!");
                }
            } else
                return ResponseMessage::fail("Employee Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Employee";
        if ($user->can($permission)) {
            $employee = Employee::find($id);
            if ($employee != null) {
                if (User::where(["username" => $employee->employee_id])->delete()) {
                    if ($employee->delete()) {
                        return ResponseMessage::success("Employee Deleted!");
                    } else {
                        return ResponseMessage::fail("Couldn't Delete Employee!");
                    }
                } else {
                    return ResponseMessage::fail("Couldn't Delete Employee User Account!");
                }
            } else {
                return ResponseMessage::fail("Employee Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function createUserAccount($employee_id, $employee_name)
    {
        $user = new User;
        $user->username = $employee_id;
        $user->password = Hash::make($employee_id);
        $user->name = $employee_name;
        $user->user_type = "Employee";
        if ($user->save()) {
            return true;
        } else
            return false;
    }
}
