<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPages extends Model
{
    use HasFactory;
    protected $casts = ['page_content' => 'json'];
    protected $table = "subpages";
    public $timestamps = false;
}
