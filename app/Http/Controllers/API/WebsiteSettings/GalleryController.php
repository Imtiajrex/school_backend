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
        if($request->album_id){
            return Gallery::where("parent_album_id",$request->album_id)->get();
        }
        return Gallery::leftJoin("album", "album.id", "=", "gallery.parent_album_id")->get(["gallery.*", "album.album_name"]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Upload Image";
        if ($user->can($permission)) {
            $request->validate([
                "image" => "required",
            ]);
            $files = $request->file('image');
            $data = [];
            $parent_album_id = -1;
            if ($request->parent_album_id)
                $parent_album_id = $request->parent_album_id;
            $caption = null;
            if ($request->caption)
                $caption = $request->caption;


            if ($request->hasFile('image')) {
                foreach ($files as $file) {
                    $image_file = FileUploader::upload($file);

                    if (array_key_exists('error', $image_file)) {
                        return ResponseMessage::fail($image_file["error"]);
                    } else if (array_key_exists('image_name', $image_file)) {
                        $d = [];
                        if ($parent_album_id != -1)
                            $d["parent_album_id"] = $parent_album_id;
                        if ($caption != null)
                            $d["caption"] = $caption;
                        $d["image_name"] = $image_file["image_name"];
                        array_push($data, $d);
                    }
                }
            }
            if (Gallery::insert($data)) {
                return ResponseMessage::success("Image Uploaded Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Image!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Image";
        if ($user->can($permission)) {
            $image = Gallery::find($id);
            if ($request->parent_album_id)
                $image->parent_album_id = $request->parent_album_id;
            if ($request->caption)
                $image->caption = $request->caption;


            if ($image->save()) {
                return ResponseMessage::success("Image Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Updated Image!");
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
