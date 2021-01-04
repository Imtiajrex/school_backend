<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $permission = "View Role";
        $user = $request->user();
        if ($user->can($permission)) {
            return Role::all();
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $permission = "Create Role";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "name" => "required|string",
                "permissions" => "required|json"
            ]);
            if (Role::where("name", $request->name)->first() == null) {
                $role = new Role;
                $role->name = $request->name;
                $role->guard_name = 'web';
                if ($role->save()) {
                    if ($this->assignPermissionToRole($request->name, $request->permissions)) {
                        return ResponseMessage::success("Role Created!");
                    } else {
                        $id = $role->id;
                        DB::table('role_has_permissions')->where("role_id", $id)->delete();
                        Role::destroy($id);
                        return ResponseMessage::fail("Permission Not Set Correctly!");
                    }
                } else {
                    return ResponseMessage::fail("Couldn't Create Role!");
                }
            } else {
                return ResponseMessage::fail("Role Exists!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function update($id, Request $request)
    {
        $permission = "Update Role";
        $user = $request->user();
        if ($user->can($permission)) {
            $request->validate([
                "name" => "required|string",
                "permissions" => "required|json"
            ]);
            $role = Role::find($id);
            if ($role != null) {
                $role->name = $request->name;
                if ($role->save()) {
                    if ($this->assignPermissionToRole($request->name, $request->permissions)) {

                        return ResponseMessage::success("Role Updated!");
                    } else {
                        return ResponseMessage::fail("Role Updated! Permission Not Set Correctly!");
                    }
                } else {
                    return ResponseMessage::fail("Couldn't Update Role!");
                }
            } else {
                return ResponseMessage::fail("Role Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete Role";
        $user = $request->user();
        if ($user->can($permission)) {
            if (Role::find($id) != null) {
                if (Role::destroy($id)) {
                    return ResponseMessage::success("Role Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Role!");
                }
            } else {
                return ResponseMessage::fail("Role Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }

    public function assignPermissionToRole($role_name, $permissions)
    {
        $role = Role::where("name", $role_name)->first();
        $id = $role->id;
        DB::table('role_has_permissions')->where("role_id", $id)->delete();
        $permissions = json_decode($permissions);
        $success_counter = 0;
        $counter = 0;
        foreach ($permissions as $permission) {
            $counter++;
            if (Permission::where("name", $permission->name)->first() != null) {
                if ($role->givePermissionTo($permission->name)) {
                    $success_counter++;
                }
            } else {
                break;
            }
        }

        if ($success_counter == $counter)
            return true;
        else
            return false;
    }
}
