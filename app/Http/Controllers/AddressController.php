<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Address;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Tag(
 *     name="Address",
 *     description="Gestion address",
 *
 * )
 */
class AddressController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Address $model,  Controller $controller = null, LocationController $locationController)
    {
        $this->model = $model;
        $this->locationController = $locationController;
    }

    /**
     * @OA\Post(
     *      path="/createAdresse/{id}",
     *      operationId="createAdresse",
     *      tags={"Address"},
     *     security={{"Authorization":{}}},
     *      summary="create addresse" ,
     *      description="create addresse",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,    
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="postcode",
     *     required=true,
     *     description="postcode",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="street",
     *     required=true,
     *     description="rue",
     *     @OA\Schema (type="string")
     *      ),
     *      @OA\Parameter (
     *     in="query",
     *     name="region",
     *     required=true,
     *     description="region",
     *     @OA\Schema (type="string")
     *      ),
     * 
     *   @OA\Parameter (
     *     in="query",
     *     name="label",
     *     required=true,
     *     description="label",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="type",
     *     required=true,
     *     description="type",
     *     @OA\Schema (type="integer",
     *           format="int(11)")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="user_id",
     *     required=true,
     *     description="user_id",
     *     @OA\Schema ( type="integer",
     *           format="bigint(20)" )
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
    public function create($id, Request $request)
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

                'street' => 'required',
                'user_id' => 'required',
                'postcode' => 'required',
                'city' => 'required',
                'region' => 'required',
                'user_id' => 'required',
                'label' => 'required',
                'type' => 'required',


            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $request['lat'] = $latlong[0]['lat'];
                    $request['long'] = $latlong[0]['long'];
                } else {
                    throw new Exception("Err: address not found");
                }
                $user = User::where('userable_id', $id)
                    ->where('userable_type', 'App\Models\Client')->first();

                //$addresse = $this->model->create($request->all());
                $addresse = new Address();
                $addresse->street = $request->street;
                $addresse->postcode = $request->postcode;
                $addresse->city = $request->city;
                $addresse->region = $request->region;
                $addresse->status = 0;
                $addresse->label = $request->label;
                $addresse->type = $request->type;
                $addresse->user_id = $user->id;
                $addresse->lat = $request['lat'];
                $addresse->long = $request['long'];
                $addresse->save();

        $res->success($addresse);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/showAddresses",
     *      operationId="showAddresses",
     *      tags={"Address"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of Adresses",
     *      description="Returns all Adresses and associated provinces.",
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
     *  @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
    public function getAll()
    {
        $addresses = Address::all();
        return $addresses;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
      /**
     * @OA\Get(
     *      path="/show/{id}",
     *     tags={"Address"},
     *     security={{"Authorization":{}}},
     *      operationId="show",
     *      summary="Get address by address id",
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
    public function show($id)
    {
        $address = Address::find($id);
        return $address;
    }
     /**
     * @OA\Get(
     *      path="/GetClientAddress/{id}",
     *     tags={"Address"},
     *     security={{"Authorization":{}}},
     *      operationId="GetClientAddress",
     *      summary="Get address by client id",
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
    public function GetClientAddress($id)
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
            $address = Address::where('user_id', $user->id)->get();
            $res->success($address);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *      path="/updateAdresse/{id}",
     *      operationId="updateAdresse",
     *      tags={"Address"},
     *     security={{"Authorization":{}}},
     *      summary="update addresse" ,
     *      description="update addresse",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,    
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="postcode",
     *     required=true,
     *     description="postcode",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="city",
     *     required=true,
     *     description="city",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="street",
     *     required=true,
     *     description="rue",
     *     @OA\Schema (type="string")
     *      ),
     *      @OA\Parameter (
     *     in="query",
     *     name="region",
     *     required=true,
     *     description="region",
     *     @OA\Schema (type="string")
     *      ),
     * 
     *   @OA\Parameter (
     *     in="query",
     *     name="label",
     *     required=true,
     *     description="label",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="type",
     *     required=true,
     *     description="type",
     *     @OA\Schema (type="integer",
     *           format="int(11)")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="user_id",
     *     required=true,
     *     description="user_id",
     *     @OA\Schema ( type="integer",
     *           format="bigint(20)" )
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $adresse = Address::find($id);

            $validator = Validator::make($request->all(), [
                'street' => 'required',
                'user_id' => 'required',
                'postcode' => 'required',
                'city' => 'required',
                'region' => 'required',
                'user_id' => 'required',
                'label' => 'required',
                'type' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $request['lat'] = $latlong[0]['lat'];
                    $request['long'] = $latlong[0]['long'];
                } else {
                    throw new Exception("Err: address not found");
                }
                $allRequestAttributes = $request->all();
                $adresse->fill($allRequestAttributes);

                $adresse->update();

            $res->success($adresse);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
      /**
     * @OA\Delete(
     *      path="/deleteAddresse/{id}",
     *      operationId="deleteAddresse",
     *      tags={"Address"},
     *     security={{"Authorization":{}}},
     *      summary="delete Address",
     *      description="delete one Address.",
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
    public function destroy($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $adresse = Address::find($id);
        $adresse->delete();
        return 'delete';
    }
}
