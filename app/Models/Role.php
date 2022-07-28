<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Role extends Model
{

    use HasFactory;
    protected $table = 'roles';
    protected $fillable = [
        'name',
        'short_name',
        'description'
    ];
    protected $hidden = [
        'updated_at',
        'created_at',
    ];
    /**
     * Get related users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function admins()
    {
        return $this->belongsToMany(User::class);
    }

}
