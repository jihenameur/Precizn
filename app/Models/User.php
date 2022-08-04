<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\VerifyApiEmail;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Laravel\Passport\HasApiTokens; // include this

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'tel',
        'role',
        'userable_id',
        'userable_type',
        'status_id'

    ];
    protected $visible = [
        'id',
        'email',
        'tel',
        'created_at',
        'userable'
    ];

    protected $with = [

        //'userable',
       // 'role',
      //  'addresses',
       // 'userable'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function sendApiEmailVerificationNotification()
    {
        $this->notify(new VerifyApiEmail); // my notification
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);

    }
     /**
     * @return MorphTo
     */
    public function userable()
    {
        return $this->morphTo();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class,'addresses');
    }

    public function status()
    {
    	return $this->belongsTo(Status::class);
    }

    public function isAuthorized(array $roles){

        $default_roles = $this->roles;
        $ok = false;
        foreach ($default_roles as $default_role){
            if(in_array($default_role->name,$roles)) {
                $ok = true;
            }
        }
        return $ok;
    }

}
