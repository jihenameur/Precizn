<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filters extends Model
{
    use HasFactory;
    protected $table = 'filters';
    protected $fillable = [
        'client_id',
        'address_id',
        'supplier_id',
        'category_id',
        'product_id'
    ];
    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id')->where('category_id', 0)->with('category');
    }
    public function address()
    {
        return $this->belongsTo('App\Models\Address', 'address_id')->where('address_id', 0)->with('address');
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id')->where('product_id', 0)->with('product');
    }
    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier', 'supplier_id')->where('supplier_id', 0)->with('supplier');
    }
    
    
}
