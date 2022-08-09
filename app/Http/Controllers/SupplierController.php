<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\ReqHelper;
use App\Jobs\SendNewSuuplierNotification;
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
use App\Models\File;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    protected $controller;

    public function __construct(
        Request $request,
        Supplier $model,
        LocationController $locationController,
        Result $res,
        ReqHelper $reqHelper,
        VerificationApiController $verificationApiController


    ) {
        $this->model = $model;
        $this->locationController = $locationController;
        $this->res = $res;
        $this->reqHelper = $reqHelper;
        $this->verificationApiController = $verificationApiController;
    }

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
                throw new Exception($validator->errors());
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
                "id"         =>  $supplier->id,
                "name"      =>  $supplier->name,
                "firstName"     =>  $supplier->firstName,
                "lastName"     =>  $supplier->lastName

            ];
            $res->success($response);
            SendNewSuuplierNotification::dispatch($supplier);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
                throw new Exception($validator->errors());
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
                "id"         =>  $supplier->id,
                "name"     =>  $supplier->name,
                "firstname"     =>  $supplier->firstName,
                "lastname"     =>  $supplier->firstName,
                "image"     =>  $file->path,
                "type" => $file->supplier[0]->pivot->type

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
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {

            $orderBy = 'name';
            $orderByType = "ASC";
            if($request->has('orderBy') && $request->orderBy != null){
                $this->validate($request,[
                    'orderBy' => 'required|in:firstName,lastName,' // complete the akak list
                ]);
                $orderBy = $request->orderBy;
            }
            if($request->has('orderByType') && $request->orderByType != null){
                $this->validate($request,[
                    'orderByType' => 'required|in:ASC,DESC' // complete the akak list
                ]);
                $orderByType = $request->orderByType;
            }
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $suppliers = Supplier::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType));
            }
            $res->success([
                'suppliers' => $suppliers,
            ]);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Filter or get By Id
     *
     * @return Collection|Model[]|mixed|void
     */
    public function getById($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
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
            $res->fail($exception->getMessage());
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

            $suppliers =  Supplier::where('name', 'like', "%$keyword%")
                ->orderBy($orderBy, $orderByType)

                ->get();

            $res->success([
                'suppliers' => SupplierResource::collection($suppliers),
            ]);
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
                "id"         =>  $supplier->id,
                "name"      =>  $supplier->name,
                "firstName"     =>  $supplier->firstName,
                "lastName"     =>  $supplier->lastName

            ];

            $res->success($response);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
            $res->fail($exception->getMessage());
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
            $res->fail($exception->getMessage());
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
                throw new Exception($validator->errors());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);

        //  $date = date_create();
        //  DB::table('users')->where('id', Auth::id())->update(['phone_verified_at' => date_format($date, 'Y-m-d H:i:s')]);

    }
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * deleted supplier
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
            $supplier = Supplier::find($user->userable_id);
            $user->delete();
            $products = Product::whereHas('suppliers', function ($q) use ($user) {
                $q->where('supplier_id', $user->userable_id);
            })->get();
            foreach ($products as $product) {
                $product->suppliers()->detach();
                $product->delete();
            }
            $supplier->delete();

            $res->success($user);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
