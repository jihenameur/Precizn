<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Writing extends Model
{
    use HasFactory;
  

    protected $fillable = [
        'note',
        'comment',
        'client_id',
        'supplier_id',
  
    ];

    protected $with = [
        'client',
        'supplier',
   

    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
