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

class DueController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Due List";
        $user = $request->user();
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_identifier)) {
            if ($request->student_identifier) {
                $student_id = ClassHasStudents::where("student_identifier", $request->student_identifier)->first();
                $student_id = $student_id != null ? $student_id->id : null;
            } else if ($request->student_id) {
                $student_id = $request->student_id;
            }
            if ($student_id)
                return StudentsPaymentAccount::where("students_payment_accounts.student_id", $student_id)->leftJoin("class_has_students", "students_payment_accounts.student_id", "=", "class_has_students.id")->leftJoin("students", "class_has_students.student_id", "=", "students.id")->leftJoin("students_payment", "students_payment_accounts.payment_id", "=", "students_payment.id")->leftJoin("session", "class_has_students.session_id", "=", "session.id")->leftJoin("class", "class.id", "=", 'class_has_students.class_id')->leftJoin("department", "department.id", "=", 'class_has_students.department_id')->get(['class.name as class', 'department.name as department', 'students_payment_accounts.amount', 'class_has_students.id as student_id', 'class_has_students.session_id', 'class_has_students.student_identifier', 'students.student_name', 'session.session', 'students_payment.payment_category', 'students_payment.payment_info', 'students_payment_accounts.id', 'students_payment_accounts.payment_id',]);
        } else {
            return ResponseMessage::unauthorized($permission);
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
        } else {
            return ResponseMessage::unauthorized($permission);
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


            $student_id = $request->student_id;


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
                DB::table("account_balance")->where("id", 1)->update(["cash" => $total_income]);
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
            return ResponseMessage::unauthorized($permission);
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

        return [$payment_arr, $due_delete_arr, $due_update_arr, $accounts_arr];
    }
}
