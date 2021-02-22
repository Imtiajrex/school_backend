<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Weekdays;
use Illuminate\Http\Request;

class WeekdaysController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Weekday";
        $user = $request->user();
        if ($user->can($permission) || $user->user_type == "student" || $user->user_type == "teacher") {
            $weekdays =  Weekdays::all();
            $days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            $d = [];
            foreach ($weekdays as $day) {
                $day["day_name"] = $days[$day->day];
                if ($request->attendance)
                    array_push($d, $day->day);
            }
            if ($request->attendance)
                return $d;
            return $weekdays;
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Weekday";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "day" => "required"
            ]);
            if (Weekdays::where(["day" => $request->day])->first() == null) {
                $Weekdays = new Weekdays;
                $Weekdays->day = $request->day;
                if ($Weekdays->save()) {
                    return ResponseMessage::success("Weekday Created!");
                } else {
                    return ResponseMessage::fail("Weekday Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("Weekday Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
    public function destroy($id, Request $request)
    {
        $permission = "Delete Weekday";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Weekdays::find($id) != null) {
                if (Weekdays::destroy($id)) {
                    return ResponseMessage::success("Weekday Deleted!");
                }
            } else {
                return ResponseMessage::fail("Weekday Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
