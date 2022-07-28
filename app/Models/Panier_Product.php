<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier_Product extends Model
{
    use HasFactory;
    protected $table = 'panier_product';
    protected $fillable = [
        'quantity',
        'product_price'
    ];
    public function options()
    {
        return $this->belongsToMany(Option::class);
    }
}
