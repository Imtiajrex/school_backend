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
        if ($user->can($permission)||$user->user_type=="teacher") {
            $query = [];
            $subjects = [];
            if ($request->class_id != null && $request->department_id != null)
                $query = ["class_has_subjects.class_id" => $request->class_id, "class_has_subjects.department_id" => $request->department_id];
            if (count($query) > 0) {
                $subjects = ClassHasSubjects::where($query);
                $subjects = $subjects->leftJoin("subjects", "subjects.id", '=', 'class_has_subjects.subject_id');
                $subjects = $subjects->leftJoin("class", "class.id", '=', 'class_has_subjects.class_id');
                $subjects = $subjects->leftJoin("department", "department.id", '=', 'class_has_subjects.department_id');
                return $subjects->get(['class_has_subjects.*', 'subjects.subject_name as name', 'class.id as class_id', 'class.name as class', 'department.name as department', 'department.id as department_id']);
            }else if($request->exam){
                $subjects = new ClassHasSubjects;
                $subjects = $subjects->leftJoin("subjects", "subjects.id", '=', 'class_has_subjects.subject_id');
                $subjects = $subjects->leftJoin("class", "class.id", '=', 'class_has_subjects.class_id');
                $subjects = $subjects->leftJoin("department", "department.id", '=', 'class_has_subjects.department_id');
                return $subjects->get(['class_has_subjects.*','subjects.id', 'subjects.subject_name as name', 'class.id as class_id', 'class.name as class', 'department.name as department', 'department.id as department_id']);
            }
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
    public function destroy($id, Request $request)
    {

        $permission = "Delete Assigned Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            if (ClassHasSubjects::destroy($id))
                return ResponseMessage::success("Assigned Subject Deleted");
            else
                return ResponseMessage::fail("Couldn't Delte Assigned Subject");
        }
    }
}
