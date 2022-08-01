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

class CouponController extends Controller
{
    public function create(Request $request)
    {

        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'code_coupon' => 'required'
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
