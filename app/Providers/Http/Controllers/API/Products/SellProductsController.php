<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Products;
use App\Models\ProductsSold;
use App\Models\Employee;
use App\Models\PaymentCategory;
use App\Models\Students;
use App\Models\StudentsPayment;
use App\Models\StudentsPaymentAccount;
use App\Models\StudentsPaymentReceipt;
use Illuminate\Http\Request;

class SellProductsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View ProductsSold";
        if ($user->can($permission)) {
            if ($request->student_id != null)
                return ProductsSold::where(["buyer_id" => $request->student_id])->get();
            else if ($request->from != null && $request->to != null) {
                $from = $request->from;
                $to = $request->to;

                return ProductsSold::whereBetween("date", [$from, $to])->get();
            }
            return ProductsSold::all();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Sell Products";
        if ($user->can($permission)) {
            $request->validate([
                "product_id" => "required|numeric",
                "quantity" => "required|numeric",
                "price" => "required|numeric",
                "paid_amount" => "required|numeric",
                "buyer_type" => "required|string",
                "buyer_id" => "required|numeric",
            ]);


            $products_sold = new ProductsSold;
            $products_sold->product_id = $request->product_id;
            if (Products::find($request->product_id) == null)
                return ResponseMessage::fail("Couldn't Find Products!");

            $payment_id = -1;


            $products_sold->quantity = $request->quantity;
            $products_sold->price = $request->price;
            $products_sold->buyer_type = $request->buyer_type;

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
            $products_sold->buyer_id = $request->buyer_id;

            $products_sold->payment_id = $payment_id;
            $products_sold->date = $date;
            if ($products_sold->save()) {
                $product = Products::find($request->product_id);
                $product->stock = $product->stock - $request->quantity;
                $product->save();
                return ResponseMessage::success("Products Sold Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Sell Products!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete ProductsSold";
        if ($user->can($permission)) {
            $products_sold = ProductsSold::find($id);
            if ($products_sold != null) {
                StudentsPayment::destroy($products_sold->payment_id);
                if ($products_sold->delete()) {
                    return ResponseMessage::success("Products Sold Record Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Products Sold Record!");
                }
            } else {
                return ResponseMessage::fail("Products Sold Record Doesn't Exist!");
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
        $payment->payment_info = "Products";
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
