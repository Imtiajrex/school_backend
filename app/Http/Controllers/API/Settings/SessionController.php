<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Session";
        if ($user->can($permission) || $user->user_type =="teacher" || $user->user_type =="student") {
            return Session::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Session";
        if ($user->can($permission)) {
            $request->validate([
                "session" => "required|numeric"
            ]);
            $session = new Session;
            $session->session = $request->session;
            if (Session::where("session", $request->session)->first() == null) {
                if ($session->save()) {
                    return ResponseMessage::success("Session Created!");
                } else {
                    return ResponseMessage::fail("Couldn't Create Session!");
                }
            } else {
                return ResponseMessage::fail("Session Exists!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Session";
        if ($user->can($permission)) {
            $request->validate([
                "session" => "required"
            ]);
            $session = Session::find($id);
            if ($session != null) {
                $session->session = $request->session;
                if ($session->save())
                    return ResponseMessage::success("Session Updated!");
                else
                    return ResponseMessage::fail("Couldn't Update Session!");
            } else
                return ResponseMessage::fail("Session Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Session";
        if ($user->can($permission)) {
            $session = Session::find($id);
            if ($session != null) {
                if ($session->delete())
                    return ResponseMessage::success("Session Deleted!");
                else
                    return ResponseMessage::fail("Couldn't Delete Session!");
            } else
                return ResponseMessage::fail("Session Doesn't Exist!");
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
