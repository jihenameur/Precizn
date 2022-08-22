<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\BaseModel\Result;
use App\Models\Client;
use App\Models\Command;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
  /**
     * @OA\Tag(
     *     name="Coupon",
     *     description="Gestion Coupon",
     *
     * )
   */
class CouponController extends Controller
{
   /**
     * @OA\Post(
     *      path="/addCoupon",
     *      operationId="addCoupon",
     *      tags={"Coupon"},
     *     security={{"Authorization":{}}},
     *      summary="create coupon.",
     *     @OA\Parameter (
     *     in="query",
     *     name="code_coupon",
     *     required=true,
     *     description="code coupon ",
     *    @OA\Schema( type="string" )
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="value",
     *     required=false,
     *     description="value",
     *    @OA\Schema(type="integer",
     *           format="double(8,2)" ),
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="title",
     *     required=false,
     *     description="title",
     *    @OA\Schema( type="string"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="start_date",
     *     required=true,
     *     description="start_date",
     *    @OA\Schema( type="string",
     *           format="date-time"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="end_date",
     *     required=true,
     *     description="end_date",
     *    @OA\Schema( type="string",
     *      format="date-time"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *    @OA\Schema( type="string" ),
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="quantity",
     *     required=true,
     *     description="quantity",
     *    @OA\Schema(type="integer",
     *           format="int(11)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="client_quantity",
     *     required=true,
     *     description="client_quantity",
     *    @OA\Schema(type="integer",
     *           format="int(11)"),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="status",
     *     required=true,
     *     description="status",
     *    @OA\Schema(type="integer",
     *           format="int(11)"),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="client_id ",
     *     required=true,
     *     description="client_id ",
     *    @OA\Schema(type="integer",
     *           format="bigint(20)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="montant_min ",
     *     required=true,
     *     description="montant_min ",
     *    @OA\Schema(type="integer",
     *           format="double(8,2)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="frais_port ",
     *     required=true,
     *     description="frais_port ",
     *    @OA\Schema(type="integer",
     *           format="tinyint(1)"),
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="taxe ",
     *     required=true,
     *     description="taxe ",
     *     @OA\Schema(type="string",enum={"TTC", "HT"})
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="currency",
     *     required=true,
     *     description="currency",
     *    @OA\Schema( type="string" ),
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
     *          description="Forbidden",
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
     */
    public function create(Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'code_coupon' => 'required',
                'type' => 'in:amount,percentage',
                'taxe' => 'in:TTC,HT'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $coupon = new Coupon();
            $coupon->code_coupon = $request->code_coupon;
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->title = $request->title;
            $coupon->start_date = $request->start_date;
            $coupon->end_date = $request->end_date;
            $coupon->description = $request->description;
            $coupon->quantity = $request->quantity;
            $coupon->client_quantity = $request->client_quantity;
            $coupon->status = $request->status;

            $coupon->montant_min = $request->montant_min;
            $coupon->currency = $request->currency;
            $coupon->taxe = $request->taxe;
            $coupon->frais_port = $request->frais_port;

            $coupon->client_id  = $request->client_id;
            $coupon->save();
            $res->success($coupon);
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
     *      path="/getCoupon/{id}",
     *     tags={"Coupon"},
     *     security={{"Authorization":{}}},
     *      operationId="getCoupon",
     *      summary="Get coupon by coupon id",
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
    public function getByid($id)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $coupon = Coupon::find($id);
            $res->success($coupon);
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
     *      path="/getAllCoupon/{per_page}",
     *      operationId="getAllCoupon",
     *      tags={"Coupon"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of coupon",
     *      description="Returns all coupon.",
     *    @OA\Parameter(
     *          name="per_page",
     *          in="path",
     *          required=true,
     *
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="orderBy",
     *     required=true,
     *     description="orderBy",
     *    @OA\Schema( type="string" ),
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
        if($request->has('orderBy') && $request->orderBy != null){
            $this->validate($request,[
                'orderBy' => 'required|in:title,id,created_at' // complete the  list
            ]);
            $orderBy = $request->orderBy;
        }
        if($request->has('orderByType') && $request->orderByType != null){
            $this->validate($request,[
                'orderByType' => 'required|in:ASC,DESC' // complete the  list
            ]);
            $orderByType = $request->orderByType;
        }
        $res = new Result();
        try {

            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $coupons = Coupon::orderBy($orderBy, $orderByType)->paginate($per_page);

            $res->success($coupons);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    private function getFilterByKeywordClosure($keyword, $orderBy, $orderByType)
    {
        $res = new Result();
        try {
            $coupons = Coupon::where('title', 'like', "%$keyword%")
            ->orderBy($orderBy, $orderByType)
            ->get();

            $res->success($coupons);

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
     *      path="/updateCoupon/{id}",
     *      operationId="updateCoupon",
     *      tags={"Coupon"},
     *     security={{"Authorization":{}}},
     *      summary="update coupon.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="code_coupon",
     *     required=true,
     *     description="code coupon ",
     *    @OA\Schema( type="string" )
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="value",
     *     required=false,
     *     description="value",
     *    @OA\Schema(type="integer",
     *           format="double(8,2)" ),
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="title",
     *     required=false,
     *     description="title",
     *    @OA\Schema( type="string"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="start_date",
     *     required=true,
     *     description="start_date",
     *    @OA\Schema( type="string",
     *           format="date-time"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="end_date",
     *     required=true,
     *     description="end_date",
     *    @OA\Schema( type="string",
     *      format="date-time"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
     *    @OA\Schema( type="string" ),
     *      ),
     *    @OA\Parameter (
     *     in="query",
     *     name="quantity",
     *     required=true,
     *     description="quantity",
     *    @OA\Schema(type="integer",
     *           format="int(11)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="client_quantity",
     *     required=true,
     *     description="client_quantity",
     *    @OA\Schema(type="integer",
     *           format="int(11)"),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="status",
     *     required=true,
     *     description="status",
     *    @OA\Schema(type="integer",
     *           format="int(11)"),
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="client_id ",
     *     required=true,
     *     description="client_id ",
     *    @OA\Schema(type="integer",
     *           format="bigint(20)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="montant_min ",
     *     required=true,
     *     description="montant_min ",
     *    @OA\Schema(type="integer",
     *           format="double(8,2)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="frais_port ",
     *     required=true,
     *     description="frais_port ",
     *    @OA\Schema(type="integer",
     *           format="tinyint(1)"),
     *      ),
     *  *  @OA\Parameter (
     *     in="query",
     *     name="taxe ",
     *     required=true,
     *     description="taxe ",
     *     @OA\Schema(type="string",enum={"TTC", "HT"})
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="currency",
     *     required=true,
     *     description="currency",
     *    @OA\Schema( type="string" ),
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
     *          description="Forbidden",
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
     */
    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'code_coupon' => 'required',
            'type' => 'in:amount,percentage',
            'taxe' => 'in:TTC,HT'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $coupon = Coupon::find($id);

            $coupon->code_coupon = $request->code_coupon;
            $coupon->type = $request->type;
            $coupon->value = $request->value;
            $coupon->title = $request->title;
            $coupon->start_date = $request->start_date;
            $coupon->end_date = $request->end_date;
            $coupon->description = $request->description;
            $coupon->quantity = $request->quantity;
            $coupon->client_quantity = $request->client_quantity;
            $coupon->status = $request->status;

            $coupon->montant_min = $request->montant_min;
            $coupon->currency = $request->currency;
            $coupon->taxe = $request->taxe;
            $coupon->frais_port = $request->frais_port;

            $coupon->client_id  = $request->client_id;
            $coupon->update();

            $res->success($coupon);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
       /**
     * @OA\Delete(
     *      path="/deleteCoupon/{id}",
     *      operationId="deleteCoupon",
     *      tags={"Coupon"},
     *     security={{"Authorization":{}}},
     *      summary="delete coupon",
     *      description="delete one coupon.",
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
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $coupon = Coupon::find($id);
            $coupon->delete();

            $res->success($coupon);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
}
