<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachersExtendedInfo extends Model
{
    use HasFactory;
    protected $table = "teacher_extended_info";
    public $timestamps = false;
}
