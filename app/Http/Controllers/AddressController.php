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

class AddressController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Address $model,  Controller $controller = null, LocationController $locationController)
    {
        $this->model = $model;
        $this->locationController = $locationController;
    }

    public function create($id, Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [

                'street' => 'required',
                'user_id' => 'required',
                'postcode' => 'required',
                'city' => 'required',
                'region' => 'required',


            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            } else {
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $request['lat'] = $latlong[0]['lat'];
                    $request['long'] = $latlong[0]['long'];
                } else {
                    throw new Exception( "Err: address not found");
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
            }
            $res->success($addresse);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
    public function show($id)
    {
        $address = Address::find($id);
        return $address;
    }
    public function GetClientAddress($id)
    {
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Client')->first();
            $address = Address::where('user_id', $user->id)->get();
            $res->success($address);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
    public function update(Request $request, $id)
    {
        /** @var Delivery $delivery */
        $res = new Result();
        try {
            $adresse = Address::find($id);

            $validator = Validator::make($request->all(), [
                'street' => 'required',
                'city' => 'required',
                'postcode' => 'required',
                'region' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            } else {
                $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
                if (is_array($latlong) && $latlong[0]['long'] > 0) {
                    $request['lat'] = $latlong[0]['lat'];
                    $request['long'] = $latlong[0]['long'];
                } else {
                    throw new Exception( "Err: address not found");
                }
                $allRequestAttributes = $request->all();
                $adresse->fill($allRequestAttributes);

                $adresse->update();
            }
            $res->success($adresse);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $adresse = Address::find($id);
        $adresse->delete();
        return 'delete';
    }
}
