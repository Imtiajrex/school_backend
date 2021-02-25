<?php

namespace App\Http\Controllers\API\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\PaymentCategory;
use App\Models\StudentsPaymentInfo;
use Illuminate\Http\Request;

class StudentPaymentInfoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Student Payment Info";
        if ($user->can($permission)  || ($user->user_type == "student" && $user->username == $request->student_id)) {
            $std_pay_info =  StudentsPaymentInfo::where("students_payment_info.student_id", $request->student_id)->leftJoin("payment_category", "payment_category.id", '=', 'student_payment_category');
            $std_pay_info = $std_pay_info->rightJoin("class_has_students","class_has_students.id",'=','students_payment_info.student_id');
            $std_pay_info = $std_pay_info->leftJoin("students","students.id",'=','class_has_students.student_id')->orderBy("role",'desc');

            return $std_pay_info->get(["students.student_name","class_has_students.student_identifier","category_name as payment_category","students_payment_info.id","student_default_fees"]);
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Assign Student Payment Info";
        if ($user->can($permission)) {
            $request->validate([
                "student_id" => "required|numeric",
                "payment_category_id" => "required|numeric",
                "student_default_fees" => "required|numeric"
            ]);


            if (PaymentCategory::find($request->payment_category_id) == null)
                return ResponseMessage::fail("Payment Category Doesn't Exist!");

            $_payment_set = StudentsPaymentInfo::where(["student_id" => $request->student_id, "student_payment_category" => $request->payment_category_id])->first();
            if ($_payment_set != null) {
                $payment_set = $_payment_set;
            } else if ($request->id != null) {
                $payment_set = StudentsPaymentInfo::find($request->id);
            } else {
                $payment_set = new StudentsPaymentInfo;
            }

            $payment_set->student_id = $request->student_id;
            $payment_set->student_payment_category = $request->payment_category_id;
            $payment_set->student_default_fees = $request->student_default_fees;
            if ($payment_set->save()) {
                return ResponseMessage::success("Payment Info Assigned!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }


    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Student Payment Info";
        if ($user->can($permission)) {
            if ($id != null) {
                $payment_set = StudentsPaymentInfo::find($id);
                if ($payment_set != null) {
                    if ($payment_set->destroy($id))
                        return ResponseMessage::success("Payment Info Deleted!");
                }
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
