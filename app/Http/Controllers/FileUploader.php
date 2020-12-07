<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class FileUploader extends Controller
{
    public static function upload($image, $resize_width)
    {
        $highest_file_size = 5;
        $supported_file_types = ["image/jpeg", "image/png"];
        if ($image != null) {
            $file = $image;
            $filesize = $file->getSize() / 1000000;
            if ($filesize <= $highest_file_size) {
                if (in_array($file->getMimeType(), $supported_file_types)) {
                    $file_type = $file->extension();
                    if ($resize_width != null) {
                        $file = Image::make($file)->resize($resize_width, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->encode('jpg', 85);

                        $file_type = 'jpg';
                        $now = Carbon::now()->toDateTimeString();
                        $hash = md5($file->__toString() . $now);

                        $file_name = $hash . '.' . $file_type;

                        $file = Storage::disk('public')->put("images/" . $file_name, $file);
                    } else {
                        $file = $file->store('public/images/');
                        $file_name = str_replace("public/images//", '', $file);
                    }
                    return ["image_name" => $file_name];
                } else {
                    return ["error" => "File Type Not Supported!"];
                }
            } else
                return ["error" => "Highest File Size 5MB !"];
        } else
            return ["error" => "No File Sent!"];
    }
}
