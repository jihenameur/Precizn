<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\Paginate;
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

class ProductController extends Controller
{
    protected $controller;

    public function __construct()
    {
    }

    public function createPublicProduct(Request $request)
    {

        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }


        //dd(json_decode($request->typeProduct));
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
                throw new Exception($validator->errors());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
                throw new Exception($validator->errors());

            }
            if ($request->product_id != null) {
                $product = Product::find($request->product_id);

                $supplier = Supplier::find($request->supplier_id);

                $product->suppliers()->attach($supplier, ['price' => $request->price]);

                foreach (json_decode($request->option_id) as $key => $value) {
                    $option = Option::find($value);
                    $product->options()->attach($option, ['supplier_id' => $request->supplier_id]);
                }
                foreach (json_decode($request->menu_id) as $key => $value) {
                    $menu = Menu::find($value);
                    $product->menu()->attach($menu, ['supplier_id' => $request->supplier_id]);
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
                    $product_hours->product_id  = $product->id;
                    $product_hours->start_hour = $request->start_hour;
                    $product_hours->end_hour = $request->end_hour;
                    $product_hours->save();
                }
                $supplier = Supplier::find($request->supplier_id);

                $product->suppliers()->attach($supplier, ['price' => $request->price]);

                foreach (json_decode($request->option_id) as $key => $value) {
                    $option = Option::find($value);
                    $product->options()->attach($option, ['supplier_id' => $request->supplier_id]);
                }
                foreach (json_decode($request->typeProduct) as $key => $value) {
                    $typeProduct = TypeProduct::find($value);
                    $product->typeproduct()->attach($typeProduct);
                }
                foreach (json_decode($request->tags) as $key => $value) {
                    $tag = Tag::find($value);
                    $product->tag()->attach($tag);
                }
                foreach (json_decode($request->menu_id) as $key => $value) {
                    $menu = Menu::find($value);
                    $product->menu()->attach($menu, ['supplier_id' => $request->supplier_id]);
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function productToSupplier(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * Filter or get all
     *
     * @return Collection|Model[]|mixed|void
     */
    public function all($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $res = new Result();
        try {

            $orderBy = 'name';
            $orderByType = "ASC";
            if($request->has('orderBy') && $request->orderBy != null){
                $this->validate($request,[
                    'orderBy' => 'required|in:name,default_price,available,private' // complete the akak list
                ]);
                $orderBy = $request->orderBy;
            }
            if($request->has('orderByType') && $request->orderByType != null){
                $this->validate($request,[
                    'orderByType' => 'required|in:ASC,DESC' // complete the akak list
                ]);
                $orderByType = $request->orderByType;
            }
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getAllPublicProduct($per_page)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $products = Product::where('private', 0)->paginate($per_page);

            $res->success([
                'par_page' => $products->count(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'products' => ProductResource::collection($products->items()),
            ]);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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

            $res->success( [
                'products' => ProductResource::collection($products),
            ]);

        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
            $res->fail($exception->getMessage());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    public function getdispoHourProductsSupplierByTag($per_page, Request $request)
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function ProductsSupplierNotAvailable($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $product = Product::find($id);
            $product->available = $request->available;
            $product->update();

            $res->success($product);
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
     * @return Product|mixed|void
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

            $validator = Validator::make($request->all(), [
                'name' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

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
            $product->description = $request->description;
            $product->default_price = $request->default_price;
            $product->min_period_time = $request->min_period_time;
            $product->max_period_time = $request->max_period_time;
            $product->private = 0;
            $product->update();
            $product->typeproduct()->detach();
            $product->tag()->detach();
            foreach (json_decode($request->typeProduct) as $key => $value) {
                $typeProduct = TypeProduct::find($value);
                $product->typeproduct()->attach($typeProduct);
            }
            foreach (json_decode($request->tags) as $key => $value) {
                $tag = TypeProduct::find($value);
                $product->tag()->attach($tag);
            }
            foreach ($images as $image) {
                $name = Str::uuid()->toString() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('public/Products'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Products/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
                $oldImagepath = $product->files;
                foreach ($oldImagepath as $key => $value) {
                    unlink('public/Products/' . $value->name);
                }
                $product->files()->detach();

                $file->products()->attach($product);
            }
            $res->success($product);
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
