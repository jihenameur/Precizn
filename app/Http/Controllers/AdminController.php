<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BaseModel\Result;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
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
        $this->request=$request;
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
                // return $validator->errors();
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form
            }
            //else {

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
            $res->fail($exception->getMessage());
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

                return ($this->getFilterByKeywordClosure($keyword));
            }
            $res->success($admins);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
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
        $res = new Result();
        try {
             $user = User::where('userable_id', $id)
                 ->where('userable_type', 'App\Models\Admin')->first();
          $admin=Admin::find($user->userable_id);
            $user->delete();

            $admin->delete();

            $res->success($user);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
    public function updateAdmin($id,Request $request)
    {
        $res = new Result();
        $admin = Admin::find($id);
        try {

            /** @var Supplier $supplier */
            $allRequestAttributes = $request->all();
            $user = $admin->user;

            $validator = Validator::make($request->all(), [
                'lastname' => 'required',
                // 'email' => 'required|email|unique:email',   // required and email format validation

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }
            $user->fill($allRequestAttributes);
            $admin->fill($allRequestAttributes);

            $user->update();
            $admin->update();

            $res->success($admin);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getLastPostionDelivery($id)
    {
        $res = new Result();
        try {
            $delivery=json_decode(Redis::get('deliveryPostion'.$id));
            $res->success($delivery);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
