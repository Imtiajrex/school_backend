<?php

namespace App\Http\Controllers\API\Payments;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Accounts;
use App\Models\Students;
use App\Models\StudentsPayment;
use App\Models\StudentsPaymentAccount;
use App\Models\StudentsPaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DueController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Due List";
        $user = $request->user();
        if ($user->can($permission) || ($user->user_type =="student"&& $user->username==$request->student_id)) {
            $request->validate([
                "student_id" => "required",
            ]);
            $student_id = Students::where("student_id", $request->student_id)->first();
            if ($student_id != null) $student_id = $student_id->id;
            else return [];
            return StudentsPaymentAccount::where("students_payment_accounts.student_id", $student_id)->leftJoin("students", "students_payment_accounts.student_id", "=", "students.id")->leftJoin("students_payment", "students_payment_accounts.payment_id", "=", "students_payment.id")->leftJoin("class_has_students", "students.id", "=", "class_has_students.student_id")->leftJoin("session", "class_has_students.session_id", "=", "session.id")->get(['students_payment_accounts.*', 'students_payment_accounts.amount as fees', 'students.student_id as student_identifier', 'students.student_name', 'class_has_students.session_id', 'session.session', 'students_payment.payment_category', 'students_payment.payment_info']);
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request  $request)
    {

        $user = $request->user();
        $permission = "Update Due Record";
        if ($user->can($permission)) {
            $request->validate([
                "amount" => "required|numeric",
            ]);
            $std_account = StudentsPaymentAccount::find($id);
            if ($std_account != null) {
                $std_account->amount = $request->amount;
                if ($std_account->save()) {
                    return ResponseMessage::success('Due Updated!');
                } else {
                    return ResponseMessage::fail('Failed To Update Due!');
                }
            }
        }
    }
    public function destroy($id, Request $request)
    {
        $permission = "Delete Due Record";
        $user = $request->user();
        if ($user->can($permission)) {
            if (StudentsPaymentAccount::find($id) != null) {
                if (StudentsPaymentAccount::destroy($id)) {
                    return ResponseMessage::success("Due Record Deleted!");
                }
            } else {
                return ResponseMessage::fail("Due Record Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function pay_due(Request $request)
    {
        $permission = "Pay Due Record";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "date" => "required|date",
                "student_id" => "required|numeric",
                "session_id" => "required|numeric",
                "payments" => "required"
            ]);
            $date = $request->date;
            $student_id = $request->student_id;
            $payments = $request->payments;
            $session_id = $request->session_id;



            if (Students::find($student_id) == null)
                return ResponseMessage::fail("Student Not Found!");

            $receipt_id = StudentsPaymentReceipt::count() + 1;

            $query_datas = $this->paymentArrayConverter($payments, $receipt_id, $student_id, $session_id, $date);
            $payment_data = $query_datas[0];
            $due_delete_data = $query_datas[1];
            $due_update_data = $query_datas[2];
            $accounts_arr = $query_datas[3];


            if (StudentsPayment::insert($payment_data)) {
                $student_payments = StudentsPayment::where("group_id", $receipt_id);
                $payment_ids_query = $student_payments->get();
                $receipt = [];
                $index = 0;
                $total_income = 0;
                foreach ($payment_ids_query as $payment_id) {
                    $accounts_arr[$index]['payment_id'] = $payment_id['id'];

                    $total_income += $accounts_arr[$index]["amount"];
                    array_push($receipt, ["id" => $receipt_id, 'student_id' => $student_id, 'payment_id' => $payment_id['id'], "date" => $date]);
                }

                Accounts::insert($accounts_arr);
                $account_balance = DB::table("account_balance")->where("id", 1)->first();
                $total_income += $account_balance->cash;
                DB::table("account_balance")->where("id", 1)->update(["cash"=>$total_income]);
                if (count($due_delete_data) > 0)
                    StudentsPaymentAccount::destroy($due_delete_data);
                if (count($due_update_data) > 0)
                    StudentsPaymentAccount::upsert($due_update_data, ["id", "student_id", "payment_id"], ["amount"]);
                if (StudentsPaymentReceipt::insert($receipt)) {

                    return response()->json(["message" => "Inserted A Due Payment Record!", "receipt_id" => $receipt_id]);
                } else {
                    return ResponseMessage::success("Failed To Create Receipt!");
                }
            } else {
                return ResponseMessage::fail("Due Payment Record Insertion Failed!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function paymentArrayConverter($payments, $receipt_id, $student_id, $session_id, $date)
    {
        $payment_arr = [];
        $due_update_arr = [];
        $due_delete_arr = [];
        $accounts_arr = [];
        $student = Students::find($student_id);
        $student_name = $student->student_name;
        foreach ($payments as $payment) {
            $payment_category = $payment["payment_category"];
            $payment_info = $payment["payment_info"];
            $payment_amount = $payment["payment_amount"];
            $paid_amount = $payment["paid_amount"];
            $payment_info = $payment_info == null ? '' : $payment_info;
            array_push($payment_arr, ["session_id" => $session_id, "student_id" => $student_id, "date" => $date, "payment_category" => $payment_category, "payment_info" => $payment_info, "payment_amount" => $payment_amount, "paid_amount" => $paid_amount, "group_id" => $receipt_id]);

            $due_id = $payment["id"];
            $payment_id = $payment["payment_id"];
            $due_record = StudentsPaymentAccount::find($due_id);
            if ($due_record != null) {
                if ($paid_amount < $due_record->amount) {
                    $amnt = $due_record->amount - $paid_amount;
                    array_push($due_update_arr, ["id" => $due_id, "payment_id" => $payment_id, "student_id" => $student_id, "amount" => $amnt]);
                } else {
                    array_push($due_delete_arr, $due_id);
                }
            }
            array_push($accounts_arr, ["balance_form" => "Cash", "entry_type" => "Credit", "entry_category" => "Student Payment", "entry_info" => "Receipt ID: " . $receipt_id . "\nStudent ID: " . $student_id . "\nStudent Name: " . $student_name . "\nPayment Info: " . $payment_category . " - " . $payment_info, "amount" => $paid_amount, "date" => $date]);
        }

        return [$payment_arr, $due_delete_arr, $due_update_arr,$accounts_arr];
    }
}
