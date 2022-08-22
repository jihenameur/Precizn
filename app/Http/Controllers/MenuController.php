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
    /**
     * @OA\Post(
     *      path="/getmenu",
     *      operationId="getSupplierMenu",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Get supplier Menu",
     *      description="Returns the menu of a supplier.",
     *    @OA\Parameter(
     *          name="supplier_id",
     *          in="query",
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
   public function getSupplierMenu(Request $request)
   {
       if (!Auth::user()->isAuthorized(['admin','supplier'])) {
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
            $menus = Menu::where('supplier_id',$request->supplier_id)->get();
            $sorted = $menus->sortBy('position');
            $res->success(MenuResource::collection($sorted));
       }catch (\Exception $exception) {
           $res->fail($exception->getMessage());
       }
       return new JsonResponse($res, $res->code);


   }

}
