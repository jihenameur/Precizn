<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'files';
    protected $fillable = [
        'name',
        'path',
        'user_id '
    ];
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    public function supplier()
    {
        return $this->belongsToMany(Supplier::class)->withPivot('type');
    }

}
