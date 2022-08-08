<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Option;
use App\Models\Product;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OptionController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Option $model,  Controller $controller = null)
    {
        $this->model = $model;
    }

    public function create(Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                // 'price' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());

            }

            $option = new Option();
            $option->name = $request->name;
            $option->description = $request->description;
            $option->price = $request->price;
            $option->default = $request->default;

            $option->save();

            $res->success($option);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getProductOptions($id,$per_page)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier','client'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $option = Option::whereHas('products', function ($q) use ($id) {
                $q->where('product_id', $id);
            })->paginate($per_page);
            $res->success($option);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getOptionByid($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $option = Option::find($id);
            $res->success($option);
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
     * @return Option|mixed|void
     */
    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            /** @var Option $option */
            $option = Option::find($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }

            $option->fill($request->all());
            $option->update();

           // return $option;
            $res->success($option);
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
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            /** @var Option $option */
            $option = Option::find($id);
            $option->delete();
            $res->success($option);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
