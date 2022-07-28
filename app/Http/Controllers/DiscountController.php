<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BaseModel\Result;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class DiscountController extends Controller
{

    public function create(Request $request)
    {

        //dd(json_decode($request->typeProduct));
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required',
                'end_date' => 'required'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());
            }
            $supplier = Supplier::find($request->supplier_id);
            $product = Product::find($request->product_id);
            if ($supplier == null) {
                $res->fail("Supplier not found");
                return new JsonResponse($res, $res->code);

            }
            if ($product == null) {
                $res->fail("Product not found");
                return new JsonResponse($res, $res->code);
            }
            $discount = new Discount();
            $discount->supplier_id = $supplier->id;
            $discount->product_id  = $product->id;
            $discount->percentage = $request->percentage;
            $discount->start_date = $request->start_date;
            $discount->end_date = $request->end_date;
            $discount->save();
            $res->success($discount);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getByid($id)
    {
        $res = new Result();
        try {
            $discount = Discount::find($id);
            $res->success($discount);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getAll($per_page)
    {
        $res = new Result();
        try {
            $discounts = Discount::paginate($per_page);
            $res->success($discounts);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function update($id, Request $request)
    {
        $res = new Result();
        try {
            /** @var Product $product */
            $discount = Discount::find($id);
            $supplier = Supplier::find($request->supplier_id);
            $product = Product::find($request->product_id);
            if ($supplier == null) {
                $res->fail("Supplier not found");
                return new JsonResponse($res, $res->code);

            }
            if ($product == null) {
                $res->fail("Product not found");
                return new JsonResponse($res, $res->code);
            }
            $discount->supplier_id = $supplier->id;
            $discount->product_id  = $product->id;
            $discount->percentage = $request->percentage;
            $discount->start_date = $request->start_date;
            $discount->end_date = $request->end_date;
            $discount->update();

            $res->success($discount);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function delete($id)
    {
        $res = new Result();
        try {
            $discount = Discount::find($id);
            $discount->delete();
            $res->success($discount);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

}
