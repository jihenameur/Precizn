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
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{

    public function create(Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        //dd(json_decode($request->typeProduct));
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required',
                'end_date' => 'required',
                'supplier_id' => 'required',
                'product_id' => 'required',
                'percentage' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());
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
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getByid($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $discount = Discount::find($id);
            $res->success($discount);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getAll($per_page,Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
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
            $discounts = Discount::orderBy($orderBy, $orderByType)->paginate($per_page);
            $res->success($discounts);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'supplier_id' => 'required',
            'product_id' => 'required',
            'percentage' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return ($validator->errors());
        }
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
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
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
            $discount = Discount::find($id);
            $discount->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

}
