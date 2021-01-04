<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Gpa;
use Illuminate\Http\Request;

class GpaController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Gpa";
        $user = $request->user();
        if ($user->can($permission)) {
            return Gpa::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Gpa";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "starting_number" => 'required',
                "ending_number" => 'required',
                "gpa" => "required"
            ]);
            if (Gpa::where(["gpa" => $request->gpa])->first() == null) {
                $Gpa = new Gpa;
                $Gpa->starting_number = $request->starting_number;
                $Gpa->ending_number = $request->ending_number;
                $Gpa->gpa = $request->gpa;
                if ($Gpa->save()) {
                    return ResponseMessage::success("Gpa Created!");
                } else {
                    return ResponseMessage::fail("Gpa Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("Gpa Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Gpa";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "starting_number" => 'required',
                "ending_number" => 'required',
                "gpa" => "required"
            ]);
            $Gpa = Gpa::find($id);
            if ($Gpa != null) {
                $Gpa->starting_number = $request->starting_number;
                $Gpa->ending_number = $request->ending_number;
                $Gpa->gpa = $request->gpa;
                if ($Gpa->save()) {
                    return ResponseMessage::success("Gpa Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Gpa!");
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
        $permission = "Delete Gpa";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Gpa::find($id) != null) {
                if (Gpa::destroy($id)) {
                    return ResponseMessage::success("Gpa Deleted!");
                }
            } else {
                return ResponseMessage::fail("Gpa Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
