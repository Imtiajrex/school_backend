<?php

namespace App\Http\Controllers\API\Library;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Books;
use App\Models\BooksSold;
use App\Models\Employee;
use App\Models\PaymentCategory;
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
                "book_id" => "required|numeric",
                "quantity" => "required|numeric",
                "price" => "required|numeric",
                "paid_amount" => "required|numeric",
                "buyer_type" => "required|string",
                "buyer_id" => "required|numeric",
            ]);


            $books_sold = new BooksSold;
            $books_sold->book_id = $request->book_id;
            if (Books::find($request->book_id) == null)
                return ResponseMessage::fail("Couldn't Find Books!");

            $payment_id = -1;


            $books_sold->quantity = $request->quantity;
            $books_sold->price = $request->price;
            $books_sold->buyer_type = $request->buyer_type;

            if ($request->buyer_type == 'student') {
                if (Students::find($request->buyer_id) == null)
                    return ResponseMessage::fail("Couldn't Find ID!");

                $date = date('Y-m-d');
                $payment = $this->studentsPayment($request->price, $request->paid_amount, $request->buyer_id, $date);
                if ($payment != false) {
                    $payment_id = $payment;
                } else {
                    return "Failed To Add Students Payment!";
                }
            } else if ($request->buyer_type == 'employee') {
                if (Employee::find($request->buyer_id) == null)
                    return ResponseMessage::fail("Couldn't Find ID!");
            } else if ($request->buyer_type == 'other') {
                $request->buyer_id = 0;
            } else {
                return ResponseMessage::fail("Invalid Buyer Type!");
            }
            $books_sold->buyer_id = $request->buyer_id;

            $books_sold->payment_id = $payment_id;
            $books_sold->date = $date;
            if ($books_sold->save()) {
                $book = Books::find($request->book_id);
                $book->stock = $book->stock - $request->quantity;
                $book->save();
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

    public function studentsPayment($price, $paid_amount, $student_id, $date)
    {
        $receipt_id = (int)microtime(true);

        $payment = new StudentsPayment();
        $payment->payment_category = "Others";
        $payment->payment_info = "Books";
        $payment->payment_amount = $price;
        $payment->paid_amount = $paid_amount;

        $payment->group_id = $receipt_id;
        $payment->student_id = $student_id;
        $payment->date = $date;


        if ($payment->save()) {
            if ($price >  $paid_amount)
                StudentsPaymentAccount::insert(["amount_difference" => $price - $paid_amount, "student_id" => $student_id, "payment_id" => $payment->id, "status" => "DUE"]);

            StudentsPaymentReceipt::insert(["id" => $receipt_id, "student_id" => $student_id, "payment_id" => $payment->id, 'date' => $date]);

            return $payment->id;
        } else
            return false;
    }
}
