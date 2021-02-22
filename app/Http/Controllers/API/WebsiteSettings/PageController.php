<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Pages;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->option) {
            return Pages::get(["page_title as text", "id as value"]);
        } else if ($request->menu) {
            return Pages::where("active", true)->get(["page_title", "id"]);
        }else if($request->page_id){
            return Pages::find($request->page_id);
        }

        return Pages::selectRaw("pages.*,case when pages.active = 1 then 'Active' else 'Inactive' end as page_status")->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Pages";
        if ($user->can($permission)) {
            $request->validate([
                "page_title" => "required|string",
                "page_content" => "required",
                "active" => 'required'
            ]);

            $page = new Pages();

            $page->page_title = $request->page_title;
            $page->page_content = $request->page_content;
            $page->active = $request->active;


            if ($page->save()) {
                return ResponseMessage::success("Page Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Pages!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $user = $request->user();
        $permission = "Update Pages";
        if ($user->can($permission)) {
            $request->validate([
                "page_title" => "required|string",
                "page_content" => "required",
                "active" => 'required'
            ]);

            $page = Pages::find($id);
            if ($page == null)
                return ResponseMessage::fail("Pages Not Found!");

            $page->page_title = $request->page_title;
            $page->page_content = $request->page_content;
            $page->active = $request->active;



            if ($page->save()) {
                return ResponseMessage::success("Pages Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update Pages!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Pages";
        if ($user->can($permission)) {
            $page = Pages::find($id);
            if ($page != null) {
                if ($page->delete()) {
                    return ResponseMessage::success("Pages Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Pages!");
                }
            } else {
                return ResponseMessage::fail("Pages Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
