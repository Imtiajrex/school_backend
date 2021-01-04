<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\Products;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Products";
        if ($user->can($permission)) {
            return Products::where('deleted',0)->get();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Products";
        if ($user->can($permission)) {
            $request->validate([
                "product_name" => "required|string",
                "product_info" => "required|string",
                "price" => "required|numeric",
                "stock" => "required|numeric",
            ]);


            $products = new Products;
            $products->product_name = $request->product_name;
            $products->product_info = $request->product_info;
            $products->price = $request->price;
            $products->stock = $request->stock;
            $products->deleted = 0;
            if ($products->save()) {
                return ResponseMessage::success("Product Created Successfully!");
            } else {
                return ResponseMessage::fail("Couldn't Create Product!");
            }
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $permission = "Update Products";
        if ($user->can($permission)) {
            $request->validate([
                "product_name" => "required|string",
                "product_info" => "required|string",
                "price" => "required|numeric",
                "stock" => "required|numeric",
            ]);


            $products = Products::find($id);
            if ($products != null) {
                $products->product_name = $request->product_name;
                $products->product_info = $request->product_info;
                $products->price = $request->price;
                $products->stock = $request->stock;

                if ($products->save()) {
                    return ResponseMessage::success("Products Updated Successfully!");
                } else {
                    return ResponseMessage::fail("Couldn't Update Products!");
                }
            } else
                return ResponseMessage::fail("Products Doesn't Exist!");
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Products";
        if ($user->can($permission)) {
            $products = Products::find($id);
            if ($products != null) {
                $products->deleted = 1; 
                if ($products->save()) {
                    return ResponseMessage::success("Products Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Products!");
                }
            } else {
                return ResponseMessage::fail("Products Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
