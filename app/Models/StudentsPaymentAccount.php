<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentsPaymentAccount extends Model
{
    use HasFactory;
    protected $table= "students_payment_accounts";
    public $timestamps = false;

}
