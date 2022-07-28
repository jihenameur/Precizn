<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categorys';
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
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }
}
