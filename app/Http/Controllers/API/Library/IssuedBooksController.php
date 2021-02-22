<?php

namespace App\Http\Controllers\API\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Books;
use App\Models\Employee;
use App\Models\IssuedBooks;
use App\Models\Students;
use Illuminate\Http\Request;

class IssuedBooksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View IssuedBooks";
        if ($user->can($permission)) {
            $request->validate(["book_issued_to_id"=>"required|numeric"]);
            return IssuedBooks::where("book_issued_to_id",$request->book_issued_to_id)->leftJoin("books","books.id","=","book_id")->orderBy("issue_status","asc")->get(["books.*","issued_books.*"]);
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create IssuedBooks";
        if ($user->can($permission)) {
            $request->validate([
                "book_issuer_type" => "required|string",
                "book_issued_to_id" => "required",
                "book_issued_date" => "required|date",
                "book_return_date" => "required|date",
                "book_ids" => "required"
            ]);

            $data = [];
            $student_id = Students::where("student_id",$request->book_issued_to_id)->first();

            $student_id = $student_id->id;
            foreach($request->book_ids as $book){
                array_push($data,["book_issuer_type"=>$request->book_issuer_type,"book_issued_to_id"=>$student_id,"book_issued_date"=>$request->book_issued_date,"book_return_date"=>$request->book_return_date,"book_id"=>$book,"issue_status"=>"issued"]);
            }
            if (IssuedBooks::insert($data)) {
                return ResponseMessage::success("Book Issued Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Issue Book!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update IssuedBooks";
        if ($user->can($permission)) {
            $request->validate([
                "returned_at"=>"required|date"
            ]);

            $issued_books = IssuedBooks::find($id);
            if ($issued_books != null) {
                $issued_books->returned_at = $request->returned_at;
                $issued_books->issue_status = "returned";

                if ($issued_books->save()) {
                    return ResponseMessage::success("IssuedBooks Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update IssuedBooks!");
                }
            } else
                return ResponseMessage::fail("IssuedBooks Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete IssuedBooks";
        if ($user->can($permission)) {
            $issued_books = IssuedBooks::find($id);
            if ($issued_books != null) {
                if ($issued_books->delete()) {
                    return ResponseMessage::success("IssuedBooks Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete IssuedBooks!");
                }
            } else {
                return ResponseMessage::fail("IssuedBooks Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
