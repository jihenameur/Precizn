<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\Paginate;
use App\Models\Category;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Product $model,  Controller $controller = null)
    {
        $this->model = $model;
    }

    public function createPublicProduct(Request $request)
    {
        //dd(json_decode($request->typeProduct));
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'default_price' => 'required',
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'unit_type' => ['required', 'in:Piece,Kg,L,M']

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());
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
                //dd($images);
                //dd($request->file('image'));
                foreach ($images as $image) {

                    $name = $image->getClientOriginalName();

                    $image->move(public_path('public/Products'), $name); // your folder path
                    $data[] = $name;
                }

                $product = new Product();
                $product->name = $request->name;
                $product['image'] = json_encode($data);
                // if ($request->file('image')) {
                //     $file = $request->file('image');
                //     $filename = date('YmdHi') . $file->getClientOriginalName();
                //     //dd( $filename);

                //     $file->move(public_path('public/Products'), $filename);
                //     $product['image'] = $filename;
                // }

                $product->description = $request->description;
                $product->default_price = $request->default_price;
                $product->private = 0;
                $product->is_deleted = false;
                $product->unit_type = $request->unit_type;
                $product->unit_limit = $request->unit_limit;
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
                        $tag = TypeProduct::find($value);
                        $product->tag()->attach($tag);
                    }
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

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

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
                //dd($images);
                //dd($request->file('image'));
                foreach ($images as $image) {

                    $name = $image->getClientOriginalName();

                    $image->move(public_path('public/Products'), $name); // your folder path
                    $data[] = $name;
                }
                $product = new Product();

                $product->name = $request->name;
                $product['image'] = json_encode($data);

                // if ($request->file('image')) {
                //     $file = $request->file('image');
                //     $filename =  $file->getClientOriginalName();
                //     $file->move(public_path('public/Products'), $filename);
                //     $product['image'] = $filename;
                // }
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
                $res->success($product);
            }
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function productToSupplier(Request $request)
    {
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
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $products = Product::paginate($per_page);
            $i = 0;
            foreach ($products as $key => $pro) {
                $supplier = Supplier::whereHas('products', function ($q) use ($pro) {
                    $q->where('product_id', $pro->id);
                })->get();
                $prods[$i] = ['product', $pro, 'supplier', $supplier];
                $i++;
            }


            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword));
            }
            $res->success($products);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getProduct($id)
    {
        $res = new Result();
        try {
            $product = Product::find($id);
            $options = Option::whereHas('products', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->get();
            $prd = [
                'product' => $product,
                'options' => $options

            ];
            $res->success($prd);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getAllPublicProduct($per_page)
    {
        $res = new Result();
        try {
            $products = Product::where('private', 0)->paginate($per_page);
            $res->success($products);
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
    private function getFilterByKeywordClosure($keyword)
    {
        $res = new Result();
        try {
            $products = Product::where('name', 'like', "%$keyword%")
                // ->orWhere('lastname', 'like', "%$keyword%")
                // ->orWhereRaw("CONCAT(lastname,' ',firstname) like '%$keyword%'")
                ->get();
            foreach ($products as $key => $pro) {
                $supplier = Supplier::whereHas('products', function ($q) use ($pro) {
                    $q->where('product_id', $pro->id);
                })->get();
                $prods = ['product', $pro, 'supplier', $supplier];
            }
            $res->success($products);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getSupplierProduct($per_page)
    {
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
                       if(count($request->id_tag)){
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
                        if(count($request->id_tag)){
                            $q->whereIn('tag_id', $request->id_tag);
                           }                    })
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

            $res->success($paginate->paginate($products,$per_page));
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function ProductsSupplierNotAvailable($id, Request $request)
    {
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
        $res = new Result();
        try {
            /** @var Product $product */
            $allRequestAttributes = $request->all();
            $product = Product::find($id);

            // $validator = Validator::make($request->all(), [
            //     'name' => 'required',

            // ]); // create the validations
            // if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            // {
            //     return back()->withInput()->withErrors($validator);
            //     // validation failed redirect back to form

            // } else {
            $images = [];
            if ($request->file('image')) {

                if (!is_array($request->file('image'))) {
                    //dd('test');
                    array_push($images, $request->file('image'));
                } else {
                    $images = $request->file('image');
                }
            }
            foreach ($images as $image) {
                $imageName = $image->getClientOriginalName();
                //save images in public/images/listing folder
                $image->move(public_path('public/Products'), $imageName);
                // Delete the old photo
                $oldImagepath = $product->image;
                foreach (json_decode($oldImagepath) as $key => $value) {
                    //unlink(storage_path('public/Products/'.$value));
                    unlink('public/Products/' . $value);
                }
                //Storage::delete($oldImagepath);

                $data[] = $imageName;
                $json_encode = json_encode($data);
                $product->image = $json_encode;
            }
            //$product->fill($allRequestAttributes);
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
        $res = new Result();
        try {
            /** @var Product $product */
            $product = Product::find($id);
            $product->delete();

            $res->success($product);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
