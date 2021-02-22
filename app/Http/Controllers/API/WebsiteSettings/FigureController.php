<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Figure;
use Illuminate\Http\Request;

class FigureController extends Controller
{
    public function index(Request $request)
    {
        if ($request->home)
            return Figure::first();
        return Figure::all();
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Figure";
        if ($user->can($permission)) {
            $request->validate([
                "students" => "required",
                "teachers" => "required",
                "result" => "required",
                "parent_satisfaction" => "required",
            ]);

            $data = [];

            $about_school = Figure::where("id", $id);

            array_push($data, ["students" => $request->students, "teachers" => $request->teachers, "result" => $request->result, "parent_satisfaction" => $request->parent_satisfaction]);




            if ($about_school->update(...$data)) {
                return ResponseMessage::success("Figure Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update Figure!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
}
