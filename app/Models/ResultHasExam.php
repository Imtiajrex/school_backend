<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultHasExam extends Model
{
    use HasFactory;
    protected $table = "result_has_exam";
    public $timestamps = false;
}
