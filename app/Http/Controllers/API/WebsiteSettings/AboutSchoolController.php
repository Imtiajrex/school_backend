<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\AboutSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutSchoolController extends Controller
{
    public function index(Request $request)
    {
        if($request->home)
        return AboutSchool::first();
        return AboutSchool::all();
    }

    public function update(Request $request,$id)
    {
        $user = $request->user();
        $permission = "Update AboutSchool";
        if ($user->can($permission)) {
            $request->validate([
                "image" => "required|file",
                "title" => "required|string",
                "content" => "required|string",
            ]);

            $file = $request->file('image');
            $data = [];

            $about_school = AboutSchool::where("id",$id);
            $prev_image = $about_school->first()->image;
            $image_file = FileUploader::upload($file);

            if (array_key_exists('error', $image_file)) {
                return ResponseMessage::fail($image_file["error"]);
            } else if (array_key_exists('image_name', $image_file)) {
                array_push($data, ["title" => $request->title,"content" => $request->content, "image" => $image_file["image_name"]]);
            }



            if ($about_school->update(...$data)) {

                Storage::delete("public/images/" . $prev_image);
                return ResponseMessage::success("AboutSchool Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload AboutSchool!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

}
