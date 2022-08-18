<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\ReqHelper;
use App\Jobs\Admin\SendNewSuuplierNotification;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Auth\VerificationApiController;
use App\Http\Resources\SupplierResource;
use App\Jobs\Admin\SendCommandClientNotification;
use App\Jobs\SendCommandClientNotification as JobsSendCommandClientNotification;
use App\Jobs\SendNewSuuplierNotification as JobsSendNewSuuplierNotification;
use App\Models\Client;
use App\Models\Command;
use App\Models\File;
use App\Notifications\CommandClientNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
/**
 * @OA\Tag(
 *     name="Supplier",
 *     description="Gestion supplier ",
 *
 * )
 */
class SupplierController extends Controller
{
    protected $controller;

    public function __construct(
        Request                   $request,
        Supplier                  $model,
        LocationController        $locationController,
        Result                    $res,
        ReqHelper                 $reqHelper,
        VerificationApiController $verificationApiController


    )
    {
        $this->model = $model;
        $this->locationController = $locationController;
        $this->res = $res;
        $this->reqHelper = $reqHelper;
        $this->verificationApiController = $verificationApiController;
    }
/**
     * @OA\Post(
     *      path="/addSupplier",
     *      operationId="addSupplier",
     *      tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      summary="create supplier" ,
     *      description="create supplier",
     *     @OA\Parameter (
     *     in="query",
     *     name="firstName",
     *     required=true,
     *     description="firstName",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="lastName",
     *     required=true,
     *     description="lastName",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
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
     *  @OA\Parameter (
     *     in="query",
     *     name="password",
     *     required=true,
     *     description="password",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="confirm_password",
     *     required=true,
     *     description="confirm_password",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="starttime",
     *     required=false,
     *     description="start time",
     *     @OA\Schema (type="date-time")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="closetime",
     *     required=false,
     *     description="close time",
     *     @OA\Schema (type="date-time")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="star",
     *     required=false,
     *     description="star",
     *     @OA\Schema (type="integer")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="qantityVente",
     *     required=false,
     *     description="qantityVente",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="delivery",
     *     required=false,
     *     description="delivery",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="take_away",
     *     required=false,
     *     description="take_away",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="on_site",
     *     required=false,
     *     description="on_site",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="category",
     *     required=false,
     *     description="category",
     *     @OA\Items( 
     *              type="array", 
     *          )),
     *   @OA\Parameter (
     *     in="query",
     *     name="commission",
     *     required=false,
     *     description="commission",
     *     @OA\Schema (type="integer")
     *      ),
     *    
     *    @OA\Parameter (
     *     in="query",
     *     name="street",
     *     required=true,
     *     description="street",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="postcode",
     *     required=true,
     *     description="postcode",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="region",
     *     required=true,
     *     description="region",
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
     *          description="Bad request. User ID must be an integer and bigger than 0",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   )
     */
    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                //'email' => 'required|email|unique:users,email',   // required and email format validation
                'email' => 'email|unique:users,email',   // required and email format validation

                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',
                'firstName' => 'required',
                'lastName' => 'required',
                'tel' => 'required|unique:users,tel',
                'region' => 'required',
                'city' => 'required',
                'postcode' => 'required',

            ]); // create the validations


            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return ($validator->errors());
            }
            $role_id = Role::where('short_name', config('roles.backadmin.supplier'))->first()->id;

            $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
            if (is_array($latlong) && $latlong[0]['long'] > 0) {
                $request['lat'] = $latlong[0]['lat'];
                $request['long'] = $latlong[0]['long'];
            } else {
                throw new Exception("Err: address not found");
            }
            $chekphoneExist = $this->verificationApiController->checkPhoneExists($request->tel);
            if ($chekphoneExist == "phone exists") {
                $res->fail("phone exists");
                return new JsonResponse($res, $res->code);
            }
            $allRequestAttributes = $request->all();
            $user = new User($allRequestAttributes);
            $user->password = bcrypt($request->password);
            $supplier = new Supplier();
            $supplier->name = $request->name;
            $supplier->firstName = $request->firstName;
            $supplier->lastName = $request->lastName;
            //$supplier->tel = $request->tel;
            $supplier->starttime = $request->starttime;
            $supplier->closetime = $request->closetime;

            $supplier->star = $request->star;
            $supplier->qantityVente = $request->qantityVente;
            $supplier->delivery = $request->delivery;
            $supplier->take_away = $request->take_away;
            $supplier->on_site = $request->on_site;
            $supplier->street = $request->street;
            $supplier->postcode = $request->postcode;
            $supplier->city = $request->city;
            $supplier->region = $request->region;
            $supplier->commission = $request->commission;
            $supplier->lat = $request->lat;
            $supplier->long = $request->long;
            $user->status_id = 4;

            $supplier->save();
            $supplier->user()->save($user);
            // $user->sendApiEmailVerificationNotification();
            $supplier = $this->model->find($supplier->id);
            $role = Role::find($role_id);
            $user->roles()->attach($role);
            $categories = $request->category;
            if (!is_array($categories)) {
                $categories = json_decode($request->category);
            }
            foreach ($categories as $key => $value) {
                $category = Category::find($value);
                $supplier->categorys()->attach($category);
            }


            //     return $supplier;
            // }
            $response['supplier'] = [
                "id" => $supplier->id,
                "name" => $supplier->name,
                "firstName" => $supplier->firstName,
                "lastName" => $supplier->lastName

            ];
            $res->success($response);
            JobsSendNewSuuplierNotification::dispatch($supplier);

        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function addImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'type' => 'required|in:principal,couverture'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return ($validator->errors());
            }
            $supplier = Supplier::find(Auth::user()->userable_id);
            if ($request->type == "principal") {
                if ($request->file('image')) {
                    $file = $request->file('image');
                    $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('public/Suppliers'), $name); // your folder path
                    $file = new File();
                    $file->name = $name;
                    $file->path = asset('public/Suppliers/' . $name);
                    $file->user_id = Auth::user()->id;
                    $file->save();
                    $file->supplier()->attach($supplier, ['type' => $request->type]);
                }
            } else if ($request->type == "couverture") {
                if ($request->file('image')) {
                    $file = $request->file('image');
                    $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('public/SuppliersCouverture'), $name); // your folder path
                    $file = new File();
                    $file->name = $name;
                    $file->path = asset('public/SuppliersCouverture/' . $name);
                    $file->user_id = Auth::user()->id;
                    $file->save();
                    $file->supplier()->attach($supplier, ['type' => $request->type]);
                }
            }
            $response['supplier'] = [
                "id" => $supplier->id,
                "name" => $supplier->name,
                "firstname" => $supplier->firstName,
                "lastname" => $supplier->firstName,
                "image" => $file->path,
                "type" => $file->supplier[0]->pivot->type

            ];

            $res->success($response);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
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
     /**
     * @OA\Get(
     *      path="/getAllSupplier/{per_page}",
     *      operationId="getAllSupplier",
     *      tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of supplier",
     *      description="Returns all supplier and associated provinces.",
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
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:firstName,lastName,id' // complete the akak list
            ]);
            $orderBy = $request->orderBy;
        }
        if ($request->has('orderByType') && $request->orderByType != null) {
            $this->validate($request, [
                'orderByType' => 'required|in:ASC,DESC' // complete the akak list
            ]);
            $orderByType = $request->orderByType;
        }
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $suppliers = Supplier::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType));
            }
            $res->success($suppliers);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG', true)) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Filter or get By Id
     *
     * @return Collection|Model[]|mixed|void
     */
    /**
     * @OA\Get(
     *      path="/getSupplierById/{id}",
     *     tags={"Client"},
     *     security={{"Authorization":{}}},
     *      operationId="getSupplierById",
     *      summary="Get supplier by supplier id",
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
    public function getById($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {

            $supplier = Supplier::where('id', '=', $id)->first();
            $res->success($supplier);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Clean keyword from extra spaces
     *
     * @param $keyword
     * @return string|string[]|null
     */
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
    private function getFilterByKeywordClosure($keyword, $orderBy, $orderByType)
    {
        $res = new Result();
        try {

            $suppliers = Supplier::where('name', 'like', "%$keyword%")
                ->orderBy($orderBy, $orderByType)
                ->get();

            $res->success([
                'suppliers' => SupplierResource::collection($suppliers),
            ]);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
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
     /**
     * @OA\Post(
     *      path="/updateSupplier/{id}",
     *      operationId="updateSupplier",
     *      tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      summary="update supplier",
     *      description="update supplier",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="firstName",
     *     required=true,
     *     description="firstName",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="lastName",
     *     required=true,
     *     description="lastName",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
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
     *   @OA\Parameter (
     *     in="query",
     *     name="starttime",
     *     required=false,
     *     description="start time",
     *     @OA\Schema (type="date-time")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="closetime",
     *     required=false,
     *     description="close time",
     *     @OA\Schema (type="date-time")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="star",
     *     required=false,
     *     description="star",
     *     @OA\Schema (type="integer")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="qantityVente",
     *     required=false,
     *     description="qantityVente",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="delivery",
     *     required=false,
     *     description="delivery",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="take_away",
     *     required=false,
     *     description="take_away",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="on_site",
     *     required=false,
     *     description="on_site",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="category",
     *     required=false,
     *     description="category",
     *     @OA\Items( 
     *              type="array", 
     *          )),
     *   @OA\Parameter (
     *     in="query",
     *     name="commission",
     *     required=false,
     *     description="commission",
     *     @OA\Schema (type="integer")
     *      ),
     *    
     *    @OA\Parameter (
     *     in="query",
     *     name="street",
     *     required=true,
     *     description="street",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="postcode",
     *     required=true,
     *     description="postcode",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="region",
     *     required=true,
     *     description="region",
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
    public function update($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Supplier')->first();
            $supplier = Supplier::find($id);
            if ($request->street && $request->postcode && $request->city && $request->region) {
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $request['lat'] = $latlong[0]['lat'];
                    $request['long'] = $latlong[0]['long'];
                } else {
                    throw new Exception("Err: address not found");
                }
            }
            //$user->password = bcrypt($request->password);
            $supplier->name = $request->name;
            $supplier->firstName = $request->firstName;
            $supplier->lastName = $request->lastName;
            $supplier->starttime = $request->starttime;
            $supplier->closetime = $request->closetime;
            $supplier->star = $request->star;
            $supplier->qantityVente = $request->qantityVente;
            $supplier->delivery = $request->delivery;
            $supplier->take_away = $request->take_away;
            $supplier->on_site = $request->on_site;
            $supplier->street = $request->street;
            $supplier->postcode = $request->postcode;
            $supplier->city = $request->city;
            $supplier->region = $request->region;
            $supplier->commission = $request->commission;
            $supplier->lat = $request->lat;
            $supplier->long = $request->long;
            $user->status_id = 4;
            $supplier->update();
            $user->update();
            // $user->sendApiEmailVerificationNotification();
            $supplier = $this->model->find($supplier->id);
            $categories = $request->category;
            if (!is_array($categories)) {
                $categories = json_decode($request->category);
                $supplier->categorys()->detach();
            }
            foreach ($categories as $key => $value) {
                $category = Category::find($value);
                $supplier->categorys()->attach($category);
            }
            $response['supplier'] = [
                "id" => $supplier->id,
                "name" => $supplier->name,
                "firstName" => $supplier->firstName,
                "lastName" => $supplier->lastName

            ];

            $res->success($response);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

       /**
     * @OA\Post(
     *      path="/updatesupplierpassword/{id}",
     *      operationId="updatesupplierpassword",
     *      tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      summary="update password supplier",
     *      description="update password supplier",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true, 
     *         
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="oldpassword",
     *     required=true,
     *     description="oldpassword",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="newpassword",
     *     required=true,
     *     description="newpassword",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="confirm_password",
     *     required=true,
     *     description="confirm_password",
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

    public function updateSupplierPW($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        /** @var Client $client */
        $res = new Result();
        try {

            $validator = Validator::make($request->all(), [
                'oldpassword' => 'required|min:8', // required and number field validation
                'newpassword' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:newpassword',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());
            }
            $supplier = Supplier::find($id);
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Supplier')->first();
            if (Hash::check($request->oldpassword, $user->password, []) == true) {
                $user->password = bcrypt($request->newpassword);
                $user->update();
            } else {
                $res->fail("Password not correct");
                return new JsonResponse($res, $res->code);
            }
            $role = Role::whereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();


            $res->success($supplier);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @inheritDoc
     *
     * @param null $id
     * @return bool|mixed|void
     */
    
    public function delete($id)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            /** @var Supplier $supplier */
            $supplier = Supplier::find($id);

            $supplier->user->delete();
            $supplier->delete();

            $res->success($supplier);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function resetPWSupplier(Request $request)
    {
        $res = new Result();
        try {
            $user = User::where('tel', $request->tel)
                ->where('userable_type', 'App\Models\Supplier')->first();
            //$this->verificationApiController->toOrange($user->id, $user->tel);
            $token = Str::random(64);

            DB::table('password_resets')->insert([
                'email' => $user->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
            $clt['client'] = [
                'email' => $user['email'],
                'tel' => $user['tel']
            ];
            $res->success($clt);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function verifySmsResetPW(Request $request)
    {

        $res = new Result();
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'size:6',
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return ($validator->errors());
            }
            $user = User::where('tel', $request->tel)->first();
            if ($request['code'] == $user->smscode) {
                $user->update(['password' => bcrypt($request->password)]);

                DB::table('password_resets')->where(['email' => $request->email])->delete();
                $supplier = Supplier::find($user->userable_id);
                $role = Role::whereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->first();

                $supp = [
                    'id' => $supplier['id'],
                    'firstname' => $supplier['firstname'],
                    'lastname' => $supplier['lastname'],
                    'image' => $supplier['image'],
                    'email' => $user['email'],
                    'status' => $user['status_id'],
                    'tel' => $user['tel'],
                    'role' => $role['id']
                ];
                $response = [
                    'token' => $user['token'],
                    // 'token_type' => 'bearer',
                    // 'expires_in' => auth()->factory()->getTTL() * 60,
                    'supplier' => $supp
                ];
                $res->success($response);
            } else {
                $res->fail('Code not verified');
            }
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);

        //  $date = date_create();
        //  DB::table('users')->where('id', Auth::id())->update(['phone_verified_at' => date_format($date, 'Y-m-d H:i:s')]);

    }

    /**
     * @OA\Get(
     *      path="/statusSupplier",
     *     tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      operationId="statusSupplier",
     *      summary="Get supplier by supplier id && status",
     *     @OA\Parameter (
     *     in="query",
     *     name="id",
     *     required=true,
     *     description="id",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="status_id",
     *     required=true,
     *     description="status_id",
     *     @OA\Schema (type="string")
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

    public function statusSupplier(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required', // required and number field validation
            'status_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $request->id)
                ->where('userable_type', 'App\Models\Supplier')->first();
            User::where('id', $user->id)->update([
                'status_id' => $request->status_id
            ]);


            $res->success($user);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

     /**
     * @OA\Post(
     *      path="/supplieraccceptrefusecommand",
     *     tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      operationId="supplieraccceptrefusecommand",
     *      summary="accept && Or refused order by supplier", 
     *    @OA\Parameter (
     *     in="query",
     *     name="command_id",
     *     required=true,
     *     description="command_id",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="action",
     *     required=true,
     *     description="action",
     *     @OA\Schema (type="string")
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

    public function supplierAccceptRefuseCommand(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'command_id' => 'required', // required and number field validation
            'action' => 'required|in:accept,refuse'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $command = Command::find($request->command_id);
            $fromUser = Supplier::find(auth()->user()->userable_id);
            $toUser = Client::find($command->client_id);
            if ($request->action == 'accept') {
                $command->status = 1;
            } else if ($request->action == 'refuse') {
                $command->status = 2;
            }
            $command->update();

           // $toUser->notify(new CommandClientNotification($command, $fromUser, $command->status));
            JobsSendCommandClientNotification::dispatch($command,$fromUser,$toUser,$request->action);

            // $toUser->notify(new CommandClientNotification($command, $fromUser, $command->status));
            JobsSendCommandClientNotification::dispatch($command, $fromUser, $toUser, $request->action);


            -$res->success($command);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * deleted supplier
     */
     /**
     * @OA\Delete(
     *      path="/deleteSupplier/{id}",
     *      operationId="deleteSupplier",
     *      tags={"Supplier"},
     *     security={{"Authorization":{}}},
     *      summary="delete supplier",
     *      description="delete one supplier.",
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
    public function deleteSupplier($id)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Supplier')->first();
            $supplier = Supplier::find($id);
            $products = Product::whereHas('suppliers', function ($q) use ($id) {
                $q->where('supplier_id', $id);
            })
                ->where('private', 1)->get();
            foreach ($products as $product) {
                $product->delete();
            }
            $supplier->delete();
            $user->delete();

            $res->success("Deleted");
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
