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
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $receipts = [];
            if ($request->receipt_id != null)
                $receipts = StudentsPaymentReceipt::where("students_payment_receipt.id", $request->receipt_id);
            else if ($request->student_id != null) {
                $student_id = Students::where("student_id", $request->student_id)->first()->id;
                $receipts = StudentsPaymentReceipt::where("students_payment_receipt.student_id", $student_id);
            } else
                return ResponseMessage::fail("Nothing To Send!");

            return $receipts->leftJoin("students_payment", 'students_payment_receipt.payment_id', '=', 'students_payment.id')->leftJoin("students", 'students.id', '=', 'students_payment_receipt.student_id')->get(['students_payment_receipt.*', 'students_payment.payment_category', 'students_payment.payment_info', 'students_payment.payment_amount', 'students_payment.paid_amount','students.student_name','students.student_id as student_identifier']);
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
