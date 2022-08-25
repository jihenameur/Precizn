<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\Paginate;
use App\Http\Resources\MenuProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\File;
use App\Models\Menu;
use App\Models\Option;
use App\Models\Product;
use App\Models\Product_hours;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\TypeProduct;
use DateTime;
use DateTimeZone;
use Exception;
use Faker\Extension\Helper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
/**
 * @OA\Tag(
 *     name="Product",
 *     description="Gestion product",
 *
 * )
 */
class ProductController extends Controller
{
    protected $controller;

    public function __construct()
    {
    }
/**
     * @OA\Post(
     *      path="/createPublicProduct",
     *      operationId="createPublicProduct",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="create a public product for the supplier." ,
     *      description="create a public product for the supplier.",
     *     @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="the product name.",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="default_price",
     *     required=false,
     *     description="default_price",
     *     @OA\Schema (type="decimal(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="min_period_time",
     *     required=true,
     *     description="min_period_time",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="max_period_time",
     *     required=true,
     *     description="max_period_time",
     *     @OA\Schema (type="integer")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="available",
     *     required=true,
     *     description="available",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter(
     *     in="query",
     *     name="unit_type",
     *     required=false,
     *     description="unit_type",
     *     @OA\Schema(type="string",enum={"Piece", "Kg", "L","M"})
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="unit_limit",
     *     required=false,
     *     description="unit_limit",
     *     @OA\Schema (type="double(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="weight",
     *     required=false,
     *     description="weight",
     *     @OA\Schema (type="double(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="dimension",
     *     required=false,
     *     description="dimension",
     *     @OA\Schema (type="string") ),
     *  @OA\Parameter (
     *     in="query",
     *     name="typeproduct",
     *     required=false,
     *     description="typeproduct",
     *     @OA\Items(
     *              type="array",
     *          )),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="tag",
     *     required=false,
     *     description="tag",
     *     @OA\Items(
     *              type="array",
     *          )),
     * @OA\Parameter(
     *     in="query",
     *     name="image[]",
     *     required=false,
     *     description="image[]",
     *     @OA\Schema (type="file")
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
    public function createPublicProduct(Request $request)
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
                'default_price' => 'required|numeric',
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'unit_type' => 'required|in:Piece,Kg,L,M'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());
            } else {
                $images = [];

                if ($request->file('image')) {

                    if (!is_array($request->file('image'))) {
                        array_push($images, $request->file('image'));
                    } else {
                        $images = $request->file('image');
                    }
                }
                $product = new Product();
                $product->name = $request->name;

                $product->description = $request->description;
                $product->default_price = $request->default_price;
                $product->private = 0;
                $product->is_deleted = false;
                if ($request->unit_type) {
                    $product->unit_type = $request->unit_type;
                }
                if ($request->unit_limit) {
                    $product->unit_limit = $request->unit_limit;
                }
                $product->weight = $request->weight;
                $product->dimension = $request->dimension;

                $product->save();
                if ($request->typeProduct != null) {

                    foreach (json_decode($request->typeProduct) as $key => $value) {
                        $typeProduct = TypeProduct::find($value);
                        $product->typeproduct()->attach($typeProduct);
                    }
                }
                if ($request->tags != null) {
                    foreach (json_decode($request->tags) as $key => $value) {
                        $tag = Tag::find($value);
                        $product->tag()->attach($tag);
                    }
                }
                foreach ($images as $image) {
                    $name = Str::uuid()->toString() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('public/Products'), $name); // your folder path
                    $file = new File();
                    $file->name = $name;
                    $file->path = asset('public/Products/' . $name);
                    $file->user_id = Auth::user()->id;
                    $file->save();
                    $file->products()->attach($product);
                }
            }
            $res->success($product);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Post(
     *      path="/createPrivateProduct",
     *      operationId="createPrivateProduct",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="create a private product for the supplier." ,
     *      description="create a private product for the supplier.",
     *     @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="the product name.",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="supplier_id",
     *     @OA\Schema (type="string")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="option_id",
     *     required=false,
     *     description="option_id",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=false,
     *     description="price",
     *     @OA\Schema (type="decimal(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="min_period_time",
     *     required=true,
     *     description="min_period_time",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="max_period_time",
     *     required=true,
     *     description="max_period_time",
     *     @OA\Schema (type="integer")
     *      ),
     * @OA\Parameter (
     *     in="query",
     *     name="menu_id",
     *     required=false,
     *     description="menu_id",
     *     @OA\Items(
     *              type="array",
     *          )),
     *     @OA\Parameter(
     *     in="query",
     *     name="unit_type",
     *     required=false,
     *     description="unit_type",
     *     @OA\Schema(type="string",enum={"Piece", "Kg", "L","M"})
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="unit_limit",
     *     required=false,
     *     description="unit_limit",
     *     @OA\Schema (type="integer",
     *           format="double(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="weight",
     *     required=false,
     *     description="weight",
     *     @OA\Schema (type="integer",
     *           format="double(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="dimension",
     *     required=false,
     *     description="dimension",
     *     @OA\Schema (type="string")),
     *  @OA\Parameter (
     *     in="query",
     *     name="typeProduct",
     *     required=false,
     *     description="typeproduct",
     *     @OA\Items(
     *              type="array",

     *          )),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="tags",
     *     required=false,
     *     description="tag",
     *     @OA\Items(
     *              type="array",

     *          )),
     *   @OA\Parameter(
     *     in="query",
     *     name="image",
     *     required=false,
     *     description="image",
     *     @OA\Schema (type="file")
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */

