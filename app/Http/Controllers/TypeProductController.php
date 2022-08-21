<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Category;
use App\Models\TypeProduct;
use App\Models\TypeProduct as ModelsTypeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Tag(
 *     name="TypeProduct",
 *     description="Gestion Type Product ",
 *
 * )
 */
class TypeProductController extends Controller
{
    protected $controller;

    public function __construct(Request $request, ModelsTypeProduct $model,  Controller $controller = null)
    {
        $this->model = $model;
    }
    /**
     * @OA\Post(
     *      path="/addtypeProduct",
     *      operationId="addtypeProduct",
     *      tags={"TypeProduct"},
     *     security={{"Authorization":{}}},
     *      summary="create type product.",
     *     @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="the type product name ",
     *    @OA\Schema( type="string" ),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=false,
     *     description="description",
     *    @OA\Schema( type="string" ),
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="type_served",
     *     required=false,
     *     description="type_served",
     *    @OA\Schema( type="integer",
     *           format="int11"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="order_id",
     *     required=true,
     *     description="order_id",
     *    @OA\Schema( type="integer",
     *           format="int64" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="parent_id",
     *     required=true,
     *     description="parent_id",
     *    @OA\Schema( type="integer",
     *           format="bigint(20)" ),
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
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
     /**
     * @OA\Put(
     *      path="/updateTypeProduct/{id}",
     *      operationId="updateTypeProduct",
     *      tags={"TypeProduct"},
     *     security={{"Authorization":{}}},
     *      summary="update type product.",
     *   @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="the type product name ",
     *    @OA\Schema( type="string" ),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=false,
     *     description="description",
     *    @OA\Schema( type="string" ),
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="type_served",
     *     required=false,
     *     description="type_served",
     *    @OA\Schema( type="integer",
     *           format="int11"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="order_id",
     *     required=true,
     *     description="order_id",
     *    @OA\Schema( type="integer",
     *           format="int64" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="parent_id",
     *     required=true,
     *     description="parent_id",
     *    @OA\Schema( type="integer",
     *           format="bigint(20)" ),
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
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
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
    private function getFilterByKeywordClosure($keyword, $orderBy, $orderByType)
    {
        $TypeProduct = ModelsTypeProduct::where('name', 'like', "%$keyword%")
            ->orderBy($orderBy, $orderByType)
            ->get();
        return $TypeProduct;
    }
    /**
     * @OA\Get(
     *      path="/getAllTypeProduct/{per_page}",
     *      operationId="getAllTypeProduct",
     *      tags={"TypeProduct"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of type product",
     *      description="Returns all type product and associated provinces.",
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
    public function getAllTypeProduct($per_page,Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:firstName,lastName,id' // complete the akak list
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
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $TypeProduct = ModelsTypeProduct::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $TypeProduct = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success($TypeProduct);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
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
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    /** @OA\Delete(
        *      path="/deleteTypeProduct/{id}",
        *      operationId="deleteTypeProduct",
        *      tags={"TypeProduct"},
        *     security={{"Authorization":{}}},
        *      summary="delete type product",
        *      description="delete one type product.",
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
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        $typeproduct = TypeProduct::find($id);
        try {
            $typeproduct->delete();
            $res->success('Deleted');
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
