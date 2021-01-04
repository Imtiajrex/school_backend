<?php

namespace App\Http\Controllers\API\Settings;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasDepartment;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Session;
use Illuminate\Http\Request;

class ClassHasDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Assigned Department";
        $user = $request->user();
        if ($user->can($permission)) {
            return ClassHasDepartment::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function assign(Request $request)
    {
        $permission = "Assign Department";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "class_id" => "required|numeric",
                "department_ids" => "required|json",
                "session_id" => "required|numeric"
            ]);
            if (SchoolClass::find($request->class_id) != null) {
                if (Session::find($request->session_id) != null) {
                    if ($this->assignDepartment($request->class_id, $request->department_ids, $request->session_id))
                        return ResponseMessage::success("Department Assigned!");
                    else {
                        return ResponseMessage::fail("Couldn't Assign Department!");
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

    public function assignDepartment($class_id, $department_ids, $session_id)
    {
        ClassHasDepartment::where(["class_id" => $class_id, "session_id" => $session_id])->delete();
        $counter = 0;
        $success_counter = 0;
        $department_ids = json_decode($department_ids);
        foreach ($department_ids as $department_id) {
            $counter++;
            if (Department::find($department_id) == null)
                break;
            $assignDepartment = new ClassHasDepartment;
            $assignDepartment->class_id = $class_id;
            $assignDepartment->department_id = $department_id;
            $assignDepartment->session_id = $session_id;
            if ($assignDepartment->save())
                $success_counter++;
        }
        if ($counter == $success_counter) {
            return true;
        } else {
            ClassHasDepartment::where(["class_id" => $class_id, "session_id" => $session_id])->delete();
            return false;
        }
    }
}