    public function createPrivateProduct(Request $request)
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
                'price' => 'required',
                'unit_type' => ['required', 'in:Piece,Kg,L,M']

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());
            }
            if ($request->product_id != null) {
                $product = Product::find($request->product_id);

                $supplier = Supplier::find($request->supplier_id);

                $product->suppliers()->attach($supplier, ['price' => $request->price]);
                if(count(json_decode($request->option_id))) {
                    foreach (json_decode($request->option_id) as $key => $value) {
                        $option = Option::find($value);
                        $product->options()->attach($option, ['supplier_id' => $request->supplier_id]);
                    }
                }
                if(count(json_decode($request->menu_id))) {
                    foreach (json_decode($request->menu_id) as $key => $value) {
                        $menu = Menu::find($value);
                        $product->menu()->attach($menu, ['supplier_id' => $request->supplier_id]);
                    }
                }
                $res->success($product);
            } else {
                $images = [];
                if ($request->file('image')) {

                    if (!is_array($request->file('image'))) {
                        //dd('test');
                        array_push($images, $request->file('image'));
                    } else {
                        $images = $request->file('image');
                    }
                }
                $product = new Product();
                $product->name = $request->name;
                $product->description = $request->description;
                $product->default_price = $request->price;
                $product->private = 1;
                $product->min_period_time = $request->min_period_time;
                $product->max_period_time = $request->max_period_time;
                $product->is_deleted = false;
                $product->unit_type = $request->unit_type;
                $product->unit_limit = $request->unit_limit;
                $product->weight = $request->weight;
                $product->dimension = $request->dimension;
                $product->save();
                if ($request->start_hour != null && $request->end_hour != null) {
                    $product_hours = new Product_hours();
                    $product_hours->product_id = $product->id;
                    $product_hours->start_hour = $request->start_hour;
                    $product_hours->end_hour = $request->end_hour;
                    $product_hours->save();
                }
                $supplier = Supplier::find($request->supplier_id);

                $product->suppliers()->attach($supplier, ['price' => $request->price]);
                if(count(json_decode($request->option_id))) {
                    foreach (json_decode($request->option_id) as $key => $value) {
                        $option = Option::find($value);
                        $product->options()->attach($option, ['supplier_id' => $request->supplier_id]);
                    }
                }
                foreach (json_decode($request->typeProduct) as $key => $value) {
                    $typeProduct = TypeProduct::find($value);
                    $product->typeproduct()->attach($typeProduct);
                }
                foreach (json_decode($request->tags) as $key => $value) {
                    $tag = Tag::find($value);
                    $product->tag()->attach($tag);
                }
                if(count(json_decode($request->menu_id))) {
                    foreach (json_decode($request->menu_id) as $key => $value) {
                        $menu = Menu::find($value);
                        $product->menu()->attach($menu, ['supplier_id' => $request->supplier_id]);
                    }
                }
                foreach ($images as $image) {
                    $name = Str::uuid()->toString() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('public/Products'), $name); // your folder path
                    $file = new File();
                    $file->name = $name;
                    $file->path = asset('public/Products/' . $name);
                    $file->user_id = Auth::user()->id;
                    $file->save();
                    $file->products()->attach($product);
                }
                $res->success($product);
            }
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

     /**
     * @OA\Post(
     *      path="/productToSupplier",
     *      operationId="productToSupplier",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="Add the product of the supplier." ,
     *      description="Add the product of the supplier.",
     *     @OA\Parameter (
     *     in="query",
     *     name="product_id",
     *     required=true,
     *     description="the product id.",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="the supplier id",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=false,
     *     description="price",
     *     @OA\Schema (type="decimal(8,2)")
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */

    public function productToSupplier(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'supplier_id' => 'required',
            'price' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return ($validator->errors());
        }
        $res = new Result();
        try {
            $product = Product::find($request->product_id);

            $supplier = Supplier::find($request->supplier_id);

            $product->suppliers()->attach($supplier, ['price' => $request->price]);

            foreach (json_decode($request->option_id) as $key => $value) {
                $option = Option::find($value);
                $product->options()->attach($option, ['supplier_id' => $request->supplier_id]);
            }

            $res->success($product);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Filter or get all
     *
     * @return Collection|Model[]|mixed|void
     */

      /**
     * @OA\Get(
     *      path="/getAllProduct/{per_page}",
     *      operationId="getAllProduct",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of products",
     *      description="Returns all products and associated provinces.",
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
    public function all($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:name,default_price,available,private' // complete the akak list
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
            $products = Product::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType));
            }

            $res->success([
                'par_page' => $products->count(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'products' => ProductResource::collection($products->items()),
            ]);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

      /**
     * @OA\Get(
     *      path="/getProduct/{id}",
     *     tags={"Product"},
     *     security={{"Authorization":{}}},
     *      operationId="getProduct",
     *      summary="Get product by product id",
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

    public function getProduct($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $product = Product::find($id);
            $options = Option::whereHas('products', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->get();
            $prd = [
                'product' => new ProductResource($product),
            ];
            $res->success($prd);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }


/**
     * @OA\Get(
     *      path="/getAllPublicProduct/{per_page}",
     *      operationId="getAllPublicProduct",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of public products",
     *      description="Returns all public products and associated provinces.",
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
    public function getAllPublicProduct($per_page,Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:name,default_price,available,private' // complete the akak list
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

            $products = Product::where('private', 0)->orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $products = Product::where('name', 'like', "%$keyword%")
                    ->orderBy($orderBy, $orderByType)
                    ->get();

            }
            $res->success([
                'par_page' => $products->count(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'products' => ProductResource::collection($products->items()),
            ]);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
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
    private function getFilterByKeywordClosure($keyword, $orderBy, $orderByType)
    {
        $res = new Result();
        try {
            $products = Product::where('name', 'like', "%$keyword%")
                ->orderBy($orderBy, $orderByType)
                ->get();

            $res->success([
                'products' => ProductResource::collection($products),
            ]);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/getSupplierProducts/{per_page}",
     *      operationId="getSupplierProducts",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of private  products where supplier id.",
     *      description="Returns all public products where supplier id.",
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

    public function getSupplierProduct($per_page)
    {
        if (!Auth::user()->isAuthorized(['supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = auth()->user();
            $product = Product::whereHas('suppliers', function ($q) use ($user) {
                $q->where('supplier_id', $user->userable_id);
            })
                ->where('is_deleted', false)
                ->paginate($per_page);
            $res->success($product);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function getdispoHourProductsSupplier($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        $dt = new DateTime();
        $tz = new DateTimeZone('Europe/paris'); // or whatever zone you're after
        $dt->setTimezone($tz);
        $currentTime = $dt->format('H:i');
        try {
            $product = Product::whereHas('suppliers', function ($q) use ($id) {
                $q->where('supplier_id', $id);
            })
                ->where('is_deleted', false)
                ->where('available', true)
                ->get();
            $products = [];

            foreach ($product as $key => $value) {
                if ($value->product_hours != null) {
                    $startTime = new DateTime($value->product_hours->start_hour);
                    $endTime = new DateTime($value->product_hours->end_hour);
                    if (($currentTime >= $startTime->format('H:i')) && ($currentTime <= $endTime->format('H:i'))) {

                        array_push($products, $value);
                    }
                } else {

                    array_push($products, $value);
                }
            }

            $res->success($products);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
 /**
     * @OA\Get(
     *      path="/getdispoHourProductsSupplierByTag",
     *      operationId="getdispoHourProductsSupplierByTag",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="Get the list of products diponibles in the current time.",
     *      description="Returns all diponible products in the current time.",
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
    public function getdispoHourProductsSupplierByTag($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier', 'client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'idSupplier' => 'required',
            'id_tag' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return ($validator->errors());
        }
        $res = new Result();
        $dt = new DateTime();
        $tz = new DateTimeZone('Europe/paris'); // or whatever zone you're after
        $dt->setTimezone($tz);
        $currentTime = $dt->format('H:i');
        try {
            if ($request->keywords == null) {
                $product = Product::whereHas('suppliers', function ($q) use ($request) {
                    $q->where('supplier_id', $request->idSupplier);
                })
                    ->where('available', true)
                    ->WhereHas('tag', function ($q) use ($request) {
                        if (count($request->id_tag)) {
                            $q->whereIn('tag_id', $request->id_tag);
                        }
                    })->get();

                // $product->paginate($per_page);
            } else {
                $product = Product::where('name', 'like', "%$request->keywords%")
                    ->whereHas('suppliers', function ($q) use ($request) {
                        $q->where('supplier_id', $request->idSupplier);
                    })
                    ->whereHas('tag', function ($q) use ($request) {
                        if (count($request->id_tag)) {
                            $q->whereIn('tag_id', $request->id_tag);
                        }
                    })
                    ->where('available', true)
                    ->get();
            }
            $products = [];

            foreach ($product as $key => $value) {
                if ($value->product_hours != null) {
                    $startTime = new DateTime($value->product_hours->start_hour);
                    $endTime = new DateTime($value->product_hours->end_hour);
                    if (($currentTime >= $startTime->format('H:i')) && ($currentTime <= $endTime->format('H:i'))) {

                        array_push($products, $value);
                    }
                } else {

                    array_push($products, $value);
                }
            }
            $paginate = new Paginate();

            $res->success($paginate->paginate($products, $per_page));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

     /**
     * @OA\Put(
     *      path="/ProductsSupplierNotAvailable/{id}",
     *      operationId="ProductsSupplierNotAvailable",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="update availability product",
     *      description="update availability product",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *      ),
     *    @OA\Parameter(
     *     in="query",
     *     name="available",
     *     required=true,
     *     description="available of product",
     *     @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
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
     *  )
     */

    public function ProductsSupplierNotAvailable($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'available' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return ($validator->errors());
        }
        $res = new Result();
        try {
            $product = Product::find($id);
            $product->available = $request->available;
            $product->update();

            $res->success($product);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Product|mixed|void
     */
    /**
     * @OA\Post(
     *      path="/updateProduct/{id}",
     *      operationId="updateProduct",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="create a public product for the supplier." ,
     *      description="create a public product for the supplier.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="the product name.",
     *     @OA\Schema (type="string")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="default_price",
     *     required=false,
     *     description="default_price",
     *     @OA\Schema (type="decimal(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="min_period_time",
     *     required=true,
     *     description="min_period_time",
     *     @OA\Schema (type="integer")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="max_period_time",
     *     required=true,
     *     description="max_period_time",
     *     @OA\Schema (type="integer")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="available",
     *     required=true,
     *     description="available",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter(
     *     in="query",
     *     name="unit_type",
     *     required=false,
     *     description="unit_type",
     *     @OA\Schema(type="string",enum={"Piece", "Kg", "L","M"})
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="unit_limit",
     *     required=false,
     *     description="unit_limit",
     *     @OA\Schema (type="double(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="weight",
     *     required=false,
     *     description="weight",
     *     @OA\Schema (type="double(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="dimension",
     *     required=false,
     *     description="dimension",
     *     @OA\Schema (type="string") ),
     *  @OA\Parameter (
     *     in="query",
     *     name="typeproduct",
     *     required=false,
     *     description="typeproduct",
     *     @OA\Items(
     *              type="array",
     *          )),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="tag",
     *     required=false,
     *     description="tag",
     *     @OA\Items(
     *              type="array",
     *          )),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
    public function update($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            /** @var Product $product */
            $allRequestAttributes = $request->all();
            $product = Product::find($id);
            if(!$product){
                return  'product not found';
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'default_price' => 'required|numeric',
                'unit_type' => 'required|in:Piece,Kg,L,M'

            ]); // create the validations

            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());
            }
            $images = [];
            if ($request->file('image')) {

                if (!is_array($request->file('image'))) {
                    //dd('test');
                    array_push($images, $request->file('image'));
                } else {
                    $images = $request->file('image');
                }
            }
            $product->name = $request->name;
            $product->description = $request->description;
            $product->default_price = $request->default_price;
            //$product->private = 1;
            $product->min_period_time = $request->min_period_time;
            $product->max_period_time = $request->max_period_time;
            $product->is_deleted = false;
            $product->unit_type = $request->unit_type;
            $product->unit_limit = $request->unit_limit;
            $product->weight = $request->weight;
            $product->dimension = $request->dimension;
            $product->update();
            if ($request->start_hour != null && $request->end_hour != null) {
                $product_hours = Product_hours::where('product_id', $product->id);
                $product_hours->product_id = $product->id;
                $product_hours->start_hour = $request->start_hour;
                $product_hours->end_hour = $request->end_hour;
                $product_hours->update();
            }
            if ($request->supplier_id) {
                $product->suppliers()->detach();
                    $supplier = Supplier::find($request->supplier_id);
                    $product->suppliers()->attach($supplier ,['price' => $request->price]);
                if(count(json_decode($request->menu_id))) {
                    $product->menu()->detach();
                    foreach (json_decode($request->menu_id) as $key => $value) {
                        $menu = Menu::find($value);
                        $product->menu()->attach($menu, ['supplier_id' => $request->supplier_id]);
                    }
                }
            }
            if (count(json_decode($request->typeProduct))) {
                $product->typeproduct()->detach();
                foreach (json_decode($request->typeProduct) as $key => $value) {
                    $typeProduct = TypeProduct::find($value);
                    $product->typeproduct()->attach($typeProduct);
                }
            }
            if (count(json_decode($request->tags))) {
                $product->tag()->detach();
                foreach (json_decode($request->tags) as $key => $value) {
                    $tag = TypeProduct::find($value);
                    $product->tag()->attach($tag);
                }
            }

                if(count(json_decode($request->option_id))) {
                    $product->options()->detach();
                    foreach (json_decode($request->option_id) as $key => $value) {
                        $option = Option::find($value);
                        $product->options()->attach($option, ['supplier_id' => $request->supplier_id]);
                    }
                }
            if ($images) {
                foreach ($images as $image) {
                    $name = Str::uuid()->toString() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('public/Products'), $name); // your folder path
                    $file = new File();
                    $file->name = $name;
                    $file->path = asset('public/Products/' . $name);
                    $file->user_id = Auth::user()->id;
                    $file->save();
                    $oldImagepath = $product->files;
                    if ($oldImagepath) {
                        foreach ($oldImagepath as $key => $value) {
                            unlink('public/Products/' . $value->name);
                        }
                        $product->files()->detach();
                    }
                        $file->products()->attach($product);
                    }
                }
                $res->success(new ProductResource($product));
            }
        catch
            (\Exception $exception) {
                if (env('APP_DEBUG')) {
                    $res->fail($exception->getMessage());
                }
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
    /**
     * @OA\Delete(
     *      path="/destroyProduct/{id}",
     *      operationId="destroyProduct",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="delete product",
     *      description="delete one product.",
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
            /** @var Product $product */
            $product = Product::find($id);
            $product->delete();

            $res->success("Deleted");
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Post(
     *      path="/getSupplierProductsClean",
     *      operationId="getSupplierProductsClean",
     *      tags={"Product"},
     *     security={{"Authorization":{}}},
     *      summary="Get products of the supplier without pagination." ,
     *      description="Get products of the supplier without pagination",
     *     @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="the product id.",
     *     @OA\Schema (type="string")
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getSuppliersProductClean(Request $request)
    {

        if (!Auth::user()->isAuthorized(['supplier','admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request,[
           'supplier_id' => 'required|exists:suppliers,id'
        ]);
        $res = new Result();
        try {
            $products = Product::whereHas('suppliers', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            })->get();
            $res->success(ProductResource::collection($products));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }else{
                $res->fail('erreur serveur 500');
            }

        }
        return new JsonResponse($res, $res->code);
    }
}
