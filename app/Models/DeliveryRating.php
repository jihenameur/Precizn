<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRating extends Model
{
    use HasFactory;
    protected $table = 'delivery_rating';

    protected $fillable = [
        'client_id',
        'delivery_id',
        'comment',
        'rating'
    ];
}
