<?php


namespace App\Http\Controllers\Auth;


use App\BaseModel\Result;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    public function signInWithSocial(Request $request)
    {
        $this->validate($request,[
           'provider' => 'required',
           'token' => 'required',
        ]);

        $res = new Result();

        try{
           $user_provider = Socialite::driver($request->provider)->stateless()->userFromToken($request->token);
           $user = User::where('social','like','%'.$user_provider->id.'%')->orWhere('social','like','%'.$user_provider->token.'%')->first();
            $new_user = new User();
            $client = new Client();
           if($user)
           {
               // login
               $token = JWTAuth::fromUser($user);
               $client = Client::find($user->userable_id);
               $address = Address::where('user_id', $user->id)
                   ->where('status', 1)->first();
               // $user->refresh_token = $refresh_token;
               // $user->update();
               $role = Role::whereHas('admins', function ($q) use ($user) {
                   $q->where('user_id', $user->id);
               })->first();
               $clt = [
                   'id' => $client['id'],
                   'firstname' => $client['firstname'],
                   'lastname' => $client['lastname'],
                   'image' => $client['image'],
                   'email' => $user['email'],
                   'gender' => $client['gender'],
                   'tel' => $user['tel'],
                   'status' => $user['status_id'],
                   'role' => $role['id'],
                   'street' => isset($address) ? $address['street'] : '',
                   'postcode' => isset($address) ? $address['postcode'] : '',
                   'city' => isset($address) ? $address['city'] : '',
                   'region' => isset($address) ? $address['region'] : '',
               ];

                   $customClaims = ['name' => $user->name]; // Here you can pass user data on claims
                   $response = [
                       'token' => $token,
                       'client' => $clt
                   ];
                   $res->success($response);
                   return new JsonResponse($res, $res->code);

           }else{
               // create a new user

               $new_user->email = $user_provider->email ? $user_provider->email : ($user_provider->id ? $user_provider->id : 'unset');
               $new_user->social = json_encode([
                   $request->provider => [
                       'id' => $user_provider->id ?? null,
                       'token' => $user_provider->token
                   ]
               ]);
               $role_id = Role::where('short_name', config('roles.backadmin.client'))->first()->id;
               $new_user->save();
               $new_user->refresh();


               $client->firstname = $user_provider->name ? $user_provider->name : ($user_provider->id ? $user_provider->id : 'unset');
               $client->lastname = $user_provider->name ? $user_provider->name : ($user_provider->id ? $user_provider->id : 'unset');
               $client->save();
               $client->refresh();
               $client->user()->save($new_user);

               $role = Role::find($role_id);
               $new_user->roles()->attach($role);

               $token = JWTAuth::fromUser($new_user);
               $new_user->token = $token;
               $new_user->status_id = 4;
               $new_user->update();

               $clt = [
                   'id' => $client['id'],
                   'firstname' => $client['firstname'],
                   'lastname' => $client['lastname'],
                   'image' => $client['image'],
                   'email' => $new_user['email'],
                   'gender' => $client['gender'],
                   'tel' => $new_user['tel'],
                   'status' => $new_user['status_id'],
                   'role' => $role['id'],
                   'street' => '',
                   'postcode' =>  '',
                   'city' => '',
                   'region' => '',
               ];

               $customClaims = ['name' => $new_user->name]; // Here you can pass user data on claims
               $response = [
                   'token' => $token,
                   'client' => $clt
               ];
               $res->success($response);
               return new JsonResponse($res, $res->code);

           }

        }catch (\Exception $exception) {
            $res->fail('erreur serveur 500');
            return new JsonResponse($res, $res->code);
        }
    }

}
