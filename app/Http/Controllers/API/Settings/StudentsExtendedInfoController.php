<?php

namespace App\Http\Controllers\API\Settings;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\StudentsExtendedInfo;
use Illuminate\Http\Request;

class StudentsExtendedInfoController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Students Extended Info";
        $user = $request->user();
        if ($user->can($permission)) {
            $data = StudentsExtendedInfo::all();
            if ($request->use) {
                foreach ($data as $record) {
                    $unready_options = explode(',', json_decode($record->options));
                    $ready_options = [];
                    if (count($unready_options) > 0) {
                        foreach ($unready_options as $option) {
                            array_push($ready_options, ["text" => $option, "value" => $option]);
                        }
                        $record["options"] = ($ready_options);
                    } else
                        $record["options"] = "";
                }
            }
            return $data;
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Students Extended Info";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "type" => "required|string",
                "placeholder" => "required|string",
            ]);
            $name = str_replace(" ", "_", strtolower($request->placeholder));
            if (StudentsExtendedInfo::where("name", $name)->first() == null) {
                $students_extended_info = new StudentsExtendedInfo;

                $students_extended_info->type = $request->type;
                if ($request->options) {
                    $students_extended_info->options = json_encode($request->options);
                }
                $students_extended_info->placeholder = $request->placeholder;
                $students_extended_info->name = $name;
                if ($students_extended_info->save()) {
                    return ResponseMessage::success("Extended Info Field Created!");
                } else {
                    return ResponseMessage::fail("Extended Info Field Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("Extended Info Field Exists. Change Name Value!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Students Extended Info";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "type" => "required|string",
                "placeholder" => "required|string",
            ]);
            $students_extended_info = StudentsExtendedInfo::find($id);
            if ($students_extended_info != null) {
                $name = str_replace(" ", "_", strtolower($request->placeholder));
                if (StudentsExtendedInfo::where("name", $name)->first() == null) {
                    $students_extended_info->type = $request->type;
                    if ($request->options) {
                        $students_extended_info->options = $request->options;
                    }
                    $students_extended_info->placeholder = $request->placeholder;
                    $students_extended_info->name = $name;
                    if ($students_extended_info->save()) {
                        return ResponseMessage::success("Extended Info Field Updated!");
                    } else {
                        return ResponseMessage::fail("Couldn't Update Extended Info Field!");
                    }
                } else {
                    return ResponseMessage::fail("Extended Info Field Name Exists. Change Name Value!");
                }
            } else {
                return ResponseMessage::fail("Extended Info Field Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Students Extended Info";
        $user = $request->user();
        if ($user->can($permission)) {
            if (StudentsExtendedInfo::find($id) != null) {
                if (StudentsExtendedInfo::destroy($id)) {
                    return ResponseMessage::success("Extended Info Field Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Extended Info Field!");
                }
            } else {
                return ResponseMessage::fail("Extended Info Field Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
