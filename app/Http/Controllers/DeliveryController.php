<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Jobs\Admin\ChangeDeliveryPositionJob;
use App\Models\Admin;
use App\Models\Command;
use App\Models\Delivery;
use App\Models\Delivery_Hours;
use App\Models\RequestDelivery;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Supplier;
use App\Notifications\DeliveryDispoNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewDeliveryNotify;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Type\Decimal;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Http\Controllers\Auth\VerificationApiController;
use App\Models\File;

use function PHPUnit\Framework\returnSelf;
/**
 * @OA\Tag(
 *     name="Delivery",
 *     description="Gestion Delivery ",
 *
 * )
 */
class DeliveryController extends Controller
{
    protected $controller;

    public function __construct(
        Request $request,
        Delivery $model,
        Controller $controller = null,
        LocationController $locationController,
        VerificationApiController $verificationApiController
    ) {
        $this->model = $model;
        $this->locationController = $locationController;
        $this->verificationApiController = $verificationApiController;
    }
/**
     * @OA\Post(
     *      path="/addDelivery",
     *      operationId="addDelivery",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="create delivery" ,
     *      description="create delivery",
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
     *     name="vehicle",
     *     required=false,
     *     description="vehicle",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="Mark_vehicle",
     *     required=false,
     *     description="Mark vehicle",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="start_worktime",
     *     required=false,
     *     description="start_worktime",
     *     @OA\Schema (type="time", format="time")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="end_worktime",
     *     required=false,
     *     description="end_worktime",
     *     @OA\Schema (type="time", format="time")
     *      ),
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
     *
     *   @OA\Parameter (
     *     in="query",
     *     name="salary",
     *     required=true,
     *     description="salary",
     *     @OA\Schema (type="integer")
     *      ),
     *    @OA\Parameter(
     *     in="query",
     *     name="cycle",
     *     required=false,
     *     description="cycle",
     *     @OA\Schema(type="string",enum={"OFF", "ON"})
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="rating",
     *     required=false,
     *     description="rating",
     *     @OA\Schema (type="double(8,2)")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="image",
     *     required=false,
     *     description="image",
     *     @OA\Schema (type="file")
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
                'firstName' => 'required',
                'lastName' => 'required',
                'vehicle' => 'required|in:Scooter,Voiture,Velo',
                'Mark_vehicle' => 'required',
                'start_worktime' => 'required',
                'end_worktime' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',
                'tel' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $role_id = Role::where('short_name', config('roles.backadmin.delivery'))->first();
            $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
            if (is_array($latlong) && $latlong[0]['long'] > 0) {
                $request['lat'] = $latlong[0]['lat'];
                $request['long'] = $latlong[0]['long'];
            } else {
                return "Err: address not found";
            }
            $chekphoneExist = $this->verificationApiController->checkPhoneExists($request->tel);
            if ($chekphoneExist == "phone exists") {
                $res->fail("phone exists");
                return new JsonResponse($res, $res->code);
            }

            $allRequestAttributes = $request->all();
            $user = new User($allRequestAttributes);
            $user->password = bcrypt($request->password);
            /** @var Delivery $delivery */


            $delivery =  $this->model->create($allRequestAttributes);

