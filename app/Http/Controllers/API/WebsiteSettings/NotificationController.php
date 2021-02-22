<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Notifications;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->id) {
            return Notifications::find($request->id);
        }
        return Notifications::selectRaw("notifications.*,case when notifications.active = 1 then 'Active' else 'Inactive' end as status")->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Notifications";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required",
                "active" => 'required'
            ]);

            $notification = new Notifications();

            $notification->title = $request->title;
            $notification->content = $request->content;
            $notification->active = $request->active;


            if ($notification->save()) {
                return ResponseMessage::success("Notification Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Notifications!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $permission = "Update Notifications";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required",
                "active" => 'required'
            ]);

            $notification = Notifications::find($id);
            if ($notification == null)
                return ResponseMessage::fail("Notifications Not Found!");

            $notification->title = $request->title;
            $notification->content = $request->content;
            $notification->active = $request->active;



            if ($notification->save()) {
                return ResponseMessage::success("Notifications Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update Notifications!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Notifications";
        if ($user->can($permission)) {
            $notification = Notifications::find($id);
            if ($notification != null) {
                if ($notification->delete()) {
                    return ResponseMessage::success("Notifications Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Notifications!");
                }
            } else {
                return ResponseMessage::fail("Notifications Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
