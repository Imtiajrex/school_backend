<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        if ($request->id) {
            return Testimonial::find($request->id);
        }
        return Testimonial::all();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Testimonial";
        if ($user->can($permission)) {
            $request->validate([
                "image" => "required|file",
                "title" => "required|string",
                "content" => "required|string",
            ]);

            $file = $request->file('image');
            $data = [];


            $image_file = FileUploader::upload($file);

            if (array_key_exists('error', $image_file)) {
                return ResponseMessage::fail($image_file["error"]);
            } else if (array_key_exists('image_name', $image_file)) {
                array_push($data, ["name" => $request->title,"content" => $request->content, "image" => $image_file["image_name"]]);
            }



            if (Testimonial::insert($data)) {
                return ResponseMessage::success("Testimonial Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Testimonial!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Testimonial";
        if ($user->can($permission)) {
            $testimonial = Testimonial::find($id);
            $prev_image = $testimonial->image;
            if ($testimonial != null) {
                if ($testimonial->delete()) {
                    Storage::delete("public/images/" . $prev_image);
                    return ResponseMessage::success("Testimonial Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Testimonial!");
                }
            } else {
                return ResponseMessage::fail("Testimonial Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
