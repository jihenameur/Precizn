<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $table = 'discounts';

    protected $fillable = [
        'percentage',
        'start_sate',
        'end_date'

    ];
    protected $with = [
        'products'
    ];
    public function products()
    {
        return $this->belongsTo(Product::class);
    }

}
