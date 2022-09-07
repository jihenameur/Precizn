<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $table = 'options';
    protected $fillable = [
        'name',
        'description',
        "price"
    ];
    protected $with = [
        //'products'
    ];
    public function products() {
        return $this->belongsToMany(
                                  'App\Models\Product',
                                  'supplier_product_option',
                                  'option_id',
                                  'product_id'
                                  );
   }
   public function supplier()
   {
       return $this->belongsTo(Supplier::class);
   }
}
