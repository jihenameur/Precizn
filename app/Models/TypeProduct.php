<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeProduct extends Model
{
    use HasFactory;
    protected $table = 'typeproduct';
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'order_id'
    ];
    protected $with = [
       // 'suppliers'
    ];
    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id')->where('parent_id', 0)->with('parent');
    }
    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id')->with('children');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
