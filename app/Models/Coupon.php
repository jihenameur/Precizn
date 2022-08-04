<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'coupons';

    protected $fillable = [
        'code_coupon',
        'client_id',
        'type',
        'value',
        'title',
        'client_id',
        'start_date',
        'end_date',
        'description',
        'quantity',
        'client_quantity',
        'status',
        'montant_min',
        'currency',
        'taxe',
        'frais_port',
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function clients()
    {
        return $this->belongsToMany(Client::class);
    }
}
