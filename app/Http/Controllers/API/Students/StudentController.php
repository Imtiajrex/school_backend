<?php

namespace App\Http\Controllers\API\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Students;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Students";
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $query = [];
            if ($request->religion != null && $request->religion != -1) {
                $query["religion"] = $request->religion;
            }

            if ($request->gender != null && $request->gender != -1) {
                $query["gender"] = $request->gender;
            }

            if ($request->age != null && $request->age != "") {
                $query["age"] = $request->age;
            }

            $students = [];
            if ($request->student_id != null && strlen($request->student_id) > 0) {
                $students = Students::where("student_id", $request->student_id)->get();
            } else if (count($query) > 0) {
                $students = Students::where($query)->get();
            }

            foreach ($students as $student) {
                $student['extended_info'] = json_decode($student["extended_info"]);
            }
            return $students;
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Students";
        if ($user->can($permission)) {
            $request->validate([
                "father_name" => "required|string",
                "mother_name" => "required|string",
                "student_name" => "required|string",
                "gender" => "required",
                "religion" => "required",
                "age" => "required",
                "primary_phone" => "required",
                "class_id" => "required|numeric",
                "department_id" => "required|numeric",
                "session_id" => "required|numeric",
                "role" => "required|numeric",
            ]);

            $current_year = date('Y') - 2000;
            $total_students = Students::where("student_id", "like", "STD" . $current_year . "%")->count();
            $total_students = Students::where("student_id", "like", "STD" . $current_year . "%")->where("student_id", "like", "%" . $total_students + 1)->count();
            $student_id = ($current_year * 10000) + $total_students + 1;
            while (Students::where("student_id", "STD".$student_id)->first() == null) {
                $student_id++;
            }

            $students = new Students;
            $students->student_id = "STD".$student_id;

            $students->student_name = $request->student_name;
            $students->mother_name = $request->mother_name;
            $students->father_name = $request->father_name;
            $students->gender = $request->gender;
            $students->religion = $request->religion;
            $students->age = $request->age;
            $students->primary_phone = $request->primary_phone;

            if ($request->student_email != null) {
                $students->student_email = $request->student_email;
            }

            if ($request->secondary_phone != null) {
                $students->secondary_phone = $request->secondary_phone;
            }

            if ($request->extended_info != null) {
                $students->extended_info = json_encode($request->extended_info);
            }

            $students->enrollment_status = 'student';

            $student_image = "default.jpg";

            if ($request->hasFile("student_image")) {
                $image_res = FileUploader::upload($request->file("student_image"), 500);
                if (array_key_exists('error', $image_res)) {
                    return ResponseMessage::fail($image_res["error"]);
                } else if (array_key_exists('image_name', $image_res)) {
                    $student_image = $image_res["image_name"];
                }
            }
            $students->student_image = $student_image;

            if ($students->save()) {
                $class_assigned = $this->assignClass($request->session_id, $request->class_id, $request->department_id, $students->id, $request->role);
                if (!array_key_exists('error', $class_assigned)) {
                    if ($this->createUserAccount($student_id, $request->student_name)) {
                        return ResponseMessage::success("Student Created Successfully!");
                    } else {
                        return ResponseMessage::success("Student Created! But No Student User Account!");
                    }
                } else {
                    Students::destroy($students->id);
                    if ($student_image != "default.jpg") {
                        Storage::delete("public/images/" . $student_image);
                    }

                    return ResponseMessage::fail($class_assigned["error"]);
                }
            } else {
                if ($student_image != "default.jpg") {
                    Storage::delete("public/images/" . $student_image);
                }

                return ResponseMessage::fail("Couldn't Create Student!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Students";
        if ($user->can($permission)) {
            $request->validate([
                "student_name" => "required|string",
                "mother_name" => "required|string",
                "father_name" => "required|string",
                "gender" => "required|string",
                "religion" => "required|string",
                "age" => "required|numeric",
                "primary_phone" => "required",
                "extended_info" => "required",
                "enrollment_status" => "required|string",
            ]);
            $students = Students::find($id);
            if ($students != null) {
                $students->student_name = $request->student_name;
                $students->mother_name = $request->mother_name;
                $students->father_name = $request->father_name;
                $students->gender = $request->gender;
                $students->religion = $request->religion;
                $students->age = $request->age;
                $students->primary_phone = $request->primary_phone;

                if ($request->student_email != null) {
                    $students->student_email = $request->student_email;
                }

                if ($request->secondary_phone != null) {
                    $students->secondary_phone = $request->secondary_phone;
                }

                if ($request->extended_info != null) {
                    $students->extended_info = $request->extended_info;
                }

                $students->enrollment_status = $request->enrollment_status;

                $prev_image = $students->student_image;

                if ($request->hasFile("student_image")) {
                    $image_res = FileUploader::upload($request->file("student_image"), 500);
                    if (array_key_exists('error', $image_res)) {
                        return ResponseMessage::fail($image_res["error"]);
                    } else if (array_key_exists('image_name', $image_res)) {
                        $student_image = $image_res["image_name"];
                    }
                    $students->student_image = $student_image;
                }

                if ($students->save()) {
                    if ($prev_image != $students->student_image && $prev_image != 'default.jpg') {
                        Storage::delete("public/images/" . $prev_image);
                    }

                    return ResponseMessage::success("Student Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Student!");
                }
            } else {
                return ResponseMessage::fail("Student Doesn't Exist!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Student";
        if ($user->can($permission)) {
            $students = Students::find($id);
            if ($students != null) {
                $student_image = $students->student_image;
                User::where(["username" => $students->student_id])->delete();
                if ($students->delete()) {
                    if ($student_image != "default.jpg") {
                        Storage::delete("public/images/" . $student_image);
                    }

                    return ResponseMessage::success("Student Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Student!");
                }
            } else {
                return ResponseMessage::fail("Student Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function createUserAccount($student_id, $student_name)
    {
        $user = new User;
        $user->username = $student_id;
        $user->password = Hash::make($student_id);
        $user->name = $student_name;
        $user->user_type = "student";
        if ($user->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function assignClass($session_id, $class_id, $department_id, $student_id, $role)
    {
        if (Session::find($session_id) != null) {
            if (SchoolClass::find($class_id) != null) {
                if (Department::find($department_id) != null) {
                    $assign_student = new ClassHasStudents;
                    $assign_student->session_id = $session_id;
                    $assign_student->class_id = $class_id;
                    $assign_student->department_id = $department_id;
                    $assign_student->student_id = $student_id;
                    $assign_student->role = $role;
                    if ($assign_student->save()) {
                        return ["success" => true];
                    } else {
                        return ["error" => "Couldn't Assign Class"];
                    }
                } else {
                    return ["error" => "Department Doesn't Exist"];
                }
            } else {
                return ["error" => "Class Doesn't Exist"];
            }
        } else {
            return ["error" => "Session Doesn't Exist"];
        }
    }
}
