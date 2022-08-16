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
/**
 * @OA\Tag(
 *     name="Administrateur",
 *     description="Gestion administrateur ",
 *
 * )
 */

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
 
    /**
     * @OA\Post(
     *      path="/addSuperAdmin",
     *      operationId="addSuperAdmin",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="create Admin / SuperAdmin",
     *      description="create admin",
     *     @OA\Parameter (
     *     in="query",
     *     name="firstname",
     *     required=true,
     *     description="firstname",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="lastname",
     *     required=true,
     *     description="lastname",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="gender",
     *     required=true,
     *     description="gender",
     *     @OA\Schema (type="string")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="tel",
     *     required=false,
     *     description="téléphone",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="email",
     *     required=true,
     *     description="email",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="password",
     *     required=true,
     *     description="password",
     *     @OA\Schema (type="string")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="confirm_password",
     *     required=true,
     *     description="confirm_password",
     *     @OA\Schema (type="string")
     *      ),
     * 
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="The email has already been taken",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
     */
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

   /**
     * @OA\Get(
     *      path="/get_admins/{per_page}",
     *      operationId="get_admins",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of admins",
     *      description="Returns all admins and associated provinces.",
     *    @OA\Parameter(
     *          name="per_page",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
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
        /**
     * @OA\Get(
     *      path="/getByid/{id}",
     *     tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      operationId="getByid",
     *      summary="Get admin by admin id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     * )
     */
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
     /**
     * @OA\Delete(
     *      path="/deleteAdmin/{id}",
     *      operationId="deleteAdmin",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="delete admin",
     *      description="delete one admin.",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Put(
     *      path="/updateAdmin/{id}",
     *      operationId="updateAdmin",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="update Admin / SuperAdmin",
     *      description="update admin",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="firstname",
     *     required=true,
     *     description="firstname",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="lastname",
     *     required=true,
     *     description="lastname",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="gender",
     *     required=true,
     *     description="gender",
     *     @OA\Schema (type="string")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="tel",
     *     required=false,
     *     description="téléphone",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="email",
     *     required=true,
     *     description="email",
     *     @OA\Schema (type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="The email has already been taken",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
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
     /**
     * @OA\Post(
     *      path="/getlastpostiondelivery/{id}",
     *      operationId="getLastPostionDelivery",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="get last postion of Delivery ",
     *      description="last position of delivery",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *   @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="The email has already been taken",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *    
     *     )
     */
    public function getLastPostionDelivery($id)
    {
        $res = new Result();
        try {
            $delivery = json_decode(Redis::get('deliveryPostion' . $id));
            $res->success($delivery);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

}
