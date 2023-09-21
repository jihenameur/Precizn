<?php

namespace App\Models;

use App\Models\task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sub_task extends Model
{
    use HasFactory;
    protected $fillable = [
        'subtask_name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'user_id',
        'claim',
        'task_id'
    ];
    public function task()
    {
        return $this->belongsTo(task::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
