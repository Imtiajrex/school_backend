<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Products;
use App\Models\ProductsSold;
use App\Models\Employee;
use App\Models\PaymentCategory;
use App\Models\PaymentRequest;
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
                "values" => "required",
                "product_issuer_type" => "required",
                "product_issued_to_id" => "required",
            ]);

            $data = [];
            $date = date("Y-m-d");
            $payment_category = "Products";
            $student_id = Students::where("student_id",$request->product_issued_to_id)->first();

            $student_id = $student_id->id;
            $payment_info = "";
            $payment_amount = 0;
            foreach ($request->values as $v) {
                array_push($data, ["buyer_type" => $request->product_issuer_type, "buyer_id" => $student_id, "date" => $date, "product_id" => $v["product_id"], "price" => $v["amount"], "quantity" => $v["quantity"]]);
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
            

            if (ProductsSold::insert($data)) {
                foreach ($request->values as $v) {
                    $product = Products::find($v["product_id"]);
                    $product->stock = $product->stock - $v["quantity"];
                    $product->save();
                }
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

}
