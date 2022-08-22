<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Command extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'commands';

    protected $fillable = [
        'client_id',
        'delivery_id',
        'date',
        'mode_pay',
        'delivery_price',
        'total_price',
        'tip',
        'status',
        'codepromo',
        'supplier_id',
        'lat',
        'long',
        'addresse_id',
        'total_price_coupon'

    ];
    protected $with = [
        'client',
        'panier',
        'delivery',
        'products',
        'supplier',
        'address',

    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }
    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function requestDelivery()
    {
        return $this->hasMany(RequestDelivery::class);
    }
    public function admin()
    {
       return $this->belongsTo(Admin::class,'admin_id');
    }
}
