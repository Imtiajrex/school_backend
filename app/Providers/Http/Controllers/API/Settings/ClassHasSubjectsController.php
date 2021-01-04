<?php

namespace App\Http\Controllers\API\Settings;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasSubjects;
use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\Subjects;
use Illuminate\Http\Request;

class ClassHasSubjectsController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Assigned Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            return ClassHasSubjects::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function assign(Request $request)
    {
        $permission = "Assign Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "subjects" => "required|json",
                "class_id" => "required|numeric",
                "department_id" => "required|numeric"
            ]);
            if (SchoolClass::find($request->class_id) != null) {
                if (Department::find($request->department_id) != null) {
                    if ($this->assignSubject($request->class_id, $request->department_id, $request->subjects))
                        return ResponseMessage::success("Subjects Assigned!");
                    else {
                        return ResponseMessage::fail("Couldn't Assign Subject!");
                    }
                } else {
                    return ResponseMessage::fail("Department Doesn't Exist!");
                }
            } else {
                return ResponseMessage::fail("Class Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function assignSubject($class_id, $department_id, $subject_ids)
    {
        ClassHasSubjects::where(["class_id" => $class_id, "department_id" => $department_id])->delete();
        $counter = 0;
        $success_counter = 0;
        $subject_ids = json_decode($subject_ids);
        foreach ($subject_ids as $subject_id) {
            $counter++;
            if (Subjects::find($subject_id) == null)
                break;
            $assignSubject = new ClassHasSubjects;
            $assignSubject->class_id = $class_id;
            $assignSubject->department_id = $department_id;
            $assignSubject->subject_id = $subject_id;
            if ($assignSubject->save())
                $success_counter++;
        }
        if ($counter == $success_counter) {
            return true;
        } else {
            ClassHasSubjects::where(["class_id" => $class_id, "department_id" => $department_id])->delete();
            return false;
        }
    }
}
