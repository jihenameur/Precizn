<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Supplier extends Model
{
    use HasFactory,Notifiable,SoftDeletes;
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'image',
        'address',
        'starttime',
        'closetime',
        'delivery',
        'take_away',
        'on_site',
        'star',
        'qantityVente',
        'street',
        'postcode',
        'city',
        'region',
        'lat',
        'long',
        'tel',
        'firstName',
        'lastName'

    ];
    protected $with = [
        'user',
        'products',
        'categorys',
        'images'

    ];

    public $toClaim = [
        'user'
    ];


    /**
     * @return MorphOne
     */
    public function user(){
        return $this->morphOne('App\Models\User', 'userable')
        ->without('userable');
    }
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('price');

    }
    public function favorit()
    {
        return $this->belongsToMany(Client::class, 'favorites');
    }
    public function categorys()
    {
        return $this->belongsToMany(Category::class);
    }
    public function writing()
    {
        return $this->hasMany(Writing::class,'writing');
    }
    public function commands()
    {
        return $this->hasMany(Command::class);
    }
    public function annonces()
    {
        return $this->hasMany(Annonces::class,'annonces');
    }
    public function images()
    {
        return $this->belongsToMany(File::class)->withPivot('type');;
    }
}
