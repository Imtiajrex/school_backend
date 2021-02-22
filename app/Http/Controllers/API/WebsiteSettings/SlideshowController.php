<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\Slideshow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SlideshowController extends Controller
{
    public function index(Request $request)
    {
        return Slideshow::all();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Upload Image";
        if ($user->can($permission)) {
            $request->validate([
                "image" => "required|file",
                "caption" => "required|string"
            ]);
            $count = DB::table("slideshows")->count();
            if ($count >= 5) {
                return ResponseMessage::fail("Can't Upload More Than Five Pictures for slideshow!");
            }
            $file = $request->file('image');
            $data = [];
            $caption = $request->caption;


            $image_file = FileUploader::upload($file);

            if (array_key_exists('error', $image_file)) {
                return ResponseMessage::fail($image_file["error"]);
            } else if (array_key_exists('image_name', $image_file)) {
                array_push($data, ["caption" => $caption, "image_name" => $image_file["image_name"]]);
            }

            if (Slideshow::insert($data)) {
                return ResponseMessage::success("Image Uploaded Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Image!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Image";
        if ($user->can($permission)) {
            $image_entry = Slideshow::find($id);
            if ($image_entry != null) {
                $image_name = $image_entry->image_name;
                if ($image_entry->delete()) {
                    Storage::delete("public/images/" . $image_name);
                    return ResponseMessage::success("Image Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Image!");
                }
            } else {
                return ResponseMessage::fail("Image Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
