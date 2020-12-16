<?php

namespace App\Http\Controllers\API\Payments;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\PaymentCategory;
use App\Models\Students;
use App\Models\StudentsPayment;
use App\Models\StudentsPaymentAccount;
use App\Models\StudentsPaymentReceipt;
use Illuminate\Http\Request;

class StudentsPaymentReceiptController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Payment Receipt";
        $user = $request->user();
        if ($user->can($permission)) {
            if ($request->receipt_id != null)
                return StudentsPaymentReceipt::where("receipt_id", $request->receipt_id)->get();
            else if ($request->student_id != null)
                return StudentsPaymentReceipt::where("student_id", $request->student_id)->get();
            else
                return ResponseMessage::fail("Nothing To Send!");
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Add Payment Receipt";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "payment_ids" => "required|json",
                "student_id" => "required|numeric"
            ]);
            $payments = json_decode($request->payment_ids);
            $student_id = $request->student_id;

            $receipt_id = (int)microtime(true);
            $date = date('Y-m-d');

            $receipt = [];
            foreach ($payments as $payment) {
                $std_payment = StudentsPayment::find($payment);
                if ($std_payment == null)
                    return ResponseMessage::fail("Some Payment Doesn't Exist!");

                array_push($receipt, ["id" => $receipt_id, 'student_id' => $student_id, 'payment_id' => $payment, "date" => $date]);
            }

            if (StudentsPayment::insert($receipt)) {
                return ResponseMessage::success("Created A Receipt!");
            } else {
                return ResponseMessage::fail("Failed To Create Receipt!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Payment Receipt";
        $user = $request->user();
        if ($user->can($permission)) {
            if (StudentsPayment::find($id) != null) {
                if (StudentsPayment::destroy($id)) {
                    return ResponseMessage::success("Payment Receipt Deleted!");
                }
            } else {
                return ResponseMessage::fail("Payment Receipt Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
