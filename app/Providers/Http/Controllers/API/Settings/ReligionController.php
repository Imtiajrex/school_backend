<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Models\Religion;
use Illuminate\Http\Request;
use App\Http\Controllers\ResponseMessage;

class ReligionController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Religion";
        $user = $request->user();
        if ($user->can($permission)) {
            return Religion::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Religion";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "religion_name" => "required"
            ]);
            if (Religion::where("religion_name", $request->religion_name)->first() == null) {
                $religion = new Religion;
                $religion->religion_name = $request->religion_name;
                if ($religion->save()) {
                    return ResponseMessage::success("Religion Created!");
                } else {
                    return ResponseMessage::fail("Create Religion Method Failed!");
                }
            } else {
                return ResponseMessage::fail("Religion Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Religion";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "religion_name" => "required"
            ]);
            $religion = Religion::find($id);
            if ($religion != null) {
                $religion->religion_name = $request->religion_name;
                if ($religion->save()) {
                    return ResponseMessage::success("Religion Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Religion!");
                }
            } else
                return ResponseMessage::fail("Religion Doesn't Exist!");
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Religion";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Religion::find($id) != null) {
                if (Religion::destroy($id)) {
                    return ResponseMessage::success("Religion Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Religion!");
                }
            } else {

                return ResponseMessage::fail("Religion Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
