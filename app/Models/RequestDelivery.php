<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDelivery extends Model
{
    use HasFactory;
    protected $table = 'requestDelivery';



    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
    public function command()
    {
        return $this->belongsTo(Command::class);
    }
}
