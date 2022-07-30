<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MessageNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $users = User::all();
        /*whereHas('roles', function ($query) {
            $query->where('id', 1);
        })->get();*/

    Notification::send($event->user->id, new MessageNotification($event->message,$event->user));
    }
}
