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

class StudentsPaymentController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Payment";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "student_id" => "required|numeric",
                "from" => "required|date",
                "to" => "required|date"
            ]);
            $from = $request->from;
            $to = $request->to;
            return StudentsPayment::where("student_id", $request->student_id)->whereBetween("date", [$from, $to])->get();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Add Payment";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "date" => "required|date",
                "student_id" => "required|numeric",
                "payments" => "required"
            ]);
            $date = $request->date;
            $student_id = $request->student_id;
            $payments = $request->payments;




            if (Students::find($student_id) == null)
                return ResponseMessage::fail("Student Not Found!");

            $receipt_id = (int)microtime(true);


            $data_to_add = $this->paymentArrayConverter($payments, $receipt_id, $student_id, $date);


            if (StudentsPayment::insert($data_to_add)) {
                $student_payments = StudentsPayment::where("group_id", $receipt_id);
                $payment_ids_query = $student_payments->get();
                $receipt = [];
                foreach ($payment_ids_query as $payment_id) {
                    array_push($receipt, ["id" => $receipt_id, 'student_id' => $student_id, 'payment_id' => $payment_id['id'], "date" => $date]);
                }
                if (StudentsPaymentReceipt::insert($receipt)) {
                    if (!$this->insertDueAdvance($payment_ids_query))
                        return ResponseMessage::fail("Failed To Edit Balance");

                    return ResponseMessage::success("Inserted A Payment Record!");
                } else {
                    $student_payments->delete();
                    return ResponseMessage::success("Failed To Create Receipt!");
                }
            } else {
                return ResponseMessage::fail("Payment Record Insertion Failed!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Payment Record";
        $user = $request->user();
        if ($user->can($permission)) {
            if (StudentsPayment::find($id) != null) {
                if (StudentsPayment::destroy($id)) {
                    return ResponseMessage::success("Payment Record Deleted!");
                }
            } else {
                return ResponseMessage::fail("Payment Record Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function insertDueAdvance($payments)
    {
        $due_arr = [];
        foreach ($payments as $payment) {
            $calc_amount = 0;
            $payment_amount = $payment->payment_amount;
            $paid_amount = $payment->paid_amount;

            if ($payment_amount !== $paid_amount) {
                $calc_amount = $payment_amount - $paid_amount;
            }

            if ($calc_amount != 0)
                array_push($due_arr, ["student_id" => $payment->student_id, "payment_id" => $payment->id, "amount_difference" => $calc_amount, "status" => "DUE"]);
        }
        if (count($due_arr) > 0)
            if (!StudentsPaymentAccount::insert($due_arr))
                return false;

        return true;
    }

    public function paymentArrayConverter($payments, $receipt_id, $student_id, $date)
    {
        $payment_arr = [];
        foreach ($payments as $payment) {
            $payment_category = $payment["payment_category"];
            $payment_info = $payment["payment_info"];
            $payment_amount = $payment["payment_amount"];
            $paid_amount = $payment["paid_amount"];
            $payment_info = $payment_info==null?'':$payment_info;
            array_push($payment_arr, ["student_id" => $student_id, "date" => $date, "payment_category" => $payment_category, "payment_info" => $payment_info, "payment_amount" => $payment_amount, "paid_amount" => $paid_amount, "group_id" => $receipt_id]);
        }
        return $payment_arr;
    }
}
