<?php

namespace App\Http\Controllers\API\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\PaymentCategory;
use App\Models\Students;
use App\Models\StudentsPaymentInfo;
use Illuminate\Http\Request;

class StudentPaymentInfoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Student Payment Info";
        if ($user->can($permission)) {
            $name = "";
            if ($request->std_id) {
                $student = Students::where("student_id", $request->std_id)->first();
                if ($student != null) {
                    $student_id = $student->id;
                    $name = $student->student_name;
                } else
                    return [];
            } else if ($request->student_id) {
                $student_id = $request->student_id;
            }


            $std_pay_info =  StudentsPaymentInfo::where("student_id", $student_id)->get();
            foreach ($std_pay_info as $spi) {
                $spi["std_id"] = $request->std_id;
                $spi["student_name"] = $name;
                $payment_category = PaymentCategory::find($spi->student_payment_category);
                $spi["payment_category"] = $payment_category->category_name;
            }
            return $std_pay_info;
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
                "student_id" => "required",
                "payment_category_id" => "required|numeric",
                "student_default_fees" => "required|numeric"
            ]);


            $student = Students::where("student_id", $request->student_id)->first();
            if ($student == null)
                return ResponseMessage::fail("Student Doesn't Exist!");
            $student_id = $student->id;

            if (PaymentCategory::find($request->payment_category_id) == null)
                return ResponseMessage::fail("Payment Category Doesn't Exist!");

            $_payment_set = StudentsPaymentInfo::where(["student_id" => $student_id, "student_payment_category" => $request->payment_category_id])->first();
            if ($_payment_set != null) {
                $payment_set = $_payment_set;
            } else if ($request->id != null) {
                $payment_set = StudentsPaymentInfo::find($request->id);
            } else {
                $payment_set = new StudentsPaymentInfo;
            }

            $payment_set->student_id = $student_id;
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
