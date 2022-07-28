<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_hours extends Model
{
    use HasFactory;
    protected $table = 'product_hours';
    protected $fillable = [
        'start_hour',
        'end_hour'
    ];


}
