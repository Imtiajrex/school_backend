<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSAccount extends Model
{
    use HasFactory;
    protected $table = "sms_account";
    public $timestamps = false;
}
