<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileUploader;
use App\Http\Controllers\ResponseMessage;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Gallery";
        if ($user->can($permission)) {
            return Gallery::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Upload Image";
        if ($user->can($permission)) {
            $request->validate([
                "image" => "required|file",
            ]);

            $image_entry = new Gallery;

            $image_name = null;
            $image_file = FileUploader::upload($request->image);

            if (array_key_exists('error', $image_file)) {
                return ResponseMessage::fail($image_file["error"]);
            } else if (array_key_exists('image_name', $image_file)) {
                $image_name = $image_file["image_name"];
            }

            $image_entry->image_name = $image_name;


            if ($request->caption != null)
                $image_entry->caption = $request->caption;

            if ($request->parent_album_id != null)
                $image_entry->parent_album_id = $request->parent_album_id;

            if ($image_entry->save()) {
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
            $image_entry = Gallery::find($id);
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