            $delivery->user()->save($user);
            // $user->sendApiEmailVerificationNotification();
            $delivery = $this->model->find($delivery->id);
            $role = Role::find($role_id);
            $user->roles()->attach($role);
            //$this->verificationApiController->toOrange($user->id, $request->phone);

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {$res->fail('erreur serveur 500'); }
        }
        return new JsonResponse($res, $res->code);
    }
    public function addImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return $validator->errors();
            }
            $delivery = Delivery::find(Auth::user()->userable_id);
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Deliverys'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Deliverys/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $delivery->file_id = $file->id;
            $delivery->update();
            $response['delivery'] = [
                "id"         =>  $delivery->id,
                "firstname"     =>  $delivery->firstName,
                "lastname"     =>  $delivery->lastName,
                "image"     =>  $file->path

            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {$res->fail('erreur serveur 500'); }
        }
        return new JsonResponse($res, $res->code);
    }
    public function updateImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $delivery = Delivery::find(Auth::user()->userable_id);
            if ($request->file('image')) {
                $image = File::find($delivery->file_id);
                unlink('public/Deliverys/' . $image->name);
                $image->delete();
                $file = $request->file('image');
                $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Deliverys'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Deliverys/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $delivery->file_id = $file->id;
            $delivery->update();
            $response['client'] = [
                "id"         =>  $delivery->id,
                "firstname"     =>  $delivery->firstName,
                "lastname"     =>  $delivery->lastName,
                "image"     =>  $file->path

            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {$res->fail('erreur serveur 500'); }
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
     *      path="/getAllDelivery/{per_page}",
     *      operationId="getAllDelivery",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of delivery",
     *      description="Returns all delivery and associated provinces.",
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

        // $this->validate($request, [
        //     'available' => 'numeric',
        //     'region' => 'numeric'
        // ]);
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:firstName,lastName,region,created_at' // complete the akak list
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
            $disponible = $request->has('available') ? $request->available :  null;
            $region = $request->has('region') ? $request->region :  null;
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType));
            }

            $delivery =  Delivery::query();

            if (!empty($disponible)) {
                $delivery->where('available', 'like', '%' . $disponible . '%');
            }
            if (!empty($region)) {
                $delivery->where('region', 'like', '%' . $region . '%');
            }

            $delivery = $delivery->orderBy($orderBy, $orderByType)->paginate($per_page);

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {$res->fail('erreur serveur 500'); }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Get(
     *      path="/getDeliveryById/{id}",
     *     tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      operationId="getDeliveryById",
     *      summary="Get delivery by delivery id",
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
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {

            $delivery = Delivery::find($id);

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {$res->fail('erreur serveur 500'); }
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
            $delivery = Delivery::whereHas('user', function ($q) use ($keyword) {
                $q->where('email', 'like', "%$keyword%");
            })
                ->orWhere('firstName', 'like', "%$keyword%")
                ->orWhere('lastName', 'like', "%$keyword%")
                ->orderBy($orderBy, $orderByType)
                ->get();
            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Client|mixed|void
     */
    /**
     * @OA\Post(
     *      path="/updateDelivery/{id}",
     *      operationId="updateDelivery",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="update delivery",
     *      description="update delivery",
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
     *     name="vehicle",
     *     required=false,
     *     description="vehicle",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="Mark_vehicle",
     *     required=false,
     *     description="Mark vehicle",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="start_worktime",
     *     required=false,
     *     description="start_worktime",
     *     @OA\Schema (type="time", format="time")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="end_worktime",
     *     required=false,
     *     description="end_worktime",
     *     @OA\Schema (type="time", format="time")
     *      ),
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
     *
     *   @OA\Parameter (
     *     in="query",
     *     name="salary",
     *     required=true,
     *     description="salary",
     *     @OA\Schema (type="integer")
     *      ),
     *    @OA\Parameter(
     *     in="query",
     *     name="cycle",
     *     required=false,
     *     description="cycle",
     *     @OA\Schema(type="string",enum={"OFF", "ON"})
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="rating",
     *     required=false,
     *     description="rating",
     *     @OA\Schema (type="double(8,2)")
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
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'vehicle' => 'required|in:Scooter,Voiture,Velo',
            'Mark_vehicle' => 'required',
            'start_worktime' => 'required',
            'end_worktime' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            /** @var Delivery $delivery */
            if ($request->file('photo')) {
                $file = $request->file('photo');
                $filename = $file->getClientOriginalName();
                //dd( $filename);

                $file->move(public_path('public/Deliverys'), $filename);
                $request['image'] = $filename;
            }
            $allRequestAttributes = $request->all();
            $delivery = Delivery::find($id);
            $user = $delivery->user;
            $user->fill($allRequestAttributes);
            $delivery->fill($allRequestAttributes);
            $user->update();
            $delivery->update();

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @return bool|mixed|void
     */
    /**
     * @OA\Delete(
     *      path="/deleteDelivery/{id}",
     *      operationId="deleteDelivery",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="delete delivery",
     *      description="delete one delivery.",
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
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Delivery')->first();
            $delivery = Delivery::find($id);
            $delivery->delete();
            $user->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
     /**
     * @OA\Post(
     *      path="/acceptCommand",
     *      operationId="acceptCommand",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Accepte ordre by delivery",
     *
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="command_id",
     *     required=true,
     *     description="command_id",
     *     @OA\Schema (type="integer")
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
     *          description="bad request",
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
    public function acceptCommand(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'command_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $delivery = Delivery::find($request['delivery_id']);
            $command = Command::find($request['command_id']);
            $delivReq = RequestDelivery::where('delivery_id', $request['delivery_id'])
                ->where('command_id', $request['command_id'])
                ->first();
            $command->delivery_id = $delivery->id;
            $command->update();
            $delivReq->accept = 1;
            $delivReq->update();
            $delivery->available = 0;
            $delivery->update();
            $res->success("command accepted");

        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);

    }
     /**
     * @OA\Get(
     *      path="/notifCommand",
     *      operationId="notifCommand",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of delivery",
     *      description="Returns all delivery and associated provinces.",
     * *    @OA\Parameter (
     *     in="query",
     *     name="command_id",
     *     required=true,
     *     description="command_id",
     *     @OA\Schema (type="integer")
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
    public function notifCommand(Request $request)
    {
        $res = new Result();
        try {
            $deliverys = Delivery::where('available', 1)->get();
            $command = Command::find($request['command_id']);
            $supplier = Supplier::where('id', $command->supplier_id)
                ->get();
            $distance = $this->locationController->getdistances($supplier[0], $deliverys);
            foreach ($distance as $key => $value) {
                if ($value['distance'] <= 6) {

                    $requetDeliv = new RequestDelivery();
                    $requetDeliv->command_id = $command->id;
                    $requetDeliv->delivery_id = $value['User']->id;
                    $requetDeliv->date = date("Y-m-d H:i:s");
                    $requetDeliv->save();

                    // Notification::route('id', $value['User']->id) //Sending mail to subscriber
                    //     ->notify(new NewDeliveryNotify($command)); //With new post
                    sleep(10);
                    $deliv = RequestDelivery::where('delivery_id', $value['User']->id)
                        ->where('command_id', $request['command_id'])
                        ->first();

                    if ($deliv['accept'] == 1) {
                        $res->success($deliv);
                        return new JsonResponse($res, $res->code);
                    }
                }
            }
            // return 'No Delivery disp';
            $res->fail('No Delivery disp');
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
     /**
     * @OA\Post(
     *      path="/rejectCommand",
     *      operationId="rejectCommand",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Accepte ordre by delivery",
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="command_id",
     *     required=true,
     *     description="command_id",
     *     @OA\Schema (type="integer")
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
     *          description="bad request",
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
    public function rejectCommand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'command_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $delivery = Delivery::find($request['delivery_id']);
            $command = Command::find($request['command_id']);
            $delivReq = RequestDelivery::where('delivery_id', $request['delivery_id'])
                ->where('command_id', $request['command_id'])
                ->get();

            $command->delivery_id = $delivery->id;
            $command->update();
            $delivReq[0]->accept = 0;
            $delivReq[0]->update();
            $res->success("command rejected");

        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
    public function ListCommandDelivered($per_page, Request $request)
    {

        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $commands = Command::whereHas('requestDelivery', function ($q) use ($request) {
                $q->where('delivery_id', $request['delivery_id']);
                $q->where('accept', 1);
            })
                ->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
      /**
     * @OA\Get(
     *      path="/ListCommandRejected/{per_page}",
     *      operationId="ListCommandRejected",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of order refuced",
     *      description="Returns all  order refuced.",
     *    @OA\Parameter(
     *          name="per_page",
     *          in="path",
     *          required=true,
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
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
    public function ListCommandRejected($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $commands = Command::whereHas('requestDelivery', function ($q) use ($request) {
                $q->where('delivery_id', $request['delivery_id']);
                $q->where('accept', 0);
                $q->orwhere('accept', null);
            })->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
           else {
               $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Get(
     *      path="/gainCommands",
     *      operationId="gainCommands",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of order refuced",
     *      description="Returns all  order refuced.",
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="date",
     *     required=true,
     *     description="date",
     *     @OA\Schema(
     *           type="string",
     *           format="date-time"
     *        ),
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
    public function gainCommands(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'date' => 'required|date'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $date = new DateTime($request['date']);
            $commands = Command::where('delivery_id', $request['delivery_id'])
                ->whereDate('date', $date->format('Y-m-d'))
                ->get();
            $daygain = 0;
            foreach ($commands as $key => $value) {
                $daygain = $daygain + $value->delivery_price;
            }
            // return $daygain;
            $res->success($daygain);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
             else {
                 $res->fail('erreur serveur 500');
             }
        }
        return new JsonResponse($res, $res->code);
    }
     /**
     * @OA\Get(
     *      path="/CommandDelivered",
     *      operationId="CommandDelivered",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="List Orders delivered",
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="command_id",
     *     required=true,
     *     description="command_id",
     *     @OA\Schema (type="integer")
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
     *          description="bad request",
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
    public function CommandDelivered(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'command_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
        $delivery = Delivery::find($request['delivery_id']);
        $command = Command::find($request['command_id']);
        $command->status = 2;
        $command->update();
        $delivery->available = 1;
        $delivery->update();

        $res->success("command delivered");
    } catch (\Exception $exception) {
         if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
         else {
             $res->fail('erreur serveur 500');
         }
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/generateInvoicePDF",
     *      operationId="generateInvoicePDF",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="generate invoice pdf",
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="action",
     *     required=true,
     *     description="action",
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
     *          description="bad request",
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
    public function generateInvoicePDF()
    {
        $pdf = PDF::loadView('myPDF');
        return $pdf->download('nicesnippets.pdf');
    }
    public function hoursWork(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'action' => 'required',
            'delivery_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $currentTime = Carbon::now()->setTimezone('Europe/paris')->format('Y-m-d H:i');

            $currentDate = Carbon::now()->setTimezone('Europe/paris')->format('Y-m-d');

            if ($request['action'] == 'start') {
                $hoursWork = new Delivery_Hours();
                $hoursWork->delivery_id = $request->delivery_id;
                $hoursWork->date =  $currentDate;
                $hoursWork->start_hour =  $currentTime;
                $hoursWork->end_hour =  $currentTime;
                $hoursWork->hours = 0;
                $hoursWork->save();
                $fromUser = Delivery::find($hoursWork->delivery_id);
                $toUser  = Admin::find(1);
                $status = "start Work";
                $toUser->notify(new DeliveryDispoNotification($fromUser, $status));
            } else if ($request['action'] == 'end') {
                $hoursWork = Delivery_Hours::where('delivery_id', $request['delivery_id'])
                    ->where('hours', 0)->first();
                $startTime = Carbon::parse($hoursWork->start_hour);
                $diff_in_hours = $startTime->diff($currentTime)->format('%H:%I:%S');
                $hoursWork->end_hour =  $currentTime;
                $hoursWork->hours =  $diff_in_hours;
                $hoursWork->update();
                $fromUser = Delivery::find($hoursWork->delivery_id);
                $toUser  = Admin::find(1);
                $status = "end Work";
                $toUser->notify(new DeliveryDispoNotification($fromUser, $status));
            } else {
                $res->fail("erreur action");
                return new JsonResponse($res, $res->code);
            }
            $res->success($hoursWork);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
             else {
                 $res->fail('erreur serveur 500');
             }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Get(
     *      path="/statisDeliv",
     *      operationId="statisDeliv",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="orders are delivered within a given time limit.",
     *     @OA\Parameter (
     *     in="query",
     *     name="from",
     *     required=true,
     *     description="from",
     *    @OA\Schema(
     *           type="string",
     *           format="date-time"
     *        ),
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="to",
     *     required=true,
     *     description="to",
     *     @OA\Schema(
     *           type="string",
     *           format="date-time"
     *        ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema (type="integer")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="action",
     *     required=true,
     *     description="action",
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
     *          description="bad request",
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
    public function statisDeliv(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date',
            'delivery_id' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $from = new DateTime($request['from']);
            $to = new DateTime($request['to']);
            $commands = Command::where('delivery_id', $request['delivery_id'])
                ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                ->get();
            $hoursWork = Delivery_Hours::where('delivery_id', $request['delivery_id'])
                ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                ->get();
            $gain = [];
            $totalgain = 0;
            $tips = 0;
            $hours = '00:00';
            for ($i = $from; $i <= $to; $i->modify('+1 day')) {
                $commandsDay = Command::where('delivery_id', $request['delivery_id'])
                    ->whereDate('date', $i->format('Y-m-d'))
                    ->get();
                $daygains = 0;
                //dd($commandsDay);

                foreach ($commandsDay as $key => $value) {
                    $daygains = $daygains + $value->delivery_price + $value->tip;
                    $totalgain = $totalgain + $value->delivery_price;
                    $tips = $tips + $value->tip;
                }
                $temp = [
                    'date' => $i->format('Y-m-d'),
                    'gain' => $daygains
                ];
                array_push($gain, $temp);
            }
            $sumSeconds = 0;
            foreach ($hoursWork as $key => $value) {

                //$hours = Carbon::createFromFormat('H:i',$hours)->addHours(intval($value->hours))->format('H:I');
                $explodedTime = explode(':', $value->hours);
                $seconds = $explodedTime[0] * 3600 + $explodedTime[1] * 60 + $explodedTime[2];

                $sumSeconds = $sumSeconds + $seconds;
            }
            $hours = floor($sumSeconds / 3600);
            $minutes = floor(($sumSeconds % 3600) / 60);
            $seconds = (($sumSeconds % 3600) % 60);
            $sumTime = $hours . ':' . $minutes;
            // dd($sumTime);

            // foreach ($commands as $key => $value) {
            //     $totalgain = $totalgain + $value->delivery_price;
            //     $tips=$tips+$value->tip;
            // }
            //dd($totalgain);
            // return $daygain;
            $stat = ["gainsDay" => $gain, "enLigne" => $sumTime, "courses" => count($commands), "priceCourses" => $totalgain, "tips" => $tips, "total" => $totalgain + $tips];
            $res->success($stat);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
             else {
                 $res->fail('erreur serveur 500');
             }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Post(
     *      path="/sendposition",
     *      operationId="sendposition",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="locate delivery position",
     *  @OA\Parameter (
     *     in="query",
     *     name="long",
     *     required=true,
     *     description="longitude",
     *     @OA\Schema (type="integer")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="lat",
     *     required=true,
     *     description="latitude",
     *     @OA\Schema (type="integer")
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
     *          description="bad request",
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
    public function sendDeliveryPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'long' => 'required',
            'lat' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $delivery =  Auth::user()->userable;
        $value = Redis::set('deliveryPostion' . $delivery->id, json_encode([
            'id' => $delivery->id,
            'lng' => $request->long,
            'lat' => $request->lat,

        ]));

        // brodcast to admins
        event(new \App\Events\Admin\DeliveryPosition(json_decode(Redis::get('deliveryPostion' . $delivery->id))));
        dispatch(new ChangeDeliveryPositionJob($delivery,json_decode(Redis::get('deliveryPostion' . $delivery->id))));

        return response()->json(json_decode(Redis::get('deliveryPostion' . $delivery->id)));
    }
    /**
     * @OA\Post(
     *      path="/statusdelivery",
     *      operationId="statusdelivery",
     *      tags={"Delivery"},
     *     security={{"Authorization":{}}},
     *      summary="Get Status delivery",
     *  @OA\Parameter (
     *     in="query",
     *     name="status_id",
     *     required=true,
     *     description="status_id",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="id",
     *     required=true,
     *     description="id",
     *     @OA\Schema (type="integer")
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
     *          description="bad request",
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
    public function statusDelivery(Request $request)
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
            return $validator->errors();
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $request->id)
                ->where('userable_type', 'App\Models\Delivery')->first();
            User::where('id', $user->id)->update([
                'status_id' => $request->status_id
            ]);


            $res->success($user);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
             else {
                 $res->fail('erreur serveur 500');
             }
        }
        return new JsonResponse($res, $res->code);
    }

    public function setAvailability(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin','delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'number', // required and number field validation
            'available' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $delivery = Delivery::where('id', isset($request['id']) ? $request['id'] : Auth::user()->userable_id)->update([
                    'available' => $request->available
                ]);


            $res->success($delivery);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            } else {
               $res->fail('erreur serveur 500');
            }

        }
        return new JsonResponse($res, $res->code);
    }
}
