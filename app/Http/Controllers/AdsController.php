<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Http\Resources\AdsResource;
use App\Models\Ads;
use App\Models\Adsarea;
use App\Models\Category;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Ads",
 *     description="Ads management",
 *
 * )
 */
class AdsController extends Controller
{
    /**
     * @OA\Post(
     *      path="/ads/create",
     *      operationId="addads",
     *      tags={"Ads"},
     *     security={{"Authorization":{}}},
     *      summary="create ads" ,
     *      description="create ads",
     *     @OA\Parameter (
     *     in="query",
     *     name="adsarea_id",
     *     required=true,
     *     description="adsarea_id",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="file_id",
     *     required=true,
     *     description="file_id",
     *     @OA\Schema (type="integer")
     *      ),
     *      @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="supplier_id",
     *     @OA\Schema (type="integer")
     *      ),
     *      @OA\Parameter (
     *     in="query",
     *     name="product_id",
     *     required=true,
     *     description="product_id",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="menu_id",
     *     required=true,
     *     description="menu_id",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="start_date",
     *     required=true,
     *     description="start_date",
     *     @OA\Schema (type="datetime")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="end_date",
     *     required=true,
     *     description="end_date",
     *     @OA\Schema (type="datetime")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=true,
     *     description="end_date",
     *     @OA\Schema (type="decimal")
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
    public function create(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'adsarea_id' => 'required',
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'price' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $ads = new Ads();
            if ($request->file('image')) {
                $name = Str::uuid()->toString() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('public/Ads'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Ads/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $ads->file_id = $file->id;
            $ads->adsarea_id  = $request->adsarea_id ;
            $ads->supplier_id = $request->supplier_id;
            $ads->product_id = $request->product_id;
            $ads->menu_id = $request->menu_id;
            $ads->start_date = $request->start_date;
            $ads->end_date = $request->end_date;
            $ads->price = $request->price;
            $ads->save();

            $res->success(new AdsResource($ads));
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/ads/get/{id}",
     *     tags={"Ads"},
     *     security={{"Authorization":{}}},
     *      operationId="getadsbyid",
     *      summary="Get ads by id",
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
    public function adsbyid($id)
    {
        $res = new Result();
        try {
            $ads = Ads::find($id);
            $res->success(new AdsResource($ads));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
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
     *      path="/ads/all/{per_page}",
     *      operationId="getads",
     *      tags={"Ads"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of ads",
     *      description="Returns all ads .",
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
     *  )
     */
    public function all($per_page = 10, Request $request)
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
                'orderBy' => 'required|in:title,created_at'
            ]);
            $orderBy = $request->orderBy;
        }
        if ($request->has('orderByType') && $request->orderByType != null) {
            $this->validate($request, [
                'orderByType' => 'required|in:ASC,DESC'
            ]);
            $orderByType = $request->orderByType;
        }
        $res = new Result();

        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $ads = Ads::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $ads = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success(AdsResource::collection($ads));
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
        $ads = Ads::where('supplier_id', 'like', "%$keyword%")
            ->orWhere('product_id', 'like', "%$keyword%")
            ->orWhere('start_date', $keyword)
            ->orWhere('end_date', $keyword)
            ->orderBy($orderBy, $orderByType)
            ->get();
        return $ads;
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Category|mixed|void
     */
    /**
     * @OA\Post(
     *      path="/ads/update/{id}",
     *      operationId="updateAds",
     *      tags={"Ads"},
     *     security={{"Authorization":{}}},
     *      summary="update ads ",
     *      description="update ads",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="ads_id",
     *     required=true,
     *     description="ads_id",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="file_id",
     *     required=true,
     *     description="file_id",
     *     @OA\Schema (type="integer")
     *      ),
     *      @OA\Parameter (
     *     in="query",
     *     name="supplier_id",
     *     required=true,
     *     description="supplier_id",
     *     @OA\Schema (type="integer")
     *      ),
     *      @OA\Parameter (
     *     in="query",
     *     name="product_id",
     *     required=true,
     *     description="product_id",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="menu_id",
     *     required=true,
     *     description="menu_id",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="start_date",
     *     required=true,
     *     description="start_date",
     *     @OA\Schema (type="datetime")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="end_date",
     *     required=true,
     *     description="end_date",
     *     @OA\Schema (type="datetime")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="price",
     *     required=true,
     *     description="end_date",
     *     @OA\Schema (type="decimal")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *
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
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'adsarea_id' => 'required',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'price' => 'required',
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $ads = Ads::find($id);
            if ($request->file('image')) {
                $image = Ads::find($ads->file_id);
                if($image) {
                    unlink('public/Ads/' . $image->name);
                }
                $name = Str::uuid()->toString() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('public/Ads'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Ads/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $ads->adsarea_id = $request->adsarea_id;
            $ads->file_id = $file->id;
            $ads->supplier_id = $request->supplier_id;
            $ads->product_id = $request->product_id;
            $ads->menu_id = $request->menu_id;
            $ads->start_date = $request->start_date;
            $ads->end_date = $request->end_date;
            $ads->price = $request->price;
            $ads->update();

            $res->success(new AdsResource($ads));
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
     * @return bool|mixed|void
     */
    /**
     * @OA\Delete(
     *      path="/ads/delete/{id}",
     *      operationId="deleteAds",
     *      tags={"Ads"},
     *     security={{"Authorization":{}}},
     *      summary="delete Ads",
     *      description="delete one Ads.",
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
        $ads = Ads::find($id);
        try {
            $ads->delete();
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
