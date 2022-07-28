<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $table = 'addresses';
    protected $fillable = [
        'street',
        'user_id',
        'postcode',
        'city',
        'region',
        'lat',
        'long',
        'status',
        'label',
        'type'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function commands()
    {
        return $this->belongsToMany(Command::class, 'commands');
    }
}
