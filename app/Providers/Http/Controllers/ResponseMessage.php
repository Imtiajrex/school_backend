<?php

namespace App\Http\Controllers;

class ResponseMessage
{
    public static function fail(string $message)
    {
        return response()->json(["message" => $message, "success" => false],500);
    }

    public static function success(string $message)
    {
        return response()->json(["message" => $message, "success" => true],200);
    }

    public static function unauthorized(string $permission)
    {
        return response()->json(["message" => "Unauthorized!", "error" => "User Doesn't Have Permission To " . $permission],401);
    }
}
