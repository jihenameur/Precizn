<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes(['verify' => true]);

Route::get('/', function () {
    //event(new \App\Events\DeliveryPosition(5));
    $admin = \App\Models\Admin::first();
    $delivery = \App\Models\Delivery::first();
    $command = \App\Models\Command::first();
    $client = \App\Models\Client::first();
    dispatch(new \App\Jobs\Admin\ChangeDeliveryStatusJob($admin, $delivery, 'dispo'));
    dispatch(new \App\Jobs\Admin\SendCommandAdminNotification($command,$client));
    return view('welcome');
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();

});

Route::get('/auth/callback', function () {
    $user =  Socialite::driver('google')->stateless()->user();

    dd($user);
});
