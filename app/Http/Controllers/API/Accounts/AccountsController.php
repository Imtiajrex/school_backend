<?php

namespace App\Http\Controllers\API\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Accounts";
        $user = $request->user();
        if ($user->can($permission)) {
            if ($request->from != null && $request->to != null) {
                $from = $request->from;
                $to = $request->to;
                $acc = Accounts::whereBetween("date", [$from, $to])->orderBy("date", "desc")->orderBy("entry_type", "asc")->get();
            } else {
                $acc = Accounts::orderBy("id", "desc")->limit(15)->get();
            }
            return $acc;
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Accounts";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "date" => "required|date",
                "balance_form" => "required|string",
                "entry_type" => "required|string",
                "amount" => "required|numeric",
            ]);
            $Accounts = new Accounts;
            $Accounts->date = $request->date;
            $Accounts->balance_form = $request->balance_form;
            $Accounts->entry_type = $request->entry_type;
            if ($request->entry_info)
                $Accounts->entry_info = $request->entry_info;
            $Accounts->amount = $request->amount;

            $account_balance = DB::table("account_balance")->where("id", 1)->get();
            $balance = $request->balance_form == "Bank" ? $account_balance[0]->bank : $account_balance[0]->cash;

            $new_balance = $request->entry_type == "Credit" ? $balance + $request->amount : $balance - $request->amount;

            $update_query = [];
            $update_query[strtolower($request->balance_form)] = $new_balance;
            if ($Accounts->save()) {
                DB::table("account_balance")->where("id", 1)->update($update_query);

                return ResponseMessage::success("Inserted A Accounts Record!");
            } else {
                return ResponseMessage::fail("Accounts Record Insertion Failed!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Accounts";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "date" => "required|date",
                "balance_form" => "required|string",
                "entry_type" => "required|string",
                "amount" => "required|numeric",
            ]);
            $Accounts = Accounts::find($id);
            if ($Accounts != null) {
                $account_balance = DB::table("account_balance")->where("id", 1)->get();
                $balance = $request->balance_form == "Bank" ? $account_balance[0]->bank : $account_balance[0]->cash;
                $changed_balance = $Accounts->entry_type == "Credit" ? $balance - $Accounts->amount : $balance + $Accounts->amount;

                $Accounts->date = $request->date;
                $Accounts->balance_form = $request->balance_form;
                $Accounts->entry_type = $request->entry_type;
                if ($request->entry_info)
                    $Accounts->entry_info = $request->entry_info;
                $Accounts->amount = $request->amount;

                $new_balance = $request->entry_type == "Credit" ? $changed_balance + $request->amount : $changed_balance - $request->amount;

                $update_query = [];
                $update_query[strtolower($request->balance_form)] = $new_balance;

                if ($Accounts->save()) {
                    DB::table("account_balance")->where("id", 1)->update($update_query);
                    return ResponseMessage::success("Accounts Record Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Accounts Record!");
                }
            } else {
                return ResponseMessage::fail("Accounts Record Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Accounts Record";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Accounts::find($id) != null) {
                if (Accounts::destroy($id)) {
                    return ResponseMessage::success("Accounts Record Deleted!");
                }
            } else {
                return ResponseMessage::fail("Accounts Record Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function getAccountBalance(Request $request)
    {

        $permission = "View Account Balance";
        $user = $request->user();
        if ($user->can($permission)) {
            $acc = DB::table("account_balance")->get();
            return $acc;
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function editAccountBalance($id, Request $request)
    {

        $permission = "Edit Account Balance";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "cash" => "required|numeric",
                "bank" => "required|numeric",
            ]);
            $acc = DB::table("account_balance")->find($id);

            if ($acc != null) {
                if (DB::table("account_balance")->where("id", $id)->update(["cash" => $request->cash, "bank" => $request->bank])) {
                    return ResponseMessage::success("Accounts Balance Record Updated!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Accounts Balance Record!");
                }
            } else {
                return ResponseMessage::fail("Accounts Balance Record Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
