<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\ResponseMessage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request_user = $request->user();
        if ($request_user->can('View User')) {
            return User::all(['name','username','user_type']);
        } else {
            return ResponseMessage::unauthorized("Unauthorized!");
        }
    }

    public function store(Request $request)
    {
        $permission = "Create User";
        $success_msg = "User Successfully Created!";

        $request_user = $request->user();
        if ($request_user->can($permission)) {
            $request->validate([
                'name' => 'required',
                'user_type' => 'required',
                'username' => 'required',
                'password' => 'required',
                'role' => 'required'
            ]);
            $role = $request->role;

            $user = new User;

            $user->name = $request->name;
            $user->user_type = $request->user_type;
            if (User::where('username', $request->username)->first() == null)
                $user->username = $request->username;
            else
                return ResponseMessage::fail("Username Exists! Change Username!");

            $user->password = Hash::make($request->password);


            if ($user->save()) {
                if (Role::where('name', $role)->first() != null) {
                    if ($user->assignRole($role)) {
                        return ResponseMessage::success($success_msg);
                    } else {
                        User::where("username", $request->username)->delete();
                        return ResponseMessage::fail("Role Assigning Failed!");
                    }
                } else {
                    User::where("username", $request->username)->delete();
                    return ResponseMessage::fail("Assigned Role Doesn't Exist!");
                }
            } else {
                return ResponseMessage::fail("User Creation Failed!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $permission = "Delete User";
        $success_msg = "User Successfully Deleted!";

        $request_user = $request->user();
        if ($request_user->can($permission)) {
            if (User::find($id) != null) {
                if (User::destroy($id)) {
                    return ResponseMessage::success($success_msg);
                }
            } else {
                return ResponseMessage::fail("User Doesn't Exist!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }



    public function update($id, Request $request)
    {
        $permission = "Update User";

        $request_user = $request->user();
        if ($request_user->can($permission)) {
            $request->validate([
                'name' => 'required',
                'user_type' => 'required',
                'username' => 'required',
                'password' => 'required',
                'role' => 'required'
            ]);
            $user = User::find($id);
            if ($user != null) {
                $user->name = $request->name;
                $user->user_type = $request->user_type;
                $user->password = Hash::make($request->password);

                if ($user->save()) {
                    $this->removeAllRoles($user);
                    $role = $request->role;
                    if ($user->assignRole($role)) {
                        return ResponseMessage::success("User Successfully Updated!");
                    } else {
                        return ResponseMessage::fail("Failed To Update User Role!");
                    }
                } else {
                    return ResponseMessage::fail("Failed To Update User!");
                }
            } else {
                return ResponseMessage::fail("User Doesn't Exist!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'err' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user_type = $user->user_type;
        $user_role = $user->getRoleNames();
        $token = $user->createToken($request->device_name)->plainTextToken;
        $user_permissions = $user->getAllPermissions();
        return response()->json(["token" => $token, "user_type" => $user_type, "role" => $user_role, "permissions" => $user_permissions]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        return $user->tokens()->delete();
    }

    public function removeAllRoles(User $user)
    {
        $roles = $user->getRoleNames();
        foreach ($roles as $role) {
            $user->removeRole($role);
        }
    }
}
