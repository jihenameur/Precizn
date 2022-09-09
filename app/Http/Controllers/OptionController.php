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
use phpDocumentor\Reflection\Types\Null_;

/**
 * @OA\Tag(
 *     name="Option",
 *     description="Gestion Option ",
 *
 * )
 */
class OptionController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Option $model,  Controller $controller = null)
    {
        $this->model = $model;
    }
/**
     * @OA\Post(
     *      path="/addOption",
     *      operationId="addOption",
     *      tags={"Option"},
     *     security={{"Authorization":{}}},
     *      summary="create option.",
     *    @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema( type="string" ),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *     @OA\Schema( type="string" ),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=true,
     *     description="price",
     *    @OA\Schema(type="integer",
     *       format="bigint(20)"),
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
     *          response=400,
     *          description="bad request",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
     */
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
                'name' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());

            }
            $option = new Option();
            $option->name = $request->name;
            $option->description = $request->description;
            $option->price = $request->price;
            $option->supplier_id  = $request->supplier_id ;
            $option->save();

            $res->success($option);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
     /**
     * @OA\Get(
     *      path="/getProductOptions/{id}/{per_page}",
     *      operationId="getProductOptions",
     *      tags={"Option"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of option of product",
     *      description="Returns all  option of product.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="per_page",
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
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
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
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
        /**
     * @OA\Post(
     *      path="/getsupplieroptions",
     *      operationId="getsupplieroptions",
     *      tags={"Option"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of option of product from the  supplier.",
     *      description="Returns all  option of product from the  supplier.",
     *   @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="supplier_id",
     *    @OA\Schema(type="integer",
     *       format="bigint(20)"),
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
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getsupplierOptions(Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $option = Option::where('supplier_id',$request->supplier_id)
            ->orwhere('supplier_id',null)
            ->get();
            $res->success($option);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Post(
     *      path="/getsupplieroptions/{per_page}",
     *      operationId="getsupplieroptions",
     *      tags={"Option"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of option of product from the  supplier.",
     *      description="Returns all  option of product from the  supplier.",
     *        @OA\Parameter(
     *          name="per_page",
     *          in="path",
     *          required=true,
     *
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="supplier_id",
     *    @OA\Schema(type="integer",
     *       format="bigint(20)"),
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
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getsupplierOptionsPaginate($per_page,Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $option = Option::where('supplier_id',$request->supplier_id)
                ->orwhere('supplier_id',null)
                ->paginate($per_page);
            $res->success($option);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Get(
     *      path="/getOptionByid/{id}",
     *     tags={"Option"},
     *     security={{"Authorization":{}}},
     *      operationId="getOptionByid",
     *      summary="Get option  by option id",
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
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
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
    /**
     * @OA\Put(
     *      path="/updateOption/{id}",
     *      operationId="updateOption",
     *      tags={"Option"},
     *     security={{"Authorization":{}}},
     *      summary="update option.",
     *  @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema( type="string" ),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *     @OA\Schema( type="string" ),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=true,
     *     description="price",
     *    @OA\Schema(type="integer",
     *       format="bigint(20)"),
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
     *          response=400,
     *          description="bad request",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
     */
    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return ($validator->errors());

        }
        $res = new Result();
        try {
            /** @var Option $option */
            $option = Option::find($id);
            $option->name=$request->name;
            $option->description=$request->description;
            $option->price=$request->price;
            $option->save();
            $option->refresh();
            $res->success($option);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @return bool|mixed|void
     */
        /** @OA\Delete(
        *      path="/deleteOption/{id}",
        *      operationId="deleteOption",
        *      tags={"Option"},
        *     security={{"Authorization":{}}},
        *      summary="delete option",
        *      description="delete one option.",
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
            $option->products()->detach();
            $option->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
}
