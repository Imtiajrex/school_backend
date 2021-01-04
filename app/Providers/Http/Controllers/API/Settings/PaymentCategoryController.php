<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\PaymentCategory;
use Illuminate\Http\Request;

class PaymentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View PaymentCategory";
        $user = $request->user();
        if ($user->can($permission)) {
            return PaymentCategory::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create PaymentCategory";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "category_name" => 'required|string',
                "info_type" => 'required',
                "info_options" => "required",
                "default_amount" => 'required|numeric',
                "recurring_type" => "required"
            ]);
            if (PaymentCategory::where(["category_name" => $request->category_name])->first() == null) {
                $PaymentCategory = new PaymentCategory;
                $PaymentCategory->category_name = $request->category_name;
                $PaymentCategory->info_type = $request->info_type;
                $PaymentCategory->info_options = $request->info_options;
                $PaymentCategory->default_amount = $request->default_amount;
                $PaymentCategory->recurring_type = $request->recurring_type;
                if ($PaymentCategory->save()) {
                    return ResponseMessage::success("PaymentCategory Created!");
                } else {
                    return ResponseMessage::fail("PaymentCategory Creation Failed!");
                }
            } else {
                return ResponseMessage::fail("PaymentCategory Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update PaymentCategory";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "category_name" => 'required',
                "info_type" => 'required',
                "info_options" => "required",
                "default_amount" => 'required',
                "recurring_type" => "required"
            ]);
            $PaymentCategory = PaymentCategory::find($id);
            if ($PaymentCategory != null) {

                $PaymentCategory->category_name = $request->category_name;
                $PaymentCategory->info_type = $request->info_type;
                $PaymentCategory->info_options = $request->info_options;
                $PaymentCategory->default_amount = $request->default_amount;
                $PaymentCategory->recurring_type = $request->recurring_type;
                if ($PaymentCategory->save()) {
                    return ResponseMessage::success("PaymentCategory Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update PaymentCategory!");
                }
            } else {
                return ResponseMessage::fail("PaymentCategory Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete PaymentCategory";
        $user = $request->user();
        if ($user->can($permission)) {
            if (PaymentCategory::find($id) != null) {
                if (PaymentCategory::destroy($id)) {
                    return ResponseMessage::success("PaymentCategory Deleted!");
                }
            } else {
                return ResponseMessage::fail("PaymentCategory Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
