<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'menu';
    protected $fillable = [
        'name',
        'description',
        "image"
    ];
    protected $with = [
        //'products'
    ];
    public function products() {
        return $this->belongsToMany(
                                  'App\Models\Product',
                                  'supplier_menu_product',
                                  'menu_id',
                                  'product_id'
                                  );
   }

}
