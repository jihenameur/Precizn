<?php

namespace App\Models;

use App\Models\Project;
use App\Models\sub_task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\TaskDeadlineNotification;

class task extends Model
{
    use HasFactory;
     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $with = [
        //  'suppliers'
        'project',
    
    ];
    protected $fillable = [
        'task_name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'project_id',
        'remark'
    ];
    public function sub_tasks()
    {
        return $this->hasMany(sub_task::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function sendDeadlineNotification()
{
    $this->notify(new TaskDeadlineNotification($this));
}
}
