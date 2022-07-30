<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Delivery extends Model
{
    use HasFactory,Notifiable;
    protected $table = 'deliverys';

    protected $fillable = [
        'vehicle',
        'lat',
        'long',
        'available',
        'firstName',
        'lastName',
        'street',
        'postcode',
        'city',
        'region',
        'Mark_vehicle',
        'start_worktime',
        'end_worktime',
        'image',
        'rating',
        'salary',
    ];
    protected $with = [
        'user',
        // 'commands'
    ];

    public $toClaim = [
        'user'
    ];


     /**
     * @return MorphOne
     */
    public function user()
    {
        return $this->morphOne('App\Models\User', 'userable');
    }
    public function commands()
    {
        return $this->hasMany(Command::class);

    }
    public function requestDelivery()
    {
        return $this->hasMany(RequestDelivery::class);

    }
}
