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
        if ($request->home) {
            if ($request->employee_type != null)
                $query["employee_type"] = $request->employee_type;

            if ($request->limit)
                return Employee::where("job_status", 'employee')->where($query)->orderBy("id", "asc")->take(5)->get();
            else
                return Employee::where("job_status", 'employee')->where($query)->orderBy("id", "asc")->get();
        }

        $user = $request->user();
        $permission = "View Employee";
        if ($user->can($permission)) {
            $query = [];
            if ($request->options) {
                $query["employee.job_status"] = "employee";
                if ($request->employee_type) {
                    $query["employee_type"] = $request->employee_type;
                }
                return Employee::where("job_status", 'employee')->where($query)->selectRaw('employee.id as value,concat(employee.employee_id, " ",employee.employee_name) as text')->get();
            }
            if ($request->religion != null && $request->religion != -1)
                $query["employee_religion"] = $request->religion;
            if ($request->gender != null && $request->gender != -1)
                $query["employee_gender"] = $request->gender;
            if ($request->age != null && $request->age != "")
                $query["employee_age"] = $request->age;
            if ($request->employee_type != null && $request->employee_type != -1)
                $query["employee_type"] = $request->employee_type;


            if ($request->employee_id != null && strlen($request->employee_id) > 0)
                $employees = Employee::where("job_status", 'employee')->where("employee_id", "like", ($request->employee_id) . "%")->get();
            else if (count($query) > 0)
                $employees = Employee::where("job_status", 'employee')->where($query)->get();
            else
                $employees = Employee::where("job_status", 'employee')->get();

            return $employees;
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
                "mother_name" => "required|string",
                "father_name" => "required|string",
                "employee_type" => "required",
                "employee_post" => "required",
                "date_of_birth" => "required",
                "employee_gender" => "required",
                "employee_religion" => "required",
                "employee_primary_phone" => "required",
            ]);


            $current_year = date('Y') - 2000;
            
            $last_employee = Employee::latest()->first();
            

            $employee_id = ($current_year * 10000) + $last_employee->id + 1;
            while (Employee::where("employee_id", $employee_id)->first() != null) {
                $employee_id++;
            }
            $employee_id = "EMP" . $employee_id;

            if (EmployeePost::where("employee_post", $request->employee_post) == null)
                return ResponseMessage::fail("Employee Post Doesn't Exist");

            $employee = new Employee;
            $employee->employee_id = $employee_id;

            $employee->employee_name = $request->employee_name;
            $employee->mother_name = $request->mother_name;
            $employee->father_name = $request->father_name;
            $employee->employee_name = $request->employee_name;
            $employee->employee_type = $request->employee_type;
            $employee->employee_post = $request->employee_post;
            $employee->date_of_birth = $request->date_of_birth;
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
                if (($request->employee_type == "Teacher")) {
                    if ($this->createUserAccount($employee->employee_id, $request->employee_name))
                        return ResponseMessage::success("Employee Created Successfully!");
                    else {
                        return ResponseMessage::success("Employee Created! But No Employee User Account!");
                    }
                }
                return ResponseMessage::success("Employee Created Successfully!");
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
                "mother_name" => "required|string",
                "father_name" => "required|string",
                "employee_post" => "required",
                "date_of_birth" => "required",
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
                $employee->mother_name = $request->mother_name;
                $employee->father_name = $request->father_name;
                $employee->employee_type = $request->employee_type;
                $employee->employee_post = $request->employee_post;
                $employee->date_of_birth = $request->date_of_birth;
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
        $user->user_type = "teacher";
        if ($user->save()) {
            return true;
        } else
            return false;
    }
}
