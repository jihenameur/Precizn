<?php

use App\Http\Resources\DeliverySocketResource;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('.chat.{client_id}', function ($client_id) {
    if(Auth::check()){
        return $client_id;
    }
});

Broadcast::channel('.positionDelivery', function ($admin_id) {
    if(Auth::user()->userable_type=="App\Models\Admin"){ // add verify admin role
        return $admin_id;
    }
});

Broadcast::channel('online', function ($user) {
    if (auth()->check()) {

            return new DeliverySocketResource($user->userable);

    }
});

Broadcast::channel('.verify.command.{admin_id}', function ($admin_id) {
    if(Auth::check()){
        return $admin_id;
    }
});
Broadcast::channel('.supplier.command.{supplier_id}', function ($supplier_id) {
    if(Auth::check()){
        return $supplier_id;
    }
});
Broadcast::channel('.client.command.{client_id}', function ($client_id) {
    if(Auth::check()){
        return $client_id;
    }
});
