<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Http\Resources\MenuResource;
use App\Http\Resources\ProductResource;
use App\Models\File;
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
        if (!Auth::user()->isAuthorized(['admin', 'supplier','client'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request, [
            'supplier_id' => 'required|exists:suppliers,id'
        ]);
        $res = new Result();
        try {
            $menus = Menu::where('supplier_id', $request->supplier_id)->get();
            $sorted = $menus->sortBy('position');
            $res->success(MenuResource::collection($sorted));
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);


    }

    /**
     * @OA\Post(
     *      path="/add_product_to_submenu",
     *      operationId="AddProductToSubMenu",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Add product to subMenu",
     *      description="Returns subMenu.",
     *    @OA\Parameter(
     *          name="submenu_id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="product_id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="position",
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
    public function AddProductToSubMenu(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'submenu_id' => 'required|exists:menus,id',
            'position' => 'required|numeric'
        ]);

        $res = new Result();
        try {
            $product = Product::find($request->product_id);
            $sub_menu = Menu::find($request->submenu_id);
            $sub_menu->products()->attach($product, ['position' => $request->position]);
            $sub_menu->save();
            $res->success(new MenuResource($sub_menu));

        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            } else {
                $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Post(
     *      path="/add_submenu",
     *      operationId="AddSubMenu",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Add subMenu",
     *      description="Returns subMenu.",
     *    @OA\Parameter(
     *          name="supplier_id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="position",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="description",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="image",
     *          in="query",
     *          required=true,
     *     @OA\Schema (type="file")
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
    public function AddSubMenu(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request, [
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required',
            'description' => 'required',
            'image' => 'required',
            'position' => 'required|numeric'
        ]);
        $res = new Result();
        try {
            $sub_menu = new Menu();
            $sub_menu->supplier_id = $request->supplier_id;
            $sub_menu->name = $request->name;
            $sub_menu->description = $request->description;
            $sub_menu->position = $request->position;
            if ($request->file('image')) {
                $name = Str::uuid()->toString() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('public/Products'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Products/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
                $sub_menu->file_id = $file->id;
            }
            $sub_menu->save();
            $sub_menu->refresh();
            $res->success(new MenuResource($sub_menu));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            } else {
                $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);

    }
    /**
     * @OA\Post(
     *      path="/update_submenu",
     *      operationId="updateSubMenu",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Add subMenu",
     *      description="Returns subMenu.",
     *    @OA\Parameter(
     *          name="id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="supplier_id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="position",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="description",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="image",
     *          in="query",
     *          required=false,
     *     @OA\Schema (type="file")
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
    public function updateSubMenu(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request, [
            'id' => 'required|exists:menus,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required',
            'description' => 'required',
            'position' => 'required|numeric'
        ]);
        $res = new Result();
        try {
            $sub_menu = Menu::find($request->id);
            $sub_menu->supplier_id = $request->supplier_id;
            $sub_menu->name = $request->name;
            $sub_menu->description = $request->description;
            $sub_menu->position = $request->position;
            if ($request->file('image')) {
                $name = Str::uuid()->toString() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('public/Products'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Products/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
                $sub_menu->file_id = $file->id;
            }
            $sub_menu->save();
            $sub_menu->refresh();
            $res->success(new MenuResource($sub_menu));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            } else {
                $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * @OA\Post(
     *      path="/update_submenuposition",
     *      operationId="updateSubMenuPosition",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Add subMenu",
     *      description="Returns subMenu.",
     *    @OA\Parameter(
     *          name="id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *
     *    @OA\Parameter(
     *          name="position",
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
    public function updateSubMenuPosition(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request, [
            'id' => 'required|exists:menus,id',
            'position' => 'required|numeric'
        ]);
        $res = new Result();
        try {
            $sub_menu = Menu::find($request->id);
            $sub_menu->position = $request->position;
            $sub_menu->save();
            $sub_menu->refresh();
            $res->success(new MenuResource($sub_menu));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            } else {
                $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @OA\Post(
     *      path="/update_submenu_products",
     *      operationId="updateSubMenuProducts",
     *      tags={"Menu"},
     *     security={{"Authorization":{}}},
     *      summary="Add subMenu",
     *      description="Returns subMenu",
     *    @OA\Parameter(
     *          name="id",
     *          in="query",
     *          required=true,
     *
     *      ),
     *    @OA\Parameter(
     *          name="products",
     *          in="query",
     *          required=true,
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
    public function updateSubMenuProducts(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $this->validate($request, [
            'id' => 'required|exists:menus,id',
            'products.*' => 'required|exists:products,id',
        ]);
        $res = new Result();
        try {
           $menu = Menu::find($request->id);
          $old_products = $menu->products;
          $menu->products()->detach();
          $i = 1;
          $ids = $request->products;
          for($i=0 ; $i < count($ids); $i++){
              $product = Product::find($ids[$i]);
              $menu->products()->attach($product,['position' => $i+1]);
          }
            $menu->save();
          $menu->refresh();
            $res->success(new MenuResource($menu));
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                $res->fail($exception->getMessage());
            } else {
                $res->fail('erreur serveur 500');
            }
        }
        return new JsonResponse($res, $res->code);
    }


}
