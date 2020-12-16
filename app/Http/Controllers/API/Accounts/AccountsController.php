<?php

namespace App\Http\Controllers\API\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Accounts;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Accounts";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "from" => "required|date",
                "to" => "required|date"
            ]);
            $from = $request->from;
            $to = $request->to;
            return Accounts::whereBetween("date", [$from, $to])->get();
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
                "entry_type" => "required|numeric",
                "entry_info" => "required|string",
                "amount" => "required|numeric",
            ]);
            $Accounts = new Accounts;
            $Accounts->date = $request->date;
            $Accounts->entry_type = $request->entry_type;
            $Accounts->entry_info = $request->entry_info;
            $Accounts->amount = $request->amount;
            if ($Accounts->save()) {
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
                "entry_type" => "required|numeric",
                "entry_info" => "required|string",
                "amount" => "required|numeric",
            ]);
            $Accounts = Accounts::find($id);
            if ($Accounts != null) {
                $Accounts->date = $request->date;
                $Accounts->entry_type = $request->entry_type;
                $Accounts->entry_info = $request->entry_info;
                $Accounts->amount = $request->amount;
                if ($Accounts->save()) {
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
}
