<?php

namespace App\Http\Controllers\API\Payments;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Accounts;
use App\Models\ClassHasStudents;
use App\Models\Students;
use App\Models\StudentsPayment;
use App\Models\StudentsPaymentAccount;
use App\Models\StudentsPaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsPaymentController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Payment";
        $user = $request->user();
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_identifier)) {
            if ($request->student_identifier) {
                $student_id = ClassHasStudents::where("student_identifier", $request->student_identifier)->first();
                $student_id = $student_id != null ? $student_id->id : null;
            } else
                $student_id = $request->student_id;
            if ($student_id) {
                $students_payment =  StudentsPayment::where(["students_payment.student_id" => $student_id]);
                $students_payment = $students_payment->leftJoin("class_has_students", "class_has_students.id", "=", "students_payment.student_id");
                $students_payment = $students_payment->leftJoin("students", "students.id", "=", "class_has_students.student_id");
                return $students_payment->get(["students_payment.id", "students.student_name", "students_payment.*"]);
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Payment";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "date" => "required|date",
                "student_id" => "required",
                "session_id" => "required|numeric",
                "payments" => "required"
            ]);
            $date = $request->date;
            $payments = $request->payments;
            $session_id = $request->session_id;


            $student_id = $request->student_id;
            $receipt_id = StudentsPaymentReceipt::max('id') + 1;


            $data_array = $this->paymentArrayConverter($payments, $receipt_id, $student_id, $session_id, $date);
            $data_to_add = $data_array[0];
            $account_arr = $data_array[1];


            if (StudentsPayment::insert($data_to_add)) {
                $student_payments = StudentsPayment::where("group_id", $receipt_id);
                $payment_ids_query = $student_payments->get();
                $receipt = [];
                $index = 0;
                $total_income = 0;
                foreach ($payment_ids_query as $payment_id) {
                    $account_arr[$index]['payment_id'] = $payment_id['id'];

                    $total_income += $account_arr[$index]["amount"];
                    array_push($receipt, ["id" => $receipt_id, 'student_id' => $student_id, 'payment_id' => $payment_id['id'], "date" => $date]);
                    ++$index;
                }
                Accounts::insert($account_arr);
                $account_balance = DB::table("account_balance")->where("id", 1)->first();
                $total_income += $account_balance->cash;
                DB::table("account_balance")->where("id", 1)->update(["cash" => $total_income]);
                if (StudentsPaymentReceipt::insert($receipt)) {

                    if (!$this->insertDueAdvance($payment_ids_query))
                        return ResponseMessage::fail("Failed To Enter Due Balance");

                    return response()->json(["message" => "Inserted A Payment Record!", "receipt_id" => $receipt_id]);
                } else {
                    return ResponseMessage::success("Failed To Create Receipt!");
                }
            } else {
                return ResponseMessage::fail("Payment Record Insertion Failed!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }
    public function update($id, Request $request)
    {

        $permission = "Update Payment";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "date" => "required|date",
                "payment_amount" => "required|numeric",
                "paid_amount" => "required|numeric"
            ]);
            $date = $request->date;
            if ($request->payment_info)
                $payment_info = $request->payment_info;
            $payment_amount = $request->payment_amount;
            $paid_amount = $request->paid_amount;
            $payment = StudentsPayment::find($id);
            if ($payment != null) {
                $student_id = $payment->student_id;
                if ($request->payment_info)
                    $payment->payment_info = $payment_info;
                $payment->payment_amount = $payment_amount;
                $payment->paid_amount = $paid_amount;
                if ($payment->save()) {
                    $due = StudentsPaymentAccount::where("payment_id", $id)->first();
                    Accounts::where("entry_category", "like", "%Student Payment%")->where("payment_id", $id)->update(["entry_category" => "Student Payment[Edited]"]);
                    if ($due != null) {
                        if ($payment_amount > $paid_amount)
                            $due->amount = $payment_amount - $paid_amount;
                        else
                            $due->delete();
                    } else {
                        if ($payment_amount > $paid_amount)
                            StudentsPaymentAccount::insert(["student_id" => $student_id, "payment_id" => $id, "amount" => $payment_amount - $paid_amount, "status" => "DUE"]);
                    }
                    return ResponseMessage::success("Payment Record Updated!");
                } else {
                    return  ResponseMessage::fail("Failed  To Update Payment Record!");
                }
            }
        } else {
            return  ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Payment Record";
        $user = $request->user();
        if ($user->can($permission)) {
            if (StudentsPayment::find($id) != null) {
                if (StudentsPayment::destroy($id)) {
                    Accounts::where("entry_category", "like", "%Student Payment%")->where("payment_id", $id)->update(["entry_category" => "Student Payment[Deleted]"]);
                    return ResponseMessage::success("Payment Record Deleted!");
                }
            } else {
                return ResponseMessage::fail("Payment Record Doesn't Exist!");
            }
        } else {
            return  ResponseMessage::unauthorized($permission);
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
                array_push($due_arr, ["student_id" => $payment->student_id, "payment_id" => $payment->id, "amount" => $calc_amount, "status" => "DUE"]);
        }
        if (count($due_arr) > 0) {

            if (!StudentsPaymentAccount::insert($due_arr))
                return false;
        }

        return true;
    }

    public function paymentArrayConverter($payments, $receipt_id, $student_id, $session_id, $date)
    {
        $payment_arr = [];
        $accounts_arr = [];
        $student = ClassHasStudents::where("class_has_students.id", $student_id)->leftJoin("students", "students.id", '=', 'class_has_students.student_id')->first();

        $student_name = $student->student_name;
        foreach ($payments as $payment) {
            $payment_category = $payment["payment_category"];
            $payment_info = $payment["payment_info"];
            $payment_amount = $payment["payment_amount"];
            $paid_amount = $payment["paid_amount"];
            $payment_info = $payment_info == null ? '' : $payment_info;
            array_push($payment_arr, ["session_id" => $session_id, "student_id" => $student_id, "date" => $date, "payment_category" => $payment_category, "payment_info" => $payment_info, "payment_amount" => $payment_amount, "paid_amount" => $paid_amount, "group_id" => $receipt_id]);
            array_push($accounts_arr, ["balance_form" => "Cash", "entry_type" => "Credit", "entry_category" => "Student Payment", "entry_info" => "Receipt ID: " . $receipt_id . "\nStudent ID: " . $student->student_identifier . "\nStudent Name: " . $student_name . "\nPayment Info: " . $payment_category . " - " . $payment_info, "amount" => $paid_amount, "date" => $date]);
        }
        return [$payment_arr, $accounts_arr];
    }
}
