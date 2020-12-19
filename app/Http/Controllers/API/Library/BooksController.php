<?php

namespace App\Http\Controllers\API\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Books;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Books";
        if ($user->can($permission)) {
            return Books::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Books";
        if ($user->can($permission)) {
            $request->validate([
                "book_name" => "required|string",
                "author_name" => "required|string",
                "category_name" => "required|string",
                "shelf_no" => "required|numeric",
                "price" => "required|numeric",
                "stock" => "required|numeric",
            ]);


            $books = new Books;
            $books->book_name = $request->book_name;
            $books->author_name = $request->author_name;
            $books->category_name = $request->category_name;
            $books->shelf_no = $request->shelf_no;
            $books->price = $request->price;
            $books->stock = $request->stock;
            $books->deleted = 0;
            if ($books->save()) {
                return ResponseMessage::success("Book Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Create Book!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Books";
        if ($user->can($permission)) {
            $request->validate([
                "book_name" => "required|string",
                "author_name" => "required|string",
                "category_name" => "required|string",
                "shelf_no" => "required|numeric",
                "price" => "required|numeric",
                "stock" => "required|numeric",
            ]);


            $books = Books::find($id);
            if ($books != null) {
                $books->book_name = $request->book_name;
                $books->author_name = $request->author_name;
                $books->category_name = $request->category_name;
                $books->shelf_no = $request->shelf_no;
                $books->price = $request->price;
                $books->stock = $request->stock;

                if ($books->save()) {
                    return ResponseMessage::success("Books Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Books!");
                }
            } else
                return ResponseMessage::fail("Books Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Books";
        if ($user->can($permission)) {
            $books = Books::find($id);
            if ($books != null) {
                if ($books->deleted = 1 && $books->save()) {
                    return ResponseMessage::success("Books Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Books!");
                }
            } else {
                return ResponseMessage::fail("Books Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
