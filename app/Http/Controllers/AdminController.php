<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BaseModel\Result;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    protected $controller;


    public function __construct(
        Request $request,
        Admin $model,
        Result $res
    ) {
        $this->request = $request;
        $this->model = $model;
        $this->res = $res;
    }
    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
               return ($validator->errors());
            }
            $allRequestAttributes = $request->all();
            $role_id = Role::where('short_name', config('roles.backadmin.superadmin'))->first()->id;
            $user = new User($allRequestAttributes);
            //$user->password = bcrypt($request->password);
            $user->password = bcrypt($request->password);
            $admin = $this->model->create($allRequestAttributes);
            // $user->sendApiEmailVerificationNotification();
            $admin = $this->model->find($admin->id);
            $admin->user()->save($user);

            $role = Role::find($role_id);
            $user->roles()->attach($role);
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            $user->token = $token;
            $user->status_id = 1;
            $user->update();
            $admn = [
                'id' => $admin['id'],
                'firstname' => $admin['firstname'],
                'lastname' => $admin['lastname'],
                'email' => $user['email'],
                'gender' => $admin['gender'],
                'role' => $role['id'],
                'status' => $user['status_id'],
            ];
            $response = [
                //'token' => $token,
                // 'refresh_token' => $refresh_token,
                // 'token_type' => 'bearer',
                // 'expires_in' => auth()->factory()->getTTL() * 60,
                'SuperAdmin' => $admn
            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function createAdmin(Request $request)
    {
        if (!Auth::user()->isAuthorized(['superadmin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',
                'tel' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return ($validator->errors());

            }
            $allRequestAttributes = $request->all();
            $role_id = Role::where('short_name', config('roles.backadmin.admin'))->first()->id;
            $user = new User($allRequestAttributes);
            //$user->password = bcrypt($request->password);
            $user->password = bcrypt($request->password);
            $admin = $this->model->create($allRequestAttributes);
            // $user->sendApiEmailVerificationNotification();
            $admin = $this->model->find($admin->id);
            $admin->user()->save($user);

            $role = Role::find($role_id);
            $user->roles()->attach($role);
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            $user->token = $token;
            $user->status_id = 1;
            $user->update();
            $admn = [
                'id' => $admin['id'],
                'firstname' => $admin['firstname'],
                'lastname' => $admin['lastname'],
                'email' => $user['email'],
                'gender' => $admin['gender'],
                'role' => $role['id'],
                'status' => $user['status_id'],
            ];
            $response = [
                //'token' => $token,
                // 'refresh_token' => $refresh_token,
                // 'token_type' => 'bearer',
                // 'expires_in' => auth()->factory()->getTTL() * 60,
                'Admin' => $admn
            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Filter or get all
     *
     * @return Collection|Model[]|mixed|void
     */
    public function all($per_page, Request $request)
    {
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $admins = Admin::paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $admins=$this->getFilterByKeywordClosure($keyword);
            }
            $res->success($admins);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getByid($id)
    {
        $res = new Result();
        try {
            $admin = Admin::where('id', '=', $id)->first();
            $res->success($admin);
        } catch (\Exception $exception) {
            $res->fail('erreur serveur');
        }
        return new JsonResponse($res, $res->code);
    }
    private function cleanKeywordSpaces($keyword)
    {
        $keyword = trim($keyword);
        $keyword = preg_replace('/\s+/', ' ', $keyword);
        return $keyword;
    }
    /**
     * Get filter by keyword
     *
     * @param $keyword
     * @return \Closure
     */
    private function getFilterByKeywordClosure($keyword)
    {

        $admins = Admin::where('firstname', 'like', "%$keyword%")->where('lastname', 'like', "%$keyword%")
            ->get();

        return $admins;
    }
    public function deleteAdmin($id)
    {
        if (!Auth::user()->isAuthorized(['superadmin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Admin')->first();
            $admin = Admin::find($user->userable_id);
            $user->delete();

            $admin->delete();

            $res->success($user);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Supplier|mixed|void
     */
    public function updateAdmin($id, Request $request)
    {
        $res = new Result();
        $admin = Admin::find($id);
        try {

            /** @var Supplier $supplier */
            $allRequestAttributes = $request->all();
            $user = $admin->user;

            $validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }
            $user->fill($allRequestAttributes);
            $admin->fill($allRequestAttributes);

            $user->update();
            $admin->update();

            $res->success($admin);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getLastPostionDelivery($id)
    {
        $res = new Result();
        try {
            $delivery = json_decode(Redis::get('deliveryPostion' . $id));
            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
