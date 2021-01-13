<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentsExtendedInfo extends Model
{
    use HasFactory;
    protected $table = "student_extended_info";
    public $timestamps = false;
}
