<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Homepage;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        return Homepage::all();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Homepage";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required",
            ]);

            $notification = new Homepage();

            $notification->title = $request->title;
            $notification->content = $request->content;


            if ($notification->save()) {
                return ResponseMessage::success("Homepage Content Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Homepage!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $permission = "Update Homepage";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required",
            ]);

            $notification = Homepage::find($id);
            if ($notification == null)
                return ResponseMessage::fail("Homepage Not Found!");

            $notification->title = $request->title;
            $notification->content = $request->content;



            if ($notification->save()) {
                return ResponseMessage::success("Homepage Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update Homepage!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Homepage";
        if ($user->can($permission)) {
            $notification = Homepage::find($id);
            if ($notification != null) {
                if ($notification->delete()) {
                    return ResponseMessage::success("Homepage Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Homepage!");
                }
            } else {
                return ResponseMessage::fail("Homepage Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
