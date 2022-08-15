<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\TypeProduct as ModelsTypeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Auth;

class TypeProductController extends Controller
{
    protected $controller;

    public function __construct(Request $request, ModelsTypeProduct $model,  Controller $controller = null)
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
                'parent_id' => 'required',
                'order_id' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }

                $typeProduct = new ModelsTypeProduct();
                $typeProduct->name = $request->name;
                $typeProduct->parent_id = $request->parent_id;
                $typeProduct->order_id = $request->order_id;
                $typeProduct->description = $request->description;
                $typeProduct->type_served = $request->type_served;

                $typeProduct->save();

                $response['typeProduct'] = [
                    "id"         =>  $typeProduct->id,
                    "name"     =>  $typeProduct->name,
                    "parent_id"     =>  $typeProduct->parent_id,
                    "order_id"     =>  $typeProduct->order_id,
                    "description"     =>  $typeProduct->description,
                    "type_served"     =>  $typeProduct->type_served,

                ];


            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function update($id,Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'parent_id' => 'required',
            'order_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());

        }
        $res = new Result();
        try {

                $typeProduct = ModelsTypeProduct::find($id);
                $typeProduct->name = $request->name;
                $typeProduct->parent_id = $request->parent_id;
                $typeProduct->order_id = $request->order_id;
                $typeProduct->description = $request->description;
                $typeProduct->type_served = $request->type_served;

                $typeProduct->update();

                $response['typeProduct'] = [
                    "id"         =>  $typeProduct->id,
                    "name"     =>  $typeProduct->name,
                    "parent_id"     =>  $typeProduct->parent_id,
                    "order_id"     =>  $typeProduct->order_id,
                    "description"     =>  $typeProduct->description,
                ];


            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
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
    private function getFilterByKeywordClosure($keyword)
    {
        $TypeProduct = ModelsTypeProduct::where('name', 'like', "%$keyword%")
            ->get();
        return $TypeProduct;
    }
    public function getAllTypeProduct($per_page,Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $TypeProduct = ModelsTypeProduct::paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $TypeProduct = $this->getFilterByKeywordClosure($keyword);
            }
            $res->success($TypeProduct);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getTypeProductByid($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'] )){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $TypeProduct = ModelsTypeProduct::find($id);

            $res->success($TypeProduct);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
