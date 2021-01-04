<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Subjects;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            return Subjects::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "subject_name" => "required"
            ]);
            if (Subjects::where(["subject_name" => $request->subject_name])->first() == null) {
                $Subjects = new Subjects;
                $Subjects->subject_name = $request->subject_name;
                if ($Subjects->save()) {
                    return ResponseMessage::success("Subject Created!");
                } else {
                    return ResponseMessage::fail("Subject Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("Subject Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "subject_name" => "required"
            ]);
            $Subjects = Subjects::find($id);
            if ($Subjects != null) {
                $Subjects->subject_name = $request->subject_name;
                if ($Subjects->save()) {
                    return ResponseMessage::success("Subject Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Subject!");
                }
            } else {
                return ResponseMessage::fail("Subject Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Subject";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Subjects::find($id) != null) {
                if (Subjects::destroy($id)) {
                    return ResponseMessage::success("Subject Deleted!");
                }
            } else {
                return ResponseMessage::fail("Subject Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
