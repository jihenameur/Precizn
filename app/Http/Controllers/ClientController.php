<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\Paginate;
use App\Models\Address;
use App\Models\Client;
use App\Models\Command;
use App\Models\Favorit;
use App\Models\Panier;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ClientController extends Controller
{
    protected $controller;
    private ReqHelper $reqHelper;


    public function __construct(
        Request $request,
        Client $model,
        Address $address,
        LocationController $locationController,
        AddressController $addressController,
        VerificationApiController $verificationApiController,

        Result $res,
        ReqHelper $reqHelper


    ) {
        $this->model = $model;
        $this->address = $address;
        $this->locationController = $locationController;
        $this->addressController = $addressController;
        $this->verificationApiController = $verificationApiController;
        $this->res = $res;
        $this->reqHelper = $reqHelper;
    }

    public function index(Request $request)
    {
        $res = new Result();
        //dd(TypeAddress::$typeAddress[1]["id"]);
        try {
            $suppliers = Supplier::all();
            $sizeAllSupp = count($suppliers);
            $client =  Auth::user();
            $addres = Address::where('user_id', $client->id)
                ->where('status', 1)
                ->first();
            if ($addres == null) {
                $res->fail("Address not found");
                return new JsonResponse($res, $res->code);
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

                        $dists = $this->locationController->getdistances($addres, $supps);

                        $k = 0;
                        for ($j = count($distances); $j < $x; $j++) {
                            $distances[$j] =  $dists[$k];
                            $k++;
                        }
                        $x = $x + 25;
                    } else {
                        $supps = [];
                        for ($j = count($distances); $j < $sizeAllSupp; $j++) {
                            array_push($supps, $suppliers[$j]);

                            unset($suppliers[$j]);
                        }

                        $distns = $this->locationController->getdistances($addres, $supps);

                        $k = 0;

                        for ($j = count($distances); $j < $sizeAllSupp; $j++) {

                            $distances[$j] =  $distns[$k];
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
                $dists = $this->locationController->getdistances($addres, $suppl);
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
                'suppliersPopular' =>  $suppliersPopu,
                'favorits' => $favorits,
                'suppliersDistance' =>  $suppliers,
                'suppliersRecommanded' =>  $suppliersRecommanded,
            ];
            $res->success($response);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

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
                throw new Exception("Err: address not found");
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
           // $this->verificationApiController->toOrange($user->id, $request->phone);

            // return $client;
            $clt = [
                'id' => $client['id'],
                'firstname' => $client['firstname'],
                'lastname' => $client['lastname'],
                'image' => $client['image'],
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
           // dd($exception);
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    public function addImage($id, Request $request)
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
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form
            }
            $client = Client::find($id);
            if ($request->file('image')) {
                $file = $request->file('image');
                //$filename = date('YmdHi') . $file->getClientOriginalName();
                $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Clients'), $filename);
                $client['image'] = $filename;
            }
            $client->update();
            $response['client'] = [
                "id"         =>  $client->id,
                "firstname"     =>  $client->firstname,
                "lastname"     =>  $client->lastname,
                "image"     =>  $client->image

            ];

            $res->success($response);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    public function addfavorite(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            //$client = Client::find($request->id_client);
            $user =  Auth::user();
            //dd($user->userable_id);
            $client = Client::find($user->userable_id);
            $supplier = Supplier::find($request->id_supplier);
            $client->favorit()->syncWithoutDetaching($supplier);
            $res->success($client);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function deletefavorite(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user =  Auth::user();
            $client = Client::find($user->userable_id);
            $supplier = Supplier::find($request->id_supplier);
            $client->favorit()->detach($supplier);
            $res->success($client);
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
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            // $supplier = Supplier::all();
            $clients = Client::paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $clients = $this->getFilterByKeywordClosure($keyword);
            }
            $res->success($clients);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function allClient(Request $request)
    {
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            // $supplier = Supplier::all();
            $clients = Client::all();
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $clients = $this->getFilterByKeywordClosure($keyword);
            }
            $res->success($clients);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     *  get client
     *
     * @return Collection|Model[]|mixed|void
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
            $client = Client::find($id);
            $commands = Command::whereHas('client', function ($q) use ($id) {
                $q->where('id', $id);
            })
                ->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
            $user =  Auth::user();
            $client = Client::find($user->userable_id);
            $favorits = Supplier::whereHas('favorit', function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })
                ->get();
            //$dists = $this->locationController->getdistances($adress, $favorits);

            $res->success($favorits);
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
    private function getFilterByKeywordClosure($keyword)
    {

        $client = Client::whereHas('user', function ($q) use ($keyword) {
            $q->where('email', 'like', "%$keyword%");
        })
            ->orWhere('lastname', 'like', "%$keyword%")
            ->orWhere('firstname', 'like', "%$keyword%")

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

            // $validator = Validator::make($request->all(), [
            //     'firstname' => 'required',
            //     'lastname' => 'required',
            //     //'email' => 'required|email|unique:users,email',   // required and email format validation
            //     //'password' => 'required|min:8', // required and number field validation
            //     //'confirm_password' => 'required|same:password',

            // ]); // create the validations
            // if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            // {
            //     throw new Exception($validator->errors());
            // }
            $allRequestAttributes = $request->all();
            $client = Client::find($id);
            $user = $client->user;
            $address = Address::where('user_id', $user->id)
                ->where('status', 1)->first();
            if ($request->street != null && $request->postcode != null && $request->city != null && $request->region) {
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $address['lat']  = $latlong[0]['lat'];
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
                throw new Exception($validator->errors());
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
        /** @var Client $client */
        $res = new Result();
        try {
            $client = Client::find($id);

            $client->user->delete();
            $client->delete();

            $res->success($client);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    // get addresses wehere id client
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    public function statusClient($id, Request $request)
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
            User::where('id', $user->id)->update([
                'status_id' => $request->status_id
            ]);


            $res->success($user);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function resetPWClient(Request $request)
    {

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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);

        //  $date = date_create();
        //  DB::table('users')->where('id', Auth::id())->update(['phone_verified_at' => date_format($date, 'Y-m-d H:i:s')]);

    }
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
            $user =  Auth::user();
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
                //dd($favorit);
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
            $res->fail($exception->getMessage());
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
    public function ClientGetSupplierByCategory($per_page, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','client'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $user =  Auth::user();
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
                //dd($favorit);
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }



    /**
     * deleted client
     */
    public function deleteClient($id)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
