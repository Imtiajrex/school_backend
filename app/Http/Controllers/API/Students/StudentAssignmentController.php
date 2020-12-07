<?php

namespace App\Http\Controllers\API\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\ClassHasSubjects;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Students;
use Illuminate\Http\Request;

class StudentAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View ClassHasStudents";
        if ($user->can($permission)) {
            return ClassHasStudents::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Assign ClassHasStudents";
        if ($user->can($permission)) {
            $request->validate([
                "class_id" => "required|numeric",
                "department_id" => "required|numeric",
                "session_id" => "required|numeric",
                "student_id" => "required|numeric",
            ]);

            $session_id = $request->session_id;
            $class_id = $request->class_id;
            $department_id = $request->department_id;
            $student_id = $request->student_id;

            if (SchoolClass::find($class_id) == null)
                return ResponseMessage::fail("Class Doesn't Exist!");

            if (Department::find($department_id) == null)
                return ResponseMessage::fail("Department Doesn't Exist!");

            if (Session::find($session_id) == null)
                return ResponseMessage::fail("Session Doesn't Exist!");

            if (Students::find($student_id) == null)
                return ResponseMessage::fail("Student Doesn't Exist!");

            $ClassHasStudents = ClassHasStudents::where(["session_id" => $session_id, "class_id" => $class_id, "department_id" => $department_id, "student_id" => $student_id])->first();

            if ($ClassHasStudents != null)
                return ResponseMessage::success("This Student is Already Assigned In This Class!");

            $ClassHasStudents = ClassHasStudents::where(["session_id" => $session_id, "student_id" => $student_id])->first();
            if ($ClassHasStudents != null)
                return ResponseMessage::success("Already Been Assigned In This Session! Remove from the assigned class first!");

            $assigned_student = new ClassHasStudents;

            $assigned_student->class_id = $class_id;
            $assigned_student->department_id = $department_id;
            $assigned_student->session_id = $session_id;
            $assigned_student->student_id = $student_id;

            if ($assigned_student->save()) {
                return ResponseMessage::success("Student Assigned Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Assign Student!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }


    public function destroy(Request $request)
    {
        $user = $request->user();
        $permission = "Delete ClassHasStudents";
        if ($user->can($permission)) {
            $request->validate([
                "class_id" => "required|numeric",
                "department_id" => "required|numeric",
                "session_id" => "required|numeric",
                "student_id" => "required|numeric",
            ]);

            $session_id = $request->session_id;
            $class_id = $request->class_id;
            $department_id = $request->department_id;
            $student_id = $request->student_id;

            $ClassHasStudents = ClassHasStudents::where(["session_id" => $session_id, "class_id" => $class_id, "department_id" => $department_id, "student_id" => $student_id])->first();

            if ($ClassHasStudents != null) {
                if ($ClassHasStudents->delete()) {
                    return ResponseMessage::success("Student Deleted From Class!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Student From Class!");
                }
            } else {
                return ResponseMessage::fail("This Student Doesn't Exist In This Class!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
