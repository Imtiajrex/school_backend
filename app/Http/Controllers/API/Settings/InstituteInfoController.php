<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\InstituteInfo;
use Illuminate\Http\Request;

class InstituteInfoController extends Controller
{
    public function index(Request $request)
    {
        return InstituteInfo::get();
        if ($request->home)
            return InstituteInfo::first();
    }


    public function update($id, Request $request)
    {
        $permission = "Update InstituteInfo";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "institute_name" => 'required|string',
                "institute_motto" => 'required|string',
                "institute_shortform" => "required|string",
                "institute_phonenumbers" => 'required|string',
                "institute_email" => "required|string",
                "institute_facebook" => "required|string",
                "institute_youtube" => "required|string",
                "institute_address" => "required|string"
            ]);
            $InstituteInfo = InstituteInfo::find($id);
            if ($InstituteInfo != null) {
                $InstituteInfo->institute_name = $request->institute_name;
                $InstituteInfo->institute_motto = $request->institute_motto;
                $InstituteInfo->institute_shortform = $request->institute_shortform;
                $InstituteInfo->institute_phonenumbers = $request->institute_phonenumbers;
                $InstituteInfo->institute_email = $request->institute_email;
                $InstituteInfo->institute_facebook = $request->institute_facebook;
                $InstituteInfo->institute_youtube = $request->institute_youtube;
                $InstituteInfo->institute_address = $request->institute_address;
                if ($InstituteInfo->save()) {
                    return ResponseMessage::success("Institute Info Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Institute Info!");
                }
            } else {
                return ResponseMessage::fail("Institute Info Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
