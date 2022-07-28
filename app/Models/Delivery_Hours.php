<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery_Hours extends Model
{
    use HasFactory;
    protected $table = 'delivery_hours';

    protected $fillable = [
        'delivery_id',
        'date',
        'start_hour',
        'end_hour',
        'hours'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
