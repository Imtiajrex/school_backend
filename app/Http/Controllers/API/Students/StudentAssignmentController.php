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
        if ($user->can($permission) || $user->user_type == "teacher" || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $query = [];
            if ($request->session_id) {
                $query["class_has_students.session_id"] = $request->session_id;

                if ($request->class_id) {
                    $query["class_has_students.class_id"] = $request->class_id;

                    if ($request->department_id) {
                        $query["class_has_students.department_id"] = $request->department_id;
                    }
                }
            }

            if ($request->student_id) {
                $student_id = Students::where("student_id", $request->student_id)->first();
                if ($student_id)
                    $query = ["class_has_students.student_id" => $student_id->id];
            }

            if (count($query) != 0) {
                $students =  ClassHasStudents::where($query)->leftJoin("class", "class_has_students.class_id", "=", "class.id")->leftJoin("session", "class_has_students.session_id", "=", "session.id")->leftJoin("department", "class_has_students.department_id", "=", "department.id")->leftJoin("students", "class_has_students.student_id", "=", "students.id")->orderBy("class_has_students.class_id", "desc")->orderBy("class_has_students.department_id", "desc")->orderBy("role", "asc");

                if ($request->phonebook)
                    return $students->get(["class_has_students.*", "class.name as class", "session.*", "department.name as department", "students.student_name", "students.student_id as student_identifier", "students.primary_phone", "students.secondary_phone"]);
                else if ($request->student_options) {
                    return $students->selectRaw('class_has_students.student_id as value,concat(students.student_id, " ",students.student_name) as text')->get();
                } else if ($request->all) {
                    return $students->get(["class_has_students.*", "class.name as class", "session.session", "department.name as department", "students.*"]);
                } else
                    return $students->get(["class_has_students.*", "class.name as class", "session.session", "department.name as department", "students.student_name", "students.student_id"]);
            }
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
                "student_id" => "required|string",
                "role" => "required|numeric",
            ]);

            $session_id = $request->session_id;
            $class_id = $request->class_id;
            $department_id = $request->department_id;
            $student_id = $request->student_id;
            $role = $request->role;


            if (SchoolClass::find($class_id) == null)
                return ResponseMessage::fail("Class Doesn't Exist!");

            if (Department::find($department_id) == null)
                return ResponseMessage::fail("Department Doesn't Exist!");

            if (Session::find($session_id) == null)
                return ResponseMessage::fail("Session Doesn't Exist!");
            $student = Students::where("student_id", $student_id)->first();
            if ($student == null)
                return ResponseMessage::fail("Student Doesn't Exist!");
            $student_id = $student->id;
            $ClassHasStudents = ClassHasStudents::where(["session_id" => $session_id, "class_id" => $class_id, "department_id" => $department_id, "student_id" => $student_id])->first();

            if ($ClassHasStudents != null)
                return ResponseMessage::success("This Student is Already Assigned In This Class!");

            $assigned_student = new ClassHasStudents;

            $assigned_student->class_id = $class_id;
            $assigned_student->department_id = $department_id;
            $assigned_student->session_id = $session_id;
            $assigned_student->student_id = $student_id;
            $assigned_student->role = $role;

            if ($assigned_student->save()) {
                return ResponseMessage::success("Student Assigned Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Assign Student!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Assign ClassHasStudents";
        if ($user->can($permission)) {
            $request->validate([
                "role" => "required|numeric",
            ]);

            $role = $request->role;

            $assigned_student = ClassHasStudents::find($id);

            $assigned_student->role = $role;

            if ($assigned_student->save()) {
                return ResponseMessage::success("Student Assigned Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Assign Student!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }


    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete ClassHasStudents";
        if ($user->can($permission)) {


            $ClassHasStudents = ClassHasStudents::find($id);

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
