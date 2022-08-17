<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Adsarea;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    /**
     * @OA\Post(
     *      path="/addadsarea",
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
                "id"         =>  $adsarea->id,
                "title"     =>  $adsarea->title
            ];


            $res->success($response);
        } catch (\Exception $exception) {
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Get(
     *      path="/getcategorybyid/{id}",
     *     tags={"Category"},
     *     security={{"Authorization":{}}},
     *      operationId="getcategorybyid",
     *      summary="Get category by category id",
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
    public function categorybyid($id)
    {
        $res = new Result();
        try {
            $category = Category::find($id);
            $res->success($category);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
