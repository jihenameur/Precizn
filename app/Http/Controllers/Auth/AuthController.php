<?php

namespace App\Http\Controllers\Auth;

use App\BaseModel\Result;
use App\Http\Controllers\Controller;
use App\Models\Admin;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

/**
 * @OA\Tag(
 *     name="Authentification",
 *     description="Authentification & Authorisation ",
 *
 * )
 */
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
    
        $role = Role::whereHas('admins', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();
        if ($admin) {

            $admn = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $role['name'],
                'firstName' => $admin['firstname'],
                'lastName' => $admin['lastname'],

            ];
            $response = [
                'token' => $token,
         
                'admin' => $admn
            ];
            $res->success($response);
            return new JsonResponse($res, $res->code);
        } else {
            $admn = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $role['id'],
                'firstName' => $admin['firstName'],
                'lastName' => $admin['lastName'],

            ];
            $response = [
                'admin' => $admn
            ];
            $res->success($response);
            return new JsonResponse($res, $res->code);
        }
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
