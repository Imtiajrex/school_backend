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
        $permission = "View InstituteInfo";
        $user = $request->user();
        if ($user->can($permission)) {
            return InstituteInfo::first();
        } else {
            ResponseMessage::unauthorized($permission);
        }
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
                "institute_phonenumbers" => 'required|json',
                "institute_email" => "required|string",
                "social_media" => "required|json",
                "institute_address" => "required|json"
            ]);
            $InstituteInfo = InstituteInfo::find($id);
            if ($InstituteInfo != null) {
                $InstituteInfo->institute_name = $request->institute_name;
                $InstituteInfo->institute_motto = $request->institute_motto;
                $InstituteInfo->institute_shortform = $request->institute_shortform;
                $InstituteInfo->institute_phonenumbers = $request->institute_phonenumbers;
                $InstituteInfo->institute_email = $request->institute_email;
                $InstituteInfo->social_media = $request->social_media;
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
