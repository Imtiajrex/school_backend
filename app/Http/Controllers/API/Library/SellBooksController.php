<?php

namespace App\Http\Controllers\API\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Books;
use App\Models\BooksSold;
use App\Models\Employee;
use App\Models\PaymentCategory;
use App\Models\PaymentRequest;
use App\Models\Students;
use App\Models\StudentsPayment;
use App\Models\StudentsPaymentAccount;
use App\Models\StudentsPaymentReceipt;
use Illuminate\Http\Request;

class SellBooksController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View BooksSold";
        if ($user->can($permission)) {
            if ($request->student_id != null)
                return BooksSold::where(["buyer_id" => $request->student_id])->get();
            else if ($request->from != null && $request->to != null) {
                $from = $request->from;
                $to = $request->to;

                return BooksSold::whereBetween("date", [$from, $to])->get();
            }
            return BooksSold::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Sell Book";
        if ($user->can($permission)) {
            $request->validate([
                "values" => "required",
                "book_issuer_type" => "required",
                "book_issued_to_id" => "required",
            ]);

            $data = [];
            $date = date("Y-m-d");
            $payment_category = "Products";
            $student_id = Students::where("student_id",$request->book_issued_to_id)->first();

            $student_id = $student_id->id;
            $payment_info = "";
            $payment_amount = 0;
            foreach ($request->values as $v) {
                array_push($data, ["buyer_type" => $request->book_issuer_type, "buyer_id" => $student_id, "date" => $date, "book_id" => $v["book_id"], "price" => $v["amount"], "quantity" => $v["quantity"]]);
                $payment_info = $payment_info.$v["info"]." (x".$v["quantity"].")\n";
                $payment_amount+=$v["amount"];
            }

            $student_payment_request = new PaymentRequest();
            $student_payment_request->student_id = $student_id;
            $student_payment_request->payment_info = $payment_info;
            $student_payment_request->payment_category = $payment_category;
            $student_payment_request->payment_amount = $payment_amount;
            $student_payment_request->date = $date;
            $student_payment_request->save();
            

            if (BooksSold::insert($data)) {
                foreach ($request->values as $v) {
                    $book = Books::find($v["book_id"]);
                    $book->stock = $book->stock - $v["quantity"];
                    $book->save();
                }
                return ResponseMessage::success("Book Sold Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Sell Book!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete BooksSold";
        if ($user->can($permission)) {
            $books_sold = BooksSold::find($id);
            if ($books_sold != null) {
                StudentsPayment::destroy($books_sold->payment_id);
                if ($books_sold->delete()) {
                    return ResponseMessage::success("Book Sold Record Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Book Sold Record!");
                }
            } else {
                return ResponseMessage::fail("Book Sold Record Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

}
