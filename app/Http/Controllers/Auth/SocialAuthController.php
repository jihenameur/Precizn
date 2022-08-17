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
use Google_Client;

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
            $client = new Google_Client(['client_id' => '301650164530-et91fi88dd6inchum8fnikq35ndq2qbu.apps.googleusercontent.com']);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($request->token);
            if ($payload) {
                $userid = $payload['sub'];

                $user = User::where('social','like','%'.$payload['sub'].'%')->orWhere('social','like','%'.$payload['sub'].'%')->first();
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
                        'user' => $clt
                    ];
                    $res->success($response);
                    return new JsonResponse($res, $res->code);

                }else{
                    // create a new user

                    $new_user->email = $payload['email'] ? $payload['email'] : ($payload['sub'] ? $payload['id'] : 'unset');
                    $new_user->social = json_encode([
                        $request->provider => [
                            'id' => $payload['sub'] ?? null,
                            'token' => $request->token
                        ]
                    ]);
                    $role_id = Role::where('short_name', config('roles.backadmin.client'))->first()->id;
                    $new_user->save();
                    $new_user->refresh();


                    $client->firstname = $payload['name'] ? $payload['name'] : ($payload['sub'] ? $payload['sub'] : 'unset');
                    $client->lastname = $payload['name'] ? $payload['name'] : ($payload['sub'] ? $payload['sub'] : 'unset');
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
                        'user' => $clt
                    ];
                    $res->success($response);
                    return new JsonResponse($res, $res->code);

                }
            } else {
                $res->fail(json_encode($payload));
                return new JsonResponse($res, $res->code);
            }
            $res->fail(json_encode($payload));
            return new JsonResponse($res, $res->code);
        }catch (\Exception $exception) {
            $res->fail(Socialite::driver($request->provider)->stateless()->user());
            return new JsonResponse($res, $res->code);
        }
    }

}
