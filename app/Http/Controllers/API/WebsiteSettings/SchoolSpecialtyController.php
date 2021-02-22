<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\SchoolSpecialty;
use Illuminate\Http\Request;

class SchoolSpecialtyController extends Controller
{
    public function index(Request $request)
    {
        return SchoolSpecialty::all();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create SchoolSpecialty";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required|string",
            ]);

            $school_specialty = new SchoolSpecialty();

            $school_specialty->title = $request->title;
            $school_specialty->content = $request->content;


            if ($school_specialty->save()) {
                return ResponseMessage::success("Page Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload SchoolSpecialty!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $permission = "Update SchoolSpecialty";
        if ($user->can($permission)) {
            $request->validate([
                "title" => "required|string",
                "content" => "required|string",
            ]);

            $school_specialty = SchoolSpecialty::find($id);
            if ($school_specialty == null)
                return ResponseMessage::fail("SchoolSpecialty Not Found!");

            $school_specialty->title = $request->title;
            $school_specialty->content = $request->content;


            if ($school_specialty->save()) {
                return ResponseMessage::success("SchoolSpecialty Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update SchoolSpecialty!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete SchoolSpecialty";
        if ($user->can($permission)) {
            $school_specialty = SchoolSpecialty::find($id);
            if ($school_specialty != null) {
                if ($school_specialty->delete()) {
                    return ResponseMessage::success("SchoolSpecialty Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete SchoolSpecialty!");
                }
            } else {
                return ResponseMessage::fail("SchoolSpecialty Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
