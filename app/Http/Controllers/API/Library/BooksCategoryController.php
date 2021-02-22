<?php

namespace App\Http\Controllers\API\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\BooksCategory;
use Illuminate\Http\Request;

class BooksCategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Book Category";
        if ($user->can($permission)) {
            return BooksCategory::get(["id","category_name as text","category_name as value","category_name"]);
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Book Category";
        if ($user->can($permission)) {
            $request->validate([
                "category_name" => "required|string",
            ]);


            $books = new BooksCategory;
            $books->category_name = $request->category_name;
            if ($books->save()) {
                return ResponseMessage::success("Book Category Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Create Book Category!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Book Category";
        if ($user->can($permission)) {
            $request->validate([
                "category_name" => "required|string",
            ]);


            $books = new BooksCategory;

            $books = BooksCategory::find($id);
            if ($books != null) {
                $books->category_name = $request->category_name;
                if ($books->save()) {
                    return ResponseMessage::success("Book Category Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Updated Book Category!");
                }
            } else
                return ResponseMessage::fail("Book Category Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Book Category";
        if ($user->can($permission)) {
            $books = BooksCategory::find($id);
            if ($books != null) {
                if ($books->delete()) {
                    return ResponseMessage::success("Book Category Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Book Category!");
                }
            } else {
                return ResponseMessage::fail("Book Category Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
