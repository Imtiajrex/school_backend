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
            return IssuedBooks::all();
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
                "book_id" => "required|numeric",
                "book_issuer_type" => "required|string",
                "book_issued_to_id" => "required|numeric",
                "book_issued_date" => "required|date",
                "book_return_date" => "required|date",
                "issue_status" => "required|string"
            ]);


            $issued_books = new IssuedBooks;
            if (Books::find($request->book_id) == null)
                return ResponseMessage::fail("Couldn't Find The Book");
            $issued_books->book_id = $request->book_id;
            $issued_books->book_issuer_type = $request->book_issuer_type;

            if ($request->book_issuer_type == 'student') {
                if (Students::find($request->book_issued_to_id) == null)
                    return ResponseMessage::fail("Couldn't Find Student");
            } else if ($request->book_issuer_type == 'teacher') {
                if (Employee::find($request->book_issued_to_id) == null)
                    return ResponseMessage::fail("Couldn't Find Teacher");
            } else {
                return ResponseMessage::fail("Issuer Type Not Found!");
            }

            $issued_books->book_issued_to_id = $request->book_issued_to_id;
            $issued_books->book_issued_date = $request->book_issued_date;
            $issued_books->book_return_date = $request->book_return_date;
            $issued_books->issue_status = $request->issue_status;
            if ($issued_books->save()) {
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
                "book_id" => "required|numeric", "book_issuer_type" => "required|string", "book_issued_to_id" => "required|numeric", "book_issued_date" => "required|date", "book_return_date" => "required|date", "returned_at" => "required|date", "issue_status" => "required|string"
            ]);

            $issued_books = IssuedBooks::find($id);
            if ($issued_books != null) {
                $issued_books->book_id = $request->book_id;
                $issued_books->book_issuer_type = $request->book_issuer_type;

                if ($request->book_issuer_type == 'student') {
                    if (Students::find($request->book_issued_to_id) == null)
                        return ResponseMessage::fail("Couldn't Find Student");
                } else if ($request->book_issuer_type == 'teacher') {
                    if (Employee::find($request->book_issued_to_id) == null)
                        return ResponseMessage::fail("Couldn't Find Teacher");
                } else {
                    return ResponseMessage::fail("Issuer Type Not Found!");
                }

                $issued_books->book_issued_to_id = $request->book_issued_to_id;
                $issued_books->book_issued_date = $request->book_issued_date;
                $issued_books->book_return_date = $request->book_return_date;
                $issued_books->returned_at = $request->returned_at;
                $issued_books->issue_status = $request->issue_status;

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
