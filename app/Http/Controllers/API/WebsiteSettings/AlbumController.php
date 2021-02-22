<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Album;
use App\Models\Gallery;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        if ($request->option) {
            return Album::get(["album_name as text", "id as value"]);
        }else if($request->home){
            return Album::leftJoin('gallery',function($join){
                $join->on("gallery.parent_album_id","=","album.id");
            })->groupBy("gallery.parent_album_id")->get(["album.id","album_name","gallery.image_name"]);
        }
        return Album::all();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Album";
        if ($user->can($permission)) {
            $request->validate([
                "album_name" => "required|string",
            ]);

            $album_entry = new Album;

            $album_entry->album_name = $request->album_name;


            if ($album_entry->save()) {
                return ResponseMessage::success("Album Uploaded Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Album!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $permission = "Update Album";
        if ($user->can($permission)) {
            $request->validate([
                "album_name" => "required|string",
            ]);

            $album_entry = Album::find($id);
            if ($album_entry == null)
                return ResponseMessage::fail("Album Not Found!");

            $album_entry->album_name = $request->album_name;


            if ($album_entry->save()) {
                return ResponseMessage::success("Album Uploaded Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Album!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Album";
        if ($user->can($permission)) {
            $album = Album::find($id);
            if ($album != null) {
                if ($album->delete()) {
                    return ResponseMessage::success("Album Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Album!");
                }
            } else {
                return ResponseMessage::fail("Album Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

}
