<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Permission";
        $user = $request->user();
        if ($user->can($permission)) {
            return Permission::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
