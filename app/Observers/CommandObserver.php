<?php

namespace App\Observers;

use App\Jobs\Admin\SendCommandAdminNotification;
use App\Models\Command;

class CommandObserver
{
    /**
     * Handle the Command "created" event.
     *
     * @param  \App\Models\Command  $command
     * @return void
     */
    public function created(Command $command)
    {
        dispatch(new SendCommandAdminNotification($command,$command->client_id));
    }

    /**
     * Handle the Command "updated" event.
     *
     * @param  \App\Models\Command  $command
     * @return void
     */
    public function updated(Command $command)
    {
        //
    }

    /**
     * Handle the Command "deleted" event.
     *
     * @param  \App\Models\Command  $command
     * @return void
     */
    public function deleted(Command $command)
    {
        //
    }

    /**
     * Handle the Command "restored" event.
     *
     * @param  \App\Models\Command  $command
     * @return void
     */
    public function restored(Command $command)
    {
        //
    }

    /**
     * Handle the Command "force deleted" event.
     *
     * @param  \App\Models\Command  $command
     * @return void
     */
    public function forceDeleted(Command $command)
    {
        //
    }
}
