<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use HasFactory, Notifiable,SoftDeletes;
    protected $table = 'employees';

    protected $fillable = [
     'post'


    ];
    protected $with = [
        'user',
        'image'
    ];

    public $toClaim = [
        'user'
    ];


    /**
     * @return MorphOne
     */
    public function user()
    {
        return $this->morphOne('App\Models\User', 'userable')->without('userable');;
    }


    public function image()
    {
       return $this->belongsTo(File::class,'file_id');
    }
}
