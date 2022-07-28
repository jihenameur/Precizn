<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\BaseModel\Result;
use App\Models\Command;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function create(Request $request)
    {

        //dd(json_decode($request->typeProduct));
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'code_coupon' => 'required',
                'percentage' => 'required'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());
            }
            $command = Command::find($request->command_id);
            if ($command == null) {
                $res->fail("command not found");
                return new JsonResponse($res, $res->code);
            }
            $coupon = new Coupon();
            $coupon->command_id = $command->id;
            $coupon->percentage = $request->percentage;
            $coupon->code_coupon = $request->code_coupon;
            $coupon->save();
            $command->total_price_coupon = $command->total_price - ($command->total_price * ($coupon->percentage / 100));
            $command->update();
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
            $command = Command::find($request->command_id);

            if ($command == null) {
                $res->fail("command not found");
                return new JsonResponse($res, $res->code);
            }
            $coupon->supplier_id = $command->id;
            $coupon->percentage = $request->percentage;
            $coupon->code_coupon = $request->code_coupon;
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
