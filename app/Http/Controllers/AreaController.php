<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Adsarea;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Adsarea",
 *     description="Adsarea management",
 *
 * )
 */
class AreaController extends Controller
{
    /**
     * @OA\Post(
     *      path="/adsarea/create",
     *      operationId="addadsarea",
     *      tags={"Adsarea"},
     *     security={{"Authorization":{}}},
     *      summary="create adsarea" ,
     *      description="create zdsarea",
     *     @OA\Parameter (
     *     in="query",
     *     name="title",
     *     required=true,
     *     description="title",
     *     @OA\Schema (type="string")
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
                'title' => 'required|unique:adsarea'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }

            $adsarea = new Adsarea();
            $adsarea->title = $request->title;
            $adsarea->save();

            $response['ads_area'] = [
                "id" => $adsarea->id,
                "title" => $adsarea->title
            ];


            $res->success($response);
        } catch (\Exception $exception) {
            $res->fail('erreur serveur 500 ');
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Get(
     *      path="/adsarea/get/{id}",
     *     tags={"Adsarea"},
     *     security={{"Authorization":{}}},
     *      operationId="getadsareabyid",
     *      summary="Get adsarea by adsarea id",
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
    public function adsareabyid($id)
    {
        $res = new Result();
        try {
            $adsarea = Adsarea::find($id);
            $res->success($adsarea);
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
     *      path="/adsarea/all/{per_page}",
     *      operationId="getadsarea",
     *      tags={"Adsarea"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of adsarea",
     *      description="Returns all adsarea .",
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
            $adsarea = Adsarea::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $adsarea = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success($adsarea);
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
        $adsarea = Adsarea::where('title', 'like', "%$keyword%")
            ->orderBy($orderBy, $orderByType)
            ->get();
        return $adsarea;
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
     *      path="/adsarea/update/{id}",
     *      operationId="updateAdsarea",
     *      tags={"Adsarea"},
     *     security={{"Authorization":{}}},
     *      summary="update adsarea ",
     *      description="update adsarea",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *         @OA\Parameter (
     *     in="query",
     *     name="title",
     *     required=true,
     *     description="title",
     *     @OA\Schema (type="string")
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
            'title' => 'required|unique:adsarea'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $adsarea = Adsarea::find($id);
            $adsarea->title = $request->title;

            $adsarea->update();

            $response['adsarea'] = [
                "id" => $adsarea->id,
                "name" => $adsarea->title,

            ];
            $res->success($response);
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
     *      path="/adsarea/delete/{id}",
     *      operationId="deleteAdsarea",
     *      tags={"Adsarea"},
     *     security={{"Authorization":{}}},
     *      summary="delete Adsarea",
     *      description="delete one Adsarea.",
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
        $adsarea = Adsarea::find($id);
        try {
            $adsarea->delete();
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
