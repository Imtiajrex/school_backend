<?php

namespace App\Http\Controllers\API\WebsiteSettings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\SubPages;
use Illuminate\Http\Request;

class SubPageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->menu) {
            return SubPages::where("active", true)->get(["page_title", "id", "page_parent"]);
        }else if($request->id){
            return SubPages::find($request->id);
        }
        return SubPages::leftJoin("pages", "pages.id", "=", "subpages.page_parent")->selectRaw("subpages.*,case when subpages.active = 1 then 'Active' else 'Inactive' end as page_status,pages.page_title as page_parent_title")->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Pages";
        if ($user->can($permission)) {
            $request->validate([
                "page_title" => "required|string",
                "page_content" => "required",
                "page_parent" => "required|numeric",
            ]);

            $page = new SubPages();

            $page->page_title = $request->page_title;
            $page->page_content = $request->page_content;
            $page->page_parent = $request->page_parent;
            $page->active = $request->active;


            if ($page->save()) {
                return ResponseMessage::success("Page Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Upload Sub Pages!");
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
                "page_parent" => "required|numeric",
            ]);

            $page = SubPages::find($id);
            if ($page == null)
                return ResponseMessage::fail("Sub Pages Not Found!");

            $page->page_title = $request->page_title;
            $page->page_content = $request->page_content;
            $page->page_parent = $request->page_parent;
            $page->active = $request->active;



            if ($page->save()) {
                return ResponseMessage::success("Sub Pages Updated Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Update Sub Pages!");
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
            $page = SubPages::find($id);
            if ($page != null) {
                if ($page->delete()) {
                    return ResponseMessage::success("Sub Pages Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Sub Pages!");
                }
            } else {
                return ResponseMessage::fail("Sub Pages Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
