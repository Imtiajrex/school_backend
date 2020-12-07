<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassHasStudents extends Model
{
    use HasFactory;
    protected $table = "class_has_students";
    public $timestamps = false;
}
