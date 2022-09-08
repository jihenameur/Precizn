<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';
    protected $fillable = [
        'name',
        'image',
        'description',
        'default_price',
        'private',
        'min_period_time',
        'max_period_time',
        'unit_type',
        'unit_limit',
        'weight',
        'dimension'
    ];
    protected $with = [
        //  'suppliers'
        'product_hours',
        'typeproduct',
        'tag',
        'discounds',
        'files',
    ];


//     public function options() {
//         return $this->belongsToMany(
//                                   Option::class,
//                                   'supplier_product_option',
//                                   'option_id',
//                                   'supplier_id');
//    }
    public function options()
    {
        return $this->belongsToMany(
            'App\Models\Option',
            'supplier_product_option',
            'product_id',
            'option_id'
        )->withPivot('price','type');
    }

    public function menu()
    {
        return $this->belongsToMany(Menu::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class)->withPivot('price');
    }

    public function panier()
    {
        return $this->belongsToMany(Panier::class)->withPivot('quantity');
    }

    public function typeproduct()
    {
        return $this->belongsToMany(TypeProduct::class);
    }

    public function tag()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class);
    }

    public function product_hours()
    {
        return $this->hasOne(Product_hours::class);
    }

    public function discounds()
    {
        return $this->hasMany(Discount::class);
    }

    public function supplier_price($supplier)
    {
        return $this->suppliers()->wherePivot('supplier_id', $supplier)->withPivot('price')->first()->pivot->price;
    }
}
