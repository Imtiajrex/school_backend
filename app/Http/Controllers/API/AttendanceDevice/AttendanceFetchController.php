<?php

namespace App\Http\Controllers\API\AttendanceDevice;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\InstituteInfo;
use Illuminate\Http\Request;

class AttendanceFetchController extends Controller
{
    public function assignID(Request $request)
    {
        $user = $request->user();
        $permission = "Assign ID Card";
        if ($user->can($permission)) {
            $request->validate([
                "id" => "required",
                "card" => "required",
            ]);
            $school_info = InstituteInfo::first();
            $id = $school_info->institute_shortform . $request->id;
            $data = array("operation" => "add_user", "auth_user" => env("ATTENDANCE_DEVICE_USER"), "signature_type" => "card", "auth_code"
            => env("ATTENDANCE_DEVICE_TOKEN"), "username" => $id, "signature" => $request->card, "device_id" => $school_info->attendance_device);


            $datapayload = json_encode($data);
            $api_request = curl_init(env("ATTENDANCE_DEVICE_URL"));
            curl_setopt($api_request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($api_request, CURLINFO_HEADER_OUT, true);
            curl_setopt($api_request, CURLOPT_POST, true);
            curl_setopt($api_request, CURLOPT_POSTFIELDS, $datapayload);
            curl_setopt($api_request, CURLOPT_HTTPHEADER, array('Content-Type:
			application/json', 'Content-Length: ' . strlen($datapayload)));
            $result = curl_exec($api_request);
            $replace_syntax = str_replace('{"log":', "", $result);

            $result_array = json_decode($replace_syntax, true);
            if ($result_array != null) {
                if ($result_array['success'] == 'User Successfully Registered') {
                    return ResponseMessage::success("Card Successfully Assigned!");
                } else {
                    return ResponseMessage::fail("Card Assignment Failed!");
                }
            } else {
                return ResponseMessage::fail("Card Assignment Failed!"+$result);
            }
        }
    }
}
