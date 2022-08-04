<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\BaseModel\Result;
use App\Models\Client;
use App\Models\Command;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class CouponController extends Controller
{
    public function create(Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'code_coupon' => 'required',
                'type' => 'in:amount,percentage',
                'taxe' => 'in:TTC,HT'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());
            }

            $coupon = new Coupon();
            $coupon->code_coupon = $request->code_coupon;
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->title = $request->title;
            $coupon->start_date = $request->start_date;
            $coupon->end_date = $request->end_date;
            $coupon->description = $request->description;
            $coupon->quantity = $request->quantity;
            $coupon->client_quantity = $request->client_quantity;
            $coupon->status = $request->status;

            $coupon->montant_min = $request->montant_min;
            $coupon->currency = $request->currency;
            $coupon->taxe = $request->taxe;
            $coupon->frais_port = $request->frais_port;

            $coupon->client_id  = $request->client_id;
            $coupon->save();
            $res->success($coupon);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getByid($id)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $coupon = Coupon::find($id);
            $res->success($coupon);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getAll($per_page)
    {
        $res = new Result();
        try {
            $coupons = Coupon::paginate($per_page);
            $res->success($coupons);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $coupon = Coupon::find($id);

            $coupon->code_coupon = $request->code_coupon;
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->title = $request->title;
            $coupon->start_date = $request->start_date;
            $coupon->end_date = $request->end_date;
            $coupon->description = $request->description;
            $coupon->quantity = $request->quantity;
            $coupon->client_quantity = $request->client_quantity;
            $coupon->status = $request->status;

            $coupon->montant_min = $request->montant_min;
            $coupon->currency = $request->currency;
            $coupon->taxe = $request->taxe;
            $coupon->frais_port = $request->frais_port;

            $coupon->client_id  = $request->client_id;
            $coupon->update();

            $res->success($coupon);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function delete($id)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $coupon = Coupon::find($id);
            $coupon->delete();

            $res->success($coupon);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
