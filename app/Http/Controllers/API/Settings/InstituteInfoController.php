<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\InstituteInfo;
use Illuminate\Http\Request;

class InstituteInfoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->update)
            return InstituteInfo::get();
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
                "institute_phonenumbers" => 'required|string',
                "institute_email" => "required|email",
                "institute_facebook" => "required|string",
                "institute_youtube" => "required|string",
                "institute_address" => "required|string"
            ]);
            $InstituteInfo = InstituteInfo::find($id);
            if ($InstituteInfo != null) {
                $InstituteInfo->institute_name = $request->institute_name;
                $InstituteInfo->institute_motto = $request->institute_motto;
                $InstituteInfo->institute_phonenumbers = $request->institute_phonenumbers;
                $InstituteInfo->institute_email = $request->institute_email;
                $InstituteInfo->institute_facebook = $request->institute_facebook;
                $InstituteInfo->institute_youtube = $request->institute_youtube;
                $InstituteInfo->institute_address = $request->institute_address;


                if ($request->hasFile('institute_logo')) {
                    $image_file = FileUploader::upload($request->file('institute_logo'));

                    if (array_key_exists('error', $image_file)) {
                        return ResponseMessage::fail($image_file["error"]);
                    } else if (array_key_exists('image_name', $image_file)) {
                        $InstituteInfo->institute_logo = $image_file['image_name'];
                    }
                }
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
