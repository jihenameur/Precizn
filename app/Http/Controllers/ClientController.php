<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\Paginate;
use App\Http\Resources\AdsResource;
use App\Models\Address;
use App\Models\Ads;
use App\Models\Category;
use App\Models\CategorySupplier;
use App\Models\Client;
use App\Models\Command;
use App\Models\Favorit;
use App\Models\Panier;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ReqHelper;
use App\Models\Status;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\TypeAddress;
use App\Http\Controllers\Auth\VerificationApiController;
use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * @OA\Tag(
 *     name="Client",
 *     description="Gestion client ",
 *
 * )
 */
class ClientController extends Controller
{
    protected $controller;
    private ReqHelper $reqHelper;


    public function __construct(
        Request                   $request,
        Client                    $model,
        Address                   $address,
        LocationController        $locationController,
        AddressController         $addressController,
        VerificationApiController $verificationApiController,

        Result                    $res,
        ReqHelper                 $reqHelper


    )
    {
        $this->model = $model;
        $this->address = $address;
        $this->locationController = $locationController;
        $this->addressController = $addressController;
        $this->verificationApiController = $verificationApiController;
        $this->res = $res;
        $this->reqHelper = $reqHelper;
    }

    public function init(Request $request)
    {
        $this->validate($request,[
            'delivery' => 'required|in:0,1'
        ]);
        $user = Auth::user();
        $client = Client::find($user->userable_id);
        $favorits = null;
        $suppliers = null;
        $popular = null;
        $today_offre = Supplier::all()->random(5); // to do
        $res = new Result();
        try {

            $address = false;
            if ($request->has('lat') && $request->has('long')) {
                if ($request->long && $request->lat) {
                    $address = (object)[];
                    $address->lat = $request->lat;
                    $address->long = $request->long;
                }
            }else{
                $user_adress = Address::where('user_id', $user->id)
                    ->where('status', 1)
                    ->first();
                if($user_adress){

                    $address = (object)[];
                    $address->lat = $user_adress->lat;
                    $address->long = $user_adress->long;
                }
            }

            if ($address) {
                $base_suppliers = new Collection();
                Supplier::chunk(25, function ($tmp_suppliers) use ($address, &$base_suppliers){
                    $tmps = $this->locationController->getdistances($address, $tmp_suppliers);
                    foreach ($tmps as $tmp){
                        $base_suppliers->push($tmp);
                    }
                 });
               $sorted_suppliers = $base_suppliers->sortBy('distance');
               $suppliers = $sorted_suppliers;
               $favorits = $client->favorit()->whereIn('suppliers.id',$suppliers->pluck('id'))->get();
            } else {
                $suppliers = Supplier::all(); //where('delivery', $request->delivery)->get();
                $favorits = $client->favorit;
            }

            $categories = Category::whereIn('id',
                CategorySupplier::whereIn('supplier_id',Supplier::all()->pluck('id')->toArray())->pluck('category_id')->toArray()
            )->get();
            $popular = $suppliers->sortByDesc('star');

            $res->success([
                "categories" => $categories,
                "recommended" => $suppliers,
                "popular" => $popular,
                'today_offers' => $today_offre,
                'favorites' => $favorits,
                'ads' => [
                    'HOME_1' => AdsResource::collection(Ads::where('adsarea_id',1)->get()),
                    'HOME_2' => AdsResource::collection(Ads::where('adsarea_id',2)->get()),
                    'HOME_3' => AdsResource::collection(Ads::where('adsarea_id',3)->get()),
                ]
            ]);

        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function index(Request $request)
    {
        $res = new Result();
        try {
            $suppliers = Supplier::all();
            $sizeAllSupp = count($suppliers);
            $client = Auth::user();
            $address = Address::where('user_id', $client->id)
                ->where('status', 1)
                ->first();
            if ($address == null) {
                if ($request->has('lat') && $request->lat != null &&
                    $request->has('long') && $request->long != null
                ) {
                    $address = (object)[];
                    $address->lat = $request->lat;
                    $address->long = $request->long;
                } else {
                    $res->fail("Address not found");
                    return new JsonResponse($res, $res->code);
                }
            }
            $supps = [];
            $distances = [];
            if (is_float(count($suppliers) / 25) && count($suppliers) > 25) {
                $size = (int)(count($suppliers) / 25) + 1;
            }
            $x = 25;
            if (count($suppliers) > 25) {

                for ($i = 0; $i < $size; $i++) {

                    if (count($suppliers) > 25) {
                        $supps = [];
                        for ($j = count($supps); $j < $x; $j++) {
                            array_push($supps, $suppliers[$j]);
                            unset($suppliers[$j]);
                        }

                        $dists = $this->locationController->getdistances($address, $supps);

                        $k = 0;
                        for ($j = count($distances); $j < $x; $j++) {
                            $distances[$j] = $dists[$k];
                            $k++;
                        }
                        $x = $x + 25;
                    } else {
                        $supps = [];
                        for ($j = count($distances); $j < $sizeAllSupp; $j++) {
                            array_push($supps, $suppliers[$j]);

                            unset($suppliers[$j]);
                        }

                        $distns = $this->locationController->getdistances($address, $supps);

                        $k = 0;

                        for ($j = count($distances); $j < $sizeAllSupp; $j++) {

                            $distances[$j] = $distns[$k];
                            $k++;
                        }
                    }
                }
            }
            $suppliers = [];
            foreach ($distances as $key => $value) {
                if ($value["distance"] <= 2)
                    array_push($suppliers, $value);
            }
            $clt = Client::find($client->userable_id);
            $favorits = $clt['favorit'];
            // $array = Supplier::all()->sortByDesc("qantityVente")->take(10);;
            //$distancesPopu = $this->locationController->getdistances($client, $array);
            $array = collect($distances)->sortByDesc('User.qantityVente')->toArray();
            $suppliersPopu = [];
            foreach ($array as $key => $value) {
                if ($value["distance"] <= 2)
                    array_push($suppliersPopu, $value);
            }
            $commands = Command::where('client_id', $client->userable_id)->get();

            $products = [];
            foreach ($commands as $key => $value) {
                $panier = Panier::where('id', $value['panier']['id'])
                    ->first();
                array_push($products, $panier['products']);
            }
            $array = collect($products)->toArray();
            $pr = [];
            foreach ($array as $key => $val) {
                foreach ($val as $key => $value) {
                    array_push($pr, $value);
                }
            }

            $dupes = []; // keep track of duplicates
            foreach ($pr as $pr1) { // iterate over all items
                $dupeCount = 0; // because we iterate over the same array, we always find at least the item itself (1 dupe minimum)

                foreach ($pr as $pr2) { // check the array again

                    if ($pr1['id'] === $pr2['id']) {
                        $dupeCount++;
                    }

                    if ($dupeCount > 1) { // because we always find at least 1, push only when we find more than that
                        array_push($dupes, $pr1); // add it to the result
                    }
                }
            }
            $array = array_unique(array_column($dupes, 'name'), SORT_REGULAR);
            $suppliersRecommanded = [];

            foreach ($array as $product) { // iterate over all items

                $suppl = Supplier::whereHas('products', function ($q) use ($product) {
                    $q->where('name', 'like', "%$product%");
                })
                    ->get();
                $dists = $this->locationController->getdistances($address, $suppl);
                foreach ($dists as $dist) {
                    if ($dist['distance'] <= 2) {
                        array_push($suppliersRecommanded, $dist); // add it to the result
                    }
                }
            }
            $suppliersRecommanded = array_unique($suppliersRecommanded, SORT_REGULAR);
            if (count($suppliersPopu) > 5) {
                $suppliersPopu = array_slice($suppliersPopu, 0, 5);
            }
            if (count($favorits) > 5) {
                $favorits = array_slice($favorits, 0, 5);
            }
            if (count($suppliers) > 5) {
                $suppliers = array_slice($suppliers, 0, 5);
            }
            if (count($suppliersRecommanded) > 5) {
                $suppliersRecommanded = array_slice($suppliersRecommanded, 0, 5);
            }
            // return ["suppliersPopular" => $suppliersPopu, "favorits" => $favorits, "suppliersDistance" => $suppliers, "suppliersRecommanded" => $suppliersRecommanded];
            $response = [
                'suppliersPopular' => $suppliersPopu,
                'favorits' => $favorits,
                'suppliersDistance' => $suppliers,
                'suppliersRecommanded' => $suppliersRecommanded,
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
     *      path="/addClient",
     *      operationId="addClient",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="create client" ,
     *      description="create client",
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
     * *    @OA\Parameter (
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
     *     @OA\Parameter (
     *     in="query",
     *     name="confirm_password",
     *     required=true,
     *     description="confirm_password",
     *     @OA\Schema (type="string")
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
     *  @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
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
        $typeAddress = [
            new TypeAddress(1, 'Domicile'),
            new TypeAddress(2, 'Travail'),
            new TypeAddress(3, 'Autre')
        ];
        $res = new Result();
        //dd(TypeAddress::$typeAddress[1]["id"]);
        try {
            $validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',
                'phone' => 'required',
                'street' => 'required', 'postcode' => 'required', 'city' => 'required', 'region' => 'required',
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return ($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form
            }
            //else {

            $allRequestAttributes = $request->all();
            $role_id = Role::where('short_name', config('roles.backadmin.client'))->first()->id;
            $user = new User($allRequestAttributes);
            //$user->password = bcrypt($request->password);
            $user->password = bcrypt($request->password);


            /** @var Client $client */
            // $user =   Auth::user();
            $addresse = new Address();
            // $addresse->save();
            $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
            if (is_array($latlong) && $latlong[0]['long'] > 0) {
                $addresse->lat = $latlong[0]['lat'];
                $addresse->long = $latlong[0]['long'];
            } else {
                return ("Err: address not found");
            }
            $chekphoneExist = $this->verificationApiController->checkPhoneExists($request->phone);
            if ($chekphoneExist == "phone exists") {
                $res->fail("phone exists");
                return new JsonResponse($res, $res->code);
            }

            $client = $this->model->create($allRequestAttributes);
            // $user->sendApiEmailVerificationNotification();
            $client = $this->model->find($client->id);
            $client->user()->save($user);
            $addresse->street = $request->street;
            $addresse->postcode = $request->postcode;
            $addresse->city = $request->city;
            $addresse->region = $request->region;
            $addresse->status = 1;
            $addresse->label = "Principale";
            $addresse->type = $typeAddress[0]->id;
            $addresse->user_id = $user->id;
            $role = Role::find($role_id);
            $user->roles()->attach($role);
            $addresse->save();
            //$statut_user = Status::find(4);
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            $user->token = $token;
            $user->tel = $request->phone;
            $user->status_id = 4;
            $user->update();
            //$this->verificationApiController->toOrange($user->id, $request->phone);

            // return $client;
            $clt = [
                'id' => $client['id'],
                'firstname' => $client['firstname'],
                'lastname' => $client['lastname'],
                'email' => $user['email'],
                'gender' => $client['gender'],
                'tel' => $request->phone,
                'role' => $role['id'],
                'status' => $user['status_id'],
                'street' => $addresse['street'],
                'postcode' => $addresse['postcode'],
                'city' => $addresse['city'],
                'region' => $addresse['region'],
            ];
            $response = [
                //'token' => $token,
                // 'refresh_token' => $refresh_token,
                // 'token_type' => 'bearer',
                // 'expires_in' => auth()->factory()->getTTL() * 60,
                'client' => $clt
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
     *      path="/addImage",
     *      operationId="addImage",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="create image client" ,
     *      description="create image client",
     *   @OA\Parameter (
     *     in="query",
     *     name="image",
     *     required=true,
     *     description="image client",
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
     *  )
     */
    public function addImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
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
                return ($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form
            }
            $client = Client::find(Auth::user()->userable_id);
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Clients'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Clients/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $client->file_id = $file->id;
            $client->update();
            $response['client'] = [
                "id" => $client->id,
                "firstname" => $client->firstname,
                "lastname" => $client->lastname,
                "image" => $file->path

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
     *      path="/updateimage",
     *      operationId="updateimage",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="update image client" ,
     *      description="update image client",
     *     @OA\Parameter (
     *     in="query",
     *     name="image",
     *     required=true,
     *     description="image",
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
     *     )
     */
    public function updateImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
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
                return ($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form
            }
            $client = Client::find(Auth::user()->userable_id);
            if ($request->file('image')) {
                $image = File::find($client->file_id);
                unlink('public/Clients/' . $image->name);
                $image->delete();
                $file = $request->file('image');
                $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Clients'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Clients/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $client->file_id = $file->id;
            $client->update();
            $response['client'] = [
                "id" => $client->id,
                "firstname" => $client->firstname,
                "lastname" => $client->lastname,
                "image" => $file->path

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
     *      path="/addfavorite",
     *      operationId="addfavorite",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="add favorite to supplier" ,
     *      description="add favorite to supplier",
     *     @OA\Parameter (
     *     in="query",
     *     name="id_supplier",
     *     required=true,
     *     description="id_supplier",
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
     *     )
     */
    public function addfavorite(Request $request)
    {

        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'id_supplier' => 'required',
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            // return $validator->errors();
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            //$client = Client::find($request->id_client);
            $user = Auth::user();
            //dd($user->userable_id);
            $client = Client::find($user->userable_id);
            $supplier = Supplier::find($request->id_supplier);
            $client->favorit()->syncWithoutDetaching($supplier);
            $res->success($client);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Delete(
     *      path="/deletefavorite",
     *      operationId="deletefavorite",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="delete favorite to supplier" ,
     *      description="delete favorite to supplier",
     *     @OA\Parameter (
     *     in="query",
     *     name="id_supplier",
     *     required=true,
     *     description="id_supplier",
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
     *     )
     */
    public function deletefavorite(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'id_supplier' => 'required',
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            // return $validator->errors();
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $user = Auth::user();
            $client = Client::find($user->userable_id);
            $supplier = Supplier::find($request->id_supplier);
            $client->favorit()->detach($supplier);
            $res->success($client);
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
     *      path="/getAllClient/{per_page}",
     *      operationId="getAllClient",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of clients",
     *      description="Returns all clients and associated provinces.",
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
                'orderBy' => 'required|in:firstname,lastname,created_at' // complete the akak list
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
            $clients = Client::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $clients = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success($clients);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/getlistclients/{per_page}",
     *      operationId="getlistclients",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of clients",
     *      description="Returns all clients and associated provinces.",
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
    public function allClient(Request $request)
    {
        $res = new Result();
        try {
            $orderBy = 'firstname';
            $orderByType = "ASC";
            if ($request->has('orderBy') && $request->orderBy != null) {
                $this->validate($request, [
                    'orderBy' => 'required|in:firstname,lastname,created_at' // complete the akak list
                ]);
                $orderBy = $request->orderBy;
            }
            if ($request->has('orderByType') && $request->orderByType != null) {
                $this->validate($request, [
                    'orderByType' => 'required|in:ASC,DESC' // complete the akak list
                ]);
                $orderByType = $request->orderByType;
            }
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $clients = Client::orderBy($orderBy, $orderByType)->get();
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $clients = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success($clients);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     *  get client
     *
     * @return Collection|Model[]|mixed|void
     */
    /**
     * @OA\Get(
     *      path="/getClientCommands/{id}/{per_page}",
     *      operationId="getClientCommands",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="Get customers where has order.",
     *      description="Returns all customers where has order.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
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
    public function getClientCommands($id, $per_page)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $commands = Command::whereHas('client', function ($q) use ($id) {
                $q->where('id', $id);
            })
                ->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/getClient/{id}",
     *     tags={"Client"},
     *     security={{"Authorization":{}}},
     *      operationId="getClient",
     *      summary="Get client by client id",
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
    public function getClient($id)
    {
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Client')->first();
            $address = Address::where('user_id', $user->id)
                ->where('status', 1)->first();
            $role = Role::whereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();
            $client = Client::find($id);
            $file = File::find($client->file_id);
            $clt['client'] = [
                'id' => $client['id'],
                'firstname' => $client['firstname'],
                'lastname' => $client['lastname'],
                'image' => $file['path'] ?? '',
                'email' => $user['email'],
                'gender' => $client['gender'],
                'tel' => $user['tel'],
                'role' => $role['id'],
                'street' => $address['street'],
                'postcode' => $address['postcode'],
                'city' => $address['city'],
                'region' => $address['region'],
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

    /**
     * @OA\Get(
     *      path="/getClientFavorits",
     *     tags={"Client"},
     *     security={{"Authorization":{}}},
     *      operationId="getClientFavorits",
     *      summary="Get favorits  ",
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
    public function getClientFavorits()
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = Auth::user();
            $client = Client::find($user->userable_id);
            $favorits = Supplier::whereHas('favorit', function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })
                ->get();
            //$dists = $this->locationController->getdistances($adress, $favorits);

            $res->success($favorits);
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

        $client = Client::whereHas('user', function ($q) use ($keyword) {
            $q->where('email', 'like', "%$keyword%");
        })
            ->orWhere('lastname', 'like', "%$keyword%")
            ->orWhere('firstname', 'like', "%$keyword%")
            ->orderBy($orderBy, $orderByType)
            ->get();

        return $client;
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Client|mixed|void
     */
    /**
     * @OA\Put(
     *      path="/updateClient/{id}",
     *      operationId="updateClient",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="update client",
     *      description="update client",
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
     * @OA\Parameter (
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
     *  @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
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
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        /** @var Client $client */
        $res = new Result();
        try {

            $validator = Validator::make($request->all(), [
                'firstname' => 'required',
                'lastname' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());
            }
            $allRequestAttributes = $request->all();
            $client = Client::find($id);
            $user = $client->user;
            $address = Address::where('user_id', $user->id)
                ->where('status', 1)->first();
            if ($request->street != null && $request->postcode != null && $request->city != null && $request->region) {
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $address['lat'] = $latlong[0]['lat'];
                    $address['long'] = $latlong[0]['long'];
                } else {
                    throw new Exception("Err: address not found");
                }
            }
            //dd($request);
            $user->fill($allRequestAttributes);
            //$user->password = bcrypt($request->password);
            $client->fill($allRequestAttributes);
            $address->fill($allRequestAttributes);
            $user->update();
            $client->update();
            $address->update();

            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Client')->first();

            $role = Role::whereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();
            $clt['client'] = [
                'id' => $client['id'],
                'firstname' => $client['firstname'],
                'lastname' => $client['lastname'],
                'image' => $client['image'],
                'email' => $user['email'],
                'gender' => $client['gender'],
                'tel' => $user['tel'],
                'role' => $role['id'],
                'street' => $address['street'],
                'postcode' => $address['postcode'],
                'city' => $address['city'],
                'region' => $address['region'],
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

    /**
     * @OA\Put(
     *      path="/updateClienPW/{id}",
     *      operationId="updateClienPW",
     *      tags={"Administrateur"},
     *     security={{"Authorization":{}}},
     *      summary="update password client",
     *      description="update password client",
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
    public function updateClienPW($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
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
            $client = Client::find($id);
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Client')->first();
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
            $address = Address::where('user_id', $user->id)
                ->where('status', 1)->first();
            $clt['client'] = [
                'id' => $client['id'],
                'firstname' => $client['firstname'],
                'lastname' => $client['lastname'],
                'image' => $client['image'],
                'email' => $user['email'],
                'gender' => $client['gender'],
                'tel' => $user['tel'],
                'role' => $role['id'],
                'street' => $address['street'],
                'postcode' => $address['postcode'],
                'city' => $address['city'],
                'region' => $address['region'],
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
    /**
     * @inheritDoc
     *
     * @param null $id
     * @return bool|mixed|void
     */
    /**
     * @OA\Delete(
     *      path="/destroyClient/{id}",
     *      operationId="destroyClient",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="delete client",
     *      description="delete one client.",
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
        /** @var Client $client */
        $res = new Result();
        try {
            $client = Client::find($id);

            $client->user->delete();
            $client->delete();

            $res->success($client);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function getAddressesClient($id, $per_page)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Client')->first();
            $adresses = Address::where('user_id ', $user->id)->paginate($per_page);
            $res->success($adresses);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/statusClient",
     *     tags={"Client"},
     *     security={{"Authorization":{}}},
     *      operationId="statusClient",
     *      summary="Get client by client id && status",
     *     @OA\Parameter (
     *     in="query",
     *     name="id",
     *     required=true,
     *     description="id_client",
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
    public function statusClient(Request $request)
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
                ->where('userable_type', 'App\Models\Client')->first();
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
     * @OA\Get(
     *      path="/resetPWClient",
     *     tags={"Client"},
     *     security={{"Authorization":{}}},
     *      operationId="resetPWClient",
     *      summary="reset password",
     *     @OA\Parameter (
     *     in="query",
     *     name="email",
     *     required=true,
     *     description="email",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="tel",
     *     required=true,
     *     description="tel",
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
     *   * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     * )
     */
    public function resetPWClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',   // required and email format validation
            'tel' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $user = User::where('email', $request->email)
                ->where('userable_type', 'App\Models\Client')->first();
            $this->verificationApiController->toOrange($user->id, $user->tel);
            $token = Str::random(64);

            DB::table('password_resets')->insert([
                'email' => $request->email,
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
            $user = User::where('email', $request->email)->first();
            if ($request['code'] == $user->smscode) {
                $user->update(['password' => bcrypt($request->password)]);

                DB::table('password_resets')->where(['email' => $request->email])->delete();
                $client = Client::find($user->userable_id);
                $address = Address::where('user_id', $user->id)
                    ->where('status', 1)->first();
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
                    'status' => $user['status_id'],
                    'tel' => $user['tel'],
                    'role' => $role['id'],
                    'street' => $address['street'],
                    'postcode' => $address['postcode'],
                    'city' => $address['city'],
                    'region' => $address['region'],
                ];
                $response = [
                    'token' => $user['token'],
                    // 'token_type' => 'bearer',
                    // 'expires_in' => auth()->factory()->getTTL() * 60,
                    'client' => $clt
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
     *      path="/ClientGetSupplier/{per_page}",
     *      operationId="ClientGetSupplier",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="Get customers where has favorite supplier.",
     *      description="Returns all customers where has favorite supplier.",
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
    public function ClientGetSupplier($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = Auth::user();
            $client = Client::find($user->userable_id);
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $suppliers = Supplier::all();
            if ($keyword != null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $suppliers = $this->getFilterByKeywordClosureSupplier($keyword);
            }
            $i = 0;
            foreach ($suppliers as $key => $supplier) {
                $favorit = Client::whereHas('favorit', function ($q) use ($client, $supplier) {
                    $q->where('client_id', $client->id);
                    $q->where('supplier_id', $supplier->id);
                })->get();
                if ($favorit->isEmpty()) {
                    $product = Product::whereHas('suppliers', function ($q) use ($supplier) {
                        $q->where('supplier_id', $supplier->id);
                    })->get();
                    $supp[$i] = ['supplier' => $supplier, 'product' => $product, 'favorit' => false];
                    $i++;
                } else {
                    $product = Product::whereHas('suppliers', function ($q) use ($supplier) {
                        $q->where('supplier_id', $supplier->id);
                    })->get();
                    $supp[$i] = ['supplier' => $supplier, 'product' => $product, 'favorit' => true];
                    $i++;
                }
            }
            $paginate = new Paginate();
            $res->success($paginate->paginate($supp, $per_page));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    private function getFilterByKeywordClosureSupplier($keyword)
    {

        $supp = Supplier::whereHas('user', function ($q) use ($keyword) {
            $q->where('email', 'like', "%$keyword%");
        })
            ->orWhere('name', 'like', "%$keyword%")
            ->orWhere('lastname', 'like', "%$keyword%")
            ->orWhere('firstname', 'like', "%$keyword%")
            ->get();

        return $supp;
    }

    /**
     * @OA\Get(
     *      path="/ClientGetSupplierByCategory/{per_page}",
     *     tags={"Client"},
     *     security={{"Authorization":{}}},
     *      operationId="ClientGetSupplierByCategory",
     *      summary="Get supplier by category id ",
     *     @OA\Parameter(
     *          name="per_page",
     *          in="path",
     *          required=true,
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="category_id",
     *     required=true,
     *     description="category_id",
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
    public function ClientGetSupplierByCategory($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            // return $validator->errors();
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $user = Auth::user();
            $client = Client::find($user->userable_id);
            $suppliers = Supplier::whereHas('categorys', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            })->get();
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $i = 0;
            $supp = [];

            foreach ($suppliers as $key => $supplier) {
                $favorit = Client::whereHas('favorit', function ($q) use ($client, $supplier) {
                    $q->where('client_id', $client->id);
                    $q->where('supplier_id', $supplier->id);
                })->get();
                if ($favorit->isEmpty()) {
                    $product = Product::whereHas('suppliers', function ($q) use ($supplier) {
                        $q->where('supplier_id', $supplier->id);
                    })->get();
                    $supp[$i] = ['supplier' => $supplier, 'product' => $product, 'favorit' => false];
                    $i++;
                } else {
                    $product = Product::whereHas('suppliers', function ($q) use ($supplier) {
                        $q->where('supplier_id', $supplier->id);
                    })->get();
                    $supp[$i] = ['supplier' => $supplier, 'product' => $product, 'favorit' => true];
                    $i++;
                }
            }
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword));
            }
            $paginate = new Paginate();

            $res->success($paginate->paginate($supp, $per_page));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }


    /**
     * @OA\Delete(
     *      path="/deleteClient/{id}",
     *      operationId="deleteClient",
     *      tags={"Client"},
     *     security={{"Authorization":{}}},
     *      summary="delete client",
     *      description="delete one client.",
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
    public function deleteClient($id)
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
                ->where('userable_type', 'App\Models\Client')->first();
            $client = Client::find($user->userable_id);
            $user->delete();

            $client->delete();

            $res->success($user);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
