<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';

    protected $fillable = [
        'message',
        'date',
        'send',
        'client_id '
    ];
    protected $with = [
       // 'client'

    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
