<?php

namespace App\Http\Controllers\API\Messages;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\Employee;
use App\Models\SMSAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SMSController extends Controller
{
    public function quickSms(Request $request)
    {
        $user = $request->user();
        $permission = "Send SMS";
        if ($user->can($permission)) {
            $sms_account = SMSAccount::find(1);
            if ($sms_account->balance > 0) {
                $request->validate([
                    "phonenumbers" => "required|string",
                    "message" => "required|string",
                ]);

                $numbers = $request->phonenumbers;
                $to="";

                $numbers = explode(",",$numbers);
                $i=0;
                $sms_available = (int)($sms_account->balance/$sms_account->rate);
                foreach($numbers as $num){
                    if($i >=$sms_available) break;
                    if($i==0)$to=$num;
                    else $to=$to.",".$num;
                    $i++;
                }
                
                $token = env("SMS_TOKEN");
                $message = $request->message;

                $url = env("SMS_URL") . "/api.php?json";


                $data = array(
                    'to' => "$to",
                    'message' => "$message",
                    'token' => "$token"
                ); // Add parameters in key value
                
                $ch = curl_init(); // Initialize cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $smsresult = json_decode(curl_exec($ch));


                $sent = 0;
                $sent_numbers = "";
                $failed_numbers = "";
                foreach ($smsresult as $res) {
                    if ($res->status == "SENT") {
                        $sent++;
                        $sent_numbers = $sent_numbers . " " . $res->to;
                    } else {
                        $failed_numbers = $failed_numbers . " " . $res->to;
                    }
                }
                if ($sent > 0) {
                    $sms_account->balance = $sms_account->balance - $sent * $sms_account->rate;
                    $sms_account->total_sent_sms = $sms_account->total_sent_sms + $sent;
                    $sms_account->save();
                }
                $total_sms = count($smsresult);
                if ($sent == $total_sms) {
                    return ResponseMessage::success("Sent All Messages.\n Sent: " . $sent_numbers);
                } else {
                    return ResponseMessage::fail("Sent " . $sent . " Failed " . $total_sms - $sent . ".\n Sent: " . $sent_numbers . "\n Failed: " . $failed_numbers);
                }
            } else {
                return ResponseMessage::fail("SMS Balance Insufficient");
            }
        }
    }

    public function studentSms(Request $request)
    {
        $user = $request->user();
        $permission = "Send SMS";
        if ($user->can($permission)) {
            $request->validate([
                "session_id" => "required|numeric",
                "message" => "required|string",
            ]);
            $sms_account = SMSAccount::find(1);
            if ($sms_account->balance > 0) {
                $query = [];
                $query["session_id"] = $request->session_id;
                if ($request->class_id != -1) {
                    $query["class_id"] = $request->class_id;
                    if ($request->department_id != -1) {
                        $query["department_id"] = $request->department_id;
                        if ($request->student_id != -1) {
                            $query["class_has_students.student_id"] = $request->student_id;
                        }
                    }
                }
                $students = ClassHasStudents::where($query)->leftJoin("students", "students.id", "=", "class_has_students.student_id")->groupBy("class_has_students.session_id")->selectRaw("group_concat(primary_phone) as number")->first();

                print_r($students);


                $to = "";


                $numbers = explode(",",$students->number);
                $i=0;
                $sms_available = (int)($sms_account->balance/$sms_account->rate);
                foreach($numbers as $num){
                    if($i >=$sms_available) break;
                    if($i==0)$to=$num;
                    else $to=$to.",".$num;
                    $i++;
                }
                $token = env("SMS_TOKEN");
                $message = $request->message;

                $url = env("SMS_URL") . "/api.php?json";


                $data = array(
                    'to' => "$to",
                    'message' => "$message",
                    'token' => "$token"
                ); // Add parameters in key value
                $ch = curl_init(); // Initialize cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $smsresult = json_decode(curl_exec($ch));


                $sent = 0;
                $sent_numbers = "";
                $failed_numbers = "";
                foreach ($smsresult as $res) {
                    if ($res->status == "SENT") {
                        $sent++;
                        $sent_numbers = $sent_numbers . " " . $res->to;
                    } else {
                        $failed_numbers = $failed_numbers . " " . $res->to;
                    }
                }
                if (intval($sent) > 0) {
                    $sms_account->balance = $sms_account->balance - $sent * $sms_account->rate;
                    $sms_account->total_sent_sms = $sms_account->total_sent_sms + $sent;
                    $sms_account->save();
                }
                $total_sms = count($smsresult);
                if ($sent == $total_sms) {
                    return ResponseMessage::success("Sent All Messages.\n Sent: " . $sent_numbers);
                } else {
                    return ResponseMessage::fail("Sent " . $sent . " Failed " . $total_sms - $sent . ".\n Sent: " . $sent_numbers . "\n Failed: " . $failed_numbers);
                }
            }
        } else {
            return ResponseMessage::fail("SMS Balance Insufficient");
        }
    }

    public function employeeSms(Request $request)
    {
        $user = $request->user();
        $permission = "Send SMS";
        if ($user->can($permission)) {
            $request->validate([
                "employee_type" => "required",
                "message" => "required|string",
            ]);
            $sms_account = SMSAccount::find(1);
            if ($sms_account->balance > 0) {
                $query = [];
                $query["employee_type"] = $request->employee_type;
                if($request->employee_id != -1) $query["id"] = $request->employee_id;
                $employee = Employee::where($query)->selectRaw("group_concat(employee_primary_phone) as number,group_concat(employee_name) as names")->first();



                $to = "";


                $numbers = explode(",",$employee->number);
                $i=0;
                $sms_available = (int)($sms_account->balance/$sms_account->rate);
                foreach($numbers as $num){
                    if($i >=$sms_available) break;
                    if($i==0)$to=$num;
                    else $to=$to.",".$num;
                    $i++;
                }
                $token = env("SMS_TOKEN");
                $message = $request->message;

                $url = env("SMS_URL") . "/api.php?json";
                $data = array(
                    'to' => "$to",
                    'message' => "$message",
                    'token' => "$token"
                ); // Add parameters in key value
                $ch = curl_init(); // Initialize cURL
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $smsresult = json_decode(curl_exec($ch));


                $sent = 0;
                $sent_numbers = "";
                $failed_numbers = "";
                foreach ($smsresult as $res) {
                    if ($res->status == "SENT") {
                        $sent++;
                        $sent_numbers = $sent_numbers . " " . $res->to;
                    } else {
                        $failed_numbers = $failed_numbers . " " . $res->to;
                    }
                }
                if ($sent > 0) {
                    $sms_account->balance = $sms_account->balance - $sent * $sms_account->rate;
                    $sms_account->total_sent_sms = $sms_account->total_sent_sms + $sent;
                    $sms_account->save();
                }
                $total_sms = count($smsresult);
                if ($sent == $total_sms) {
                    return ResponseMessage::success("Sent All Messages.\n Sent: " . $employee->names);
                } else {
                    return ResponseMessage::fail("Sent " . $sent . " Failed " . $total_sms - $sent . ".\n Sent: " . $employee->names . "\n Failed: " . $failed_numbers);
                }
            }
        } else {
            return ResponseMessage::fail("SMS Balance Insufficient");
        }
    }

    public function getSMSAccount()
    {
        return SMSAccount::find(1);
    }

    public function updateSMSAccount(Request $request)
    {
        $user = $request->user();
        if ($user->user_type == "developer") {
            $request->validate([
                "balance" => "required",
                "rate" => "required"
            ]);

            $msg = SMSAccount::find(1);

            $msg->rate = $request->rate;
            $msg->balance = $request->balance;

            if ($msg->save()) {
                return ResponseMessage::success("SMS Account Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update SMS Account!");
            }
        } else {
            return ResponseMessage::unauthorized("Not Developer!");
        }
    }
}
