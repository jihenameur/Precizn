<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Option;
use App\Models\Panier;
use App\Models\Panier_Product;
use App\Models\Product;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Tag(
 *     name="Panier",
 *     description="Gestion panier",
 *
 * )
 */
class PanierController extends Controller
{
/**
     * @OA\Post(
     *      path="/create",
     *      operationId="create",
     *      tags={"Panier"},
     *     security={{"Authorization":{}}},
     *      summary="create  panier." ,
     *      description="create panier.",
     *     @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=true,
     *     description="prix.",
     *     @OA\Schema ( type="integer",
     *           format="decimal(8,2)")
     *      ),
     *
     *
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
    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), []); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }

            $panier = new Panier();
            $panier->save();
            //return $panier;
            $res->success($panier);
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
     *      path="/addProduct/{id}",
     *      operationId="addProduct",
     *      tags={"Panier"},
     *     security={{"Authorization":{}}},
     *      summary="add products to shopping cart.." ,
     *      description="add products to shopping cart.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="quantity",
     *     required=true,
     *     description="quantity products.",
     *     @OA\Schema ( type="integer",
     *           format="int(11)")
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="product_id",
     *     required=true,
     *     description="product id.",
     *     @OA\Schema ( type="integer",
     *           format="bigint(20)")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="options",
     *     required=true,
     *     description="options.",
     *  @OA\Items(type="array")
     *
     *     ),
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
    public function addProduct(Request $request, $id)
    {
        $res = new Result();
        try {
            $panier = Panier::find($id);
            $priceOption = 0;
            if ($panier->products->isEmpty()) {
                $product = Product::find($request['product_id']);
                $panier_product = new Panier_Product();
                $panier_product->quantity = $request['quantity'];
                $panier_product->product_id = $request['product_id'];
                $panier_product->panier_id = $panier->id;
                $panier_product->product_price = $product->price;
                $panier_product->save();
                foreach ($request['options'] as $key => $value) {
                    $option = Option::find($value);
                    $panier_product->options()->attach($option);
                    $priceOption = $priceOption + $option->price;
                }
                $panier_product->product_price = $panier_product->product_price + $priceOption;
                $panier_product->update();
                //$panier->products()->attach($product, ['quantity' => $request['quantity']]);
                // $panier_product=Panier_Product::where()->get();
                $panier->price = $panier->price + (($product->price +  $priceOption) * $request['quantity']);
                $panier->update();
            } else {

                $product = Product::find($request['product_id']);

                $suppliernewproduct = Supplier::whereHas('products', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })->get();
                $supplierpanierproduct = Supplier::whereHas('products', function ($q) use ($panier) {
                    $q->where('product_id', $panier->products[0]->id);
                })->get();

                if ($supplierpanierproduct ==  $suppliernewproduct) {
                    //$panier->products()->attach($product, ['quantity' => $request['quantity']]);
                    $panier_product = new Panier_Product();
                    $panier_product->quantity = $request['quantity'];
                    $panier_product->product_id = $request['product_id'];
                    $panier_product->panier_id = $panier->id;
                    $panier_product->product_price = $product->price;
                    $panier_product->save();
                    foreach ($request['options'] as $key => $value) {
                        $option = Option::find($value);
                        $panier_product->options()->attach($option);
                        $priceOption = $priceOption + $option->price;
                    }
                    $panier_product->product_price = $panier_product->product_price + $priceOption;
                    $panier_product->update();

                    $panier->price = $panier->price + (($product->price + $priceOption) * $request['quantity']);
                    $panier->update();
                } else {
                    $res->fail('Not same supplier');
                    return new JsonResponse($res, $res->code);
                }
            }

            // return $panier;
            $res->success($panier);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
     /** @OA\Delete(
        *      path="/deleteProduct/{id}",
        *      operationId="deleteProduct",
        *      tags={"Panier"},
        *     security={{"Authorization":{}}},
        *      summary="delete product panier",
        *      description="delete product panier.",
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
    public function deleteProduct(Request $request, $id)
    {
        $res = new Result();
        try {
            $panier = Panier::find($id);

            $product = Product::find($request['product_id']);

            $panier->products()->detach($product);

            $res->success($panier);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    public function deletePanier($id)
    {
        $res = new Result();
        try {
            $panier = Panier::find($id);
            $panier->delete();
            $res->success($panier);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    /** @OA\Get(
        *      path="/getPanier/{id}",
        *     tags={"Panier"},
        *     security={{"Authorization":{}}},
        *      operationId="getPanier",
        *      summary="Get panier by panier id",
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
    public function getPanier($id)
    {
        $res = new Result();
        try {
            $product_options = [];
            $panier = Panier::find($id);
            $panier_product = Panier_Product::where('panier_id', $id)->get();

            foreach ($panier_product as $key => $value) {
                $product_option = Option::whereHas('panierproducts', function ($q) use ($value) {
                    $q->where('panier__product_id', $value->id);
                })
                    ->get();
                $product = Product::where('id', $value->product_id)->get();

                $productOpt = ['product' => $product, 'quantity' => $value['quantity'], 'options' => $product_option];
                array_push($product_options, $productOpt);
            }
            //return [$panier, $product_options];

            $res->success($product_options);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
}
