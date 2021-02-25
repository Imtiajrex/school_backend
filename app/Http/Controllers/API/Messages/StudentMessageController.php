<?php

namespace App\Http\Controllers\API\Messages;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\StudentMessage;
use Illuminate\Http\Request;

class StudentMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Student Message";
        if ($user->can($permission) || $user->user_type == "teacher" || $user->user_type == "student") {
            if ($request->student_id) {
                $student_id = ClassHasStudents::find($request->student_id);
                $student_id = $student_id->id;
                return StudentMessage::where("student_messages.student_id", $student_id)->leftJoin("class_has_students", "class_has_students.id", "=", "student_messages.student_id")->leftJoin("students", "students.id", '=', 'class_has_students.student_id')->get(["title", "content", "student_messages.id", "class_has_students.student_identifier", "students.student_name"]);
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Student Message";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate([
                "title" => "required|string",
                "content" => "required|string",
            ]);
            if ($request->student_id) {
                $msg = new StudentMessage();

                $msg->title = $request->title;
                $msg->content = $request->content;
                $msg->student_id = $request->student_id;


                if ($msg->save()) {
                    return ResponseMessage::success("Student Message Created Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Create Student Message!");
                }
            } else {
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
                if (count($query > 0)) {
                    $data = [];
                    $students = ClassHasStudents::where($query)->get();
                    foreach ($students as $student) {
                        array_push($data, ["title" => $request->title, "content" => $request->content, "student_id" => $student->id]);
                    }

                    if (StudentMessage::insert($data)) {
                        return ResponseMessage::success("Student Message Created Successfully!");
                    } else {
                        return ResponseMessage::fail("Couldn't Create Student Message!");
                    }
                }
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Create Student Message";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $request->validate([
                "title" => "required|string",
                "content" => "required|string"
            ]);

            $msg = StudentMessage::find($id);

            $msg->title = $request->title;
            $msg->content = $request->content;


            if ($msg->save()) {
                return ResponseMessage::success("Student Message Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Updated Student Message!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Student Message";
        if ($user->can($permission) || $user->user_type == "teacher") {
            $msg = StudentMessage::find($id);
            if ($msg != null) {
                if ($msg->delete()) {
                    return ResponseMessage::success("Student Message Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Student Message!");
                }
            } else {
                return ResponseMessage::fail("Student Message Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
