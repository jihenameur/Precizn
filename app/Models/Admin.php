<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $table = 'admins';

    protected $fillable = [
        'firstname',
        'lastname',
        'gender'

    ];
    protected $with = [
        'user'
    ];

    public $toClaim = [
        'user'
    ];



    /**
     * @return MorphOne
     */
    public function user()
    {
        return $this->morphOne('App\Models\User', 'userable')->without('userable');

    }


}
