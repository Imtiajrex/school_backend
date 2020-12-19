<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsSold extends Model
{
    use HasFactory;
    protected $table = "products_sold";
    public $timestamps = false;
}
