<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Session;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Department";
        $user = $request->user();
        if ($user->can($permission) || $user->user_type == "teacher" || $user->user_type == "student") {
            $query = [];
            $depts = [];
            if ($request->class_id != null && $request->session_id != null)
                $query = ["class_id" => $request->class_id, "session_id" => $request->session_id];
            if (count($query) > 0)
                $depts = Department::where($query)->get();
            else
                $depts = Department::all();

            foreach ($depts as $dept) {
                $class = SchoolClass::find($dept->class_id);
                $session = Session::find($dept->session_id);
                if ($class != null && $session != null) {
                    $dept["class"] = $class->name;
                    $dept["session"] = $session->session;
                }
            }
            return $depts;
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Department";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "name" => "required|string",
                "class_id" => "required|numeric",
                "session_id" => "required|numeric"
            ]);
            if (Department::where(["name" => $request->name, "class_id" => $request->class_id, "session_id" => $request->session_id])->first() == null) {
                $department = new Department;
                $department->name = $request->name;
                $department->class_id = $request->class_id;
                $department->session_id = $request->session_id;
                if ($department->save()) {
                    return ResponseMessage::success("Department Created!");
                } else {
                    return ResponseMessage::fail("Department Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("Department Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Department";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "name" => "required|string"
            ]);
            $department = Department::find($id);
            if ($department != null) {
                $department->name = $request->name;
                $department->class_id = $request->class_id;
                $department->session_id = $request->session_id;
                if ($department->save()) {
                    return ResponseMessage::success("Department Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Department!");
                }
            } else {
                return ResponseMessage::fail("Department Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Department";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Department::find($id) != null) {
                if (Department::destroy($id)) {
                    return ResponseMessage::success("Department Deleted!");
                }
            } else {
                return ResponseMessage::fail("Department Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
