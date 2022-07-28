<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annonces extends Model
{
    use HasFactory;
    protected $fillable = [
     
        'description',
        'supplier_id'
        
    ];
    public function fournisseur()
    {
        return $this->belongsTo(Supplier::class);

    }

}
