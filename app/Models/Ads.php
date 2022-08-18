<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ads extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ads';
    protected $fillable = [
        'adsarea_id',
        'file_id',
        'supplier_id',
        'product_id',
        'menu_id',
        'start_date',
        'end_date',
        'price'
    ];
    protected $with = [
        'product',
        'menu'
    ];
    public function adsarea()
    {
        return $this->belongsTo(Adsarea::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

}
