<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory,SoftDeletes;

    public function products() {
        return $this->belongsToMany(Product::class)->withPivot('position');
   }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function image()
    {
       return $this->belongsTo(File::class,'file_id','id');
    }
}
