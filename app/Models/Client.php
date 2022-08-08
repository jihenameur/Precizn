<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use NunoMaduro\Collision\Writer;

class Client extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'clients';

    protected $fillable = [
        'address',
        'firstname',
        'lastname',
        'gender',
        'image',
        'verified'
        //'street',
        // 'postcode',
        // 'city',
        // 'region',
        // 'lat',
        // 'long'

    ];
    protected $with = [
        'user',
        'favorit',
        //   'commands'
        'file'
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

    public function commands()
    {
        return $this->hasMany(Command::class);
    }
    public function favorit()
    {
        return $this->belongsToMany(Supplier::class, 'favorites');
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getWallet()
    {
        return $this->balance;
    }

    public function incrementDecrementBalance($value, $incrm = true) // incr -> false : for decrement
    {
        if ($incrm) {
            $this->balance += $value;
        } else {
            $this->balance - $value < 0 ? $this->balance = 0 : $this->balance -= $value;
        }

        $this->save();
        return $this->balance;
    }
    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
