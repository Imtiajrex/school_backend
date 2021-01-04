<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Grade";
        $user = $request->user();
        if ($user->can($permission)) {
            return Grade::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Grade";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "starting_gpa" => 'required',
                "ending_gpa" => 'required',
                "grade" => "required"
            ]);
            if (Grade::where(["grade" => $request->grade])->first() == null) {
                $Grade = new Grade;
                $Grade->starting_gpa = $request->starting_gpa;
                $Grade->ending_gpa = $request->ending_gpa;
                $Grade->grade = $request->grade;
                if ($Grade->save()) {
                    return ResponseMessage::success("Grade Created!");
                } else {
                    return ResponseMessage::fail("Grade Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("Grade Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Grade";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "starting_gpa" => 'required',
                "ending_gpa" => 'required',
                "grade" => "required"
            ]);
            $Grade = Grade::find($id);
            if ($Grade != null) {
                $Grade->starting_gpa = $request->starting_gpa;
                $Grade->ending_gpa = $request->ending_gpa;
                $Grade->grade = $request->grade;
                if ($Grade->save()) {
                    return ResponseMessage::success("Grade Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Grade!");
                }
            } else {
                ResponseMessage::unauthorized($permission);
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Grade";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Grade::find($id) != null) {
                if (Grade::destroy($id)) {
                    return ResponseMessage::success("Grade Deleted!");
                }
            } else {
                return ResponseMessage::fail("Grade Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
