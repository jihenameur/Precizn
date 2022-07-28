<?php

namespace App\Http\Controllers\Auth;

use App\BaseModel\Result;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\Role;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    use VerifiesEmails;
    public $successStatus = 200;
    function loginAdmin(Request $request)
    {
        $res = new Result();

        $credentials = $request->only('email', 'password');
        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        // if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        //     $user = Auth::user();
        //     if ($user->email_verified_at == NULL) {
        //         return response()->json(['error' => 'Please Verify Email'], 401);
        //     }
        // }
        //Request is validated
        //Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }
        //Token created, return with success response and jwt token

        $user = User::find(auth()->user()->id);

        $admin = Admin::where('id', auth()->user()->userable_id)->first();
        // $user->refresh_token = $refresh_token;
        // $user->update();
        $role = Role::whereHas('admins', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();
        if (auth()->user()->status_id == 1) {

        $admn = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $role['id'],
            'firstname' => $admin['firstname'],
            'lastname' => $admin['lastname'],
            'gender' => $admin['gender']

        ];
        $response = [
            'token' => $token,
            // 'refresh_token' => $refresh_token,
            // 'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60,
            'admin' => $admn
        ];
        $res->success($response);
        return new JsonResponse($res, $res->code);
    }else{
        $admn = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $role['id'],
            'firstname' => $admin['firstname'],
            'lastname' => $admin['lastname'],
            'gender' => $admin['gender']

        ];
        $response = [
            //'token' => $token,
            // 'refresh_token' => $refresh_token,
            // 'token_type' => 'bearer',
            // 'expires_in' => auth()->factory()->getTTL() * 60,
            'admin' => $admn
        ];
        $res->success($response);
        return new JsonResponse($res, $res->code);
    }
    }
    function loginClient(Request $request)
    {
        $res = new Result();

        $credentials = $request->only('email', 'password');
        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        // if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        //     $user = Auth::user();
        //     if ($user->email_verified_at == NULL) {
        //         return response()->json(['error' => 'Please Verify Email'], 401);
        //     }
        // }
        //Request is validated
        //Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }
        //Token created, return with success response and jwt token
        // dd($token);
        // $refresh_token = JWTAuth::refresh($token);
        $client = Client::find(auth()->user()->userable_id);
        $address = Address::where('user_id', auth()->user()->id)
            ->where('status', 1)->first();
        $user = User::find(auth()->user()->id);
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
        if (auth()->user()->status_id == 1) {
            $customClaims = ['name' => $user->name]; // Here you can pass user data on claims
            $tokens = JWTAuth::fromUser($user, $customClaims);

            $response = [
                'token' => $token,
                // 'refresh_token' => $refresh_token,
                // 'token_type' => 'bearer',
                // 'expires_in' => auth()->factory()->getTTL() * 60,
                'client' => $clt
            ];
            $res->success($response);
            return new JsonResponse($res, $res->code);
        } else {
            $response = [

                'client' => $clt
            ];
            $res->success($response);
            return new JsonResponse($res, $res->code);
            //     if (auth()->user()->status_id == 2) {
            //         $res->fail("User disabled");
            //         return new JsonResponse($res, $res->code);
            //     }
            //     if (auth()->user()->status_id == 3) {
            //         $res->fail("User blocked");
            //         return new JsonResponse($res, $res->code);
            //     }
            //     if (auth()->user()->status_id == 4) {
            //         $res->fail("User in progress");
            //         return new JsonResponse($res, $res->code);
            //     }
        }
    }
    function loginSupplier(Request $request)
    {
        $res = new Result();
        //Send failed response if request is not valid
        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->messages()], 200);
        // }
        // if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        //     $user = Auth::user();
        //     if ($user->email_verified_at == NULL) {
        //         return response()->json(['error' => 'Please Verify Email'], 401);
        //     }
        // }
        //Request is validated
        //Crean token
        try {

            if (str_contains($request->user, '@')) {
                $request['email'] = $request['user'];

                $credentials = $request->only('email', 'password');
            } else if (str_contains($request->user, '+')) {
                $request['tel'] = $request['user'];
                $credentials = $request->only('tel', 'password');
            } else {
                $res->fail('Email , phone not valid');
                return new JsonResponse($res, $res->code);
            }
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }
        //Token created, return with success response and jwt token

        $supplier = Supplier::find(auth()->user()->userable_id);
        $user = auth()->user();
        $role = Role::whereHas('admins', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();
        $response['supplier'] = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'name' => $supplier['name'],
            // 'image'=>[''],
            'email' => $user['email'],
            'tel' => $user['tel'],
            'role' => $role['id'],
            'street' => $supplier['street'],
            'postcode' => $supplier['postcode'],
            'city' => $supplier['city'],
            'region' => $supplier['region'],
        ];
        $res->success($response);
        return new JsonResponse($res, $res->code);
    }
    function loginDelivery(Request $request)
    {
        $res = new Result();

        $credentials = $request->only('email', 'password');
        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        // if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        //     $user = Auth::user();
        //     if ($user->email_verified_at == NULL) {
        //         return response()->json(['error' => 'Please Verify Email'], 401);
        //     }
        // }
        //Request is validated
        //Crean token
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }
        //Token created, return with success response and jwt token

        $delivery = Delivery::find(auth()->user()->userable_id);
        $user = auth()->user();
        $role = Role::whereHas('admins', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();
        $response['delivery'] = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'vehicle' => $delivery['vehicle'],
            // 'image'=>[''],
            'email' => $user['email'],
            'tel' => $user['tel'],
            'role' => $role['id'],
            'street' => $delivery['lat'],
            'postcode' => $delivery['long']
        ];
        $res->success($response);
        return new JsonResponse($res, $res->code);
    }
    function doRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //  'name' => 'required',
            'email' => 'required|email|unique:users,email',   // required and email format validation
            'password' => 'required|min:8', // required and number field validation
            'confirm_password' => 'required|same:password',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return back()->withInput()->withErrors($validator);
            // validation failed redirect back to form

        } else {
            //validations are passed, save new user in database
            $User = new User;
            //   $User->name = $request->name;
            $User->email = $request->email;
            $User->password = bcrypt($request->password);
            // $User->role = $request->role;

            $User->save();
            $User->sendApiEmailVerificationNotification();
            $role = Role::find($request->role); // security issue
            $User->roles()->attach($role);
            //User created, return success response
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $User,
                'mail' => 'Please confirm yourself by clicking on verify user button sent to you on your email',
            ], Response::HTTP_OK);
        }
    }


    // logout method to clear the sesson of logged in user
    function logout(Request $request)
    {
        //valid credential
        // $validator = Validator::make($request->only('token'), [
        //     'token' => 'required'
        // ]);

        //Send failed response if request is not valid
        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->messages()], 200);
        // }

        //Request is validated, do logout
        try {

            JWTAuth::invalidate($request->bearerToken());

            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}
