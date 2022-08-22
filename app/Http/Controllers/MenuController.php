<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Http\Resources\MenuResource;
use App\Http\Resources\ProductResource;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
/**
 * @OA\Tag(
 *     name="Menu",
 *     description="Gestion Menu ",
 *
 * )
 */
class MenuController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Menu $model,  Controller $controller = null)
    {
        $this->model = $model;
    }
/**
     * @OA\Post(
     *      path="/addMenu",
     *      operationId="addMenu",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="create option.",
     *    @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema( type="string" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=false,
     *     description="description",
     *     @OA\Schema( type="string" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="image",
     *     required=true,
     *     description="image menu",
     *      @OA\Schema(type="array", @OA\Items(type="file")),
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
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());

            }
            $menu = new Menu();
            $menu->name = $request->name;
            $menu->description = $request->description;
            $menu->supplier_id  = $request->supplier_id ;
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Menu'), $filename);
                $file->path = asset('public/Menu/' . $filename);
                $menu['image'] =  $file->path;
            }

            $menu->save();

            $res->success(new MenuResource($menu));
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Get(
     *      path="/getMenuProducts/{id}/{per_page}",
     *      operationId="getMenuProducts",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of products  of menu where product id",
     *      description="Returns all  produnct of menu where product id.",
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
    public function getMenuProducts($id, $per_page)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $products = Product::whereHas('menu', function ($q) use ($id) {
                $q->where('menu_id', $id);
            })->paginate($per_page);
            $res->success(ProductResource::collection($products));
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getMenuByid($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $menu = Menu::find($id);
            $res->success(new MenuResource($menu));
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
     /**
     * @OA\Get(
     *      path="/getmenuBysupplierid",
     *      operationId="getmenuBysupplierid",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Get Menu of supplier",
     *      description="Returns menu of supplier.",
     *  @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=false,
     *     description="supplier_id",
     *     @OA\Schema (type="integer",
     *           format="bigint(20)")
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
    public function getMenuBySupplierId(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return ($validator->errors());
        }
        $res = new Result();
        try {
            $menu = Menu::where('supplier_id',$request->supplier_id)->get();
            $res->success(MenuResource::collection($menu));
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Put(
     *      path="/updateMenu/{id}",
     *      operationId="updateMenu",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="update option.",
     *   @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=false,
     *     description="supplier_id",
     *     @OA\Schema (type="integer",
     *           format="bigint(20)")
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
     *     required=false,
     *     description="description",
     *     @OA\Schema( type="string" ),
     *      ),
     *
     *   @OA\Parameter (
     *     in="query",
     *     name="image",
     *     required=true,
     *     description="image menu",
     *      @OA\Schema(type="array", @OA\Items(type="file")),
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
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $menu = Menu::find($id);

            $menu->name = $request->name;
            $menu->description = $request->description;
            if ($request->file('image')) {
                unlink('public/Menu/' . $menu->image);
                $file = $request->file('image');
                $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Menu'), $filename);
                $file->path = asset('public/Menu/' . $filename);
                $menu['image'] =  $file->path;
            }
            $menu->update();

            $res->success(new MenuResource($menu));
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

        /** @OA\Delete(
        *      path="/deleteMenu/{id}",
        *      operationId="deleteMenu",
        *      tags={"Menu"},
        *     security={{"Authorization":{}}},
        *      summary="delete menu",
        *      description="delete one menu.",
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
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $menu = Menu::find($id);
            $menu->delete();
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
