<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Tag(
 *     name="Category",
 *     description="Gestion category",
 *
 * )
 */
class CategoryController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Category $model,  Controller $controller = null)
    {
        $this->model = $model;
    }
/**
     * @OA\Post(
     *      path="/addCategory",
     *      operationId="addCategory",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="create category" ,
     *      description="create category",
     *     @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="parent_id",
     *     required=true,
     *     description="parent_id",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="order_id",
     *     required=true,
     *     description="order_id",
     *     @OA\Schema (type="string")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=true,
     *     description="description",
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
                'name' => 'required',
                'parent_id' => 'required',
                'order_id' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }

            $category = new Category();
            $category->name = $request->name;
            $category->parent_id = $request->parent_id;
            $category->order_id = $request->order_id;
            $category->description = $request->description;
            $category->save();

            $response['category'] = [
                "id"         =>  $category->id,
                "name"     =>  $category->name,
                "parent_id"     =>  $category->parent_id,
                "order_id"     =>  $category->order_id,
                "description"     =>  $category->description,
            ];


            $res->success($response);
        } catch (\Exception $exception) {
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
     *      path="/getCategoryChildren/{id}",
     *     tags={"Category"},
     *     security={{"Authorization":{}}},
     *      operationId="getCategoryChildren",
     *      summary="Get category per id with children here. ",
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
     *     @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *     @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     * )
     */
    public function getCategoryChildren(Request $request, $id)
    {
        $res = new Result();
        try {
            $category = Category::findOrFail($id);
            $children = $category->children;

            $res->success($children);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
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
     *      path="/getCategoryParent/{id}",
     *     tags={"Category"},
     *     security={{"Authorization":{}}},
     *      operationId="getCategoryParent",
     *      summary="Get category per id with parent here. ",
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
     *     @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *     @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     * )
     */
    public function getCategoryParent(Request $request, $id)
    {
        $res = new Result();
        try {
            $category = Category::find($id);
            $parent = $category->parent;
            $res->success($parent);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    public function getCategorysupplier($id, $per_page)
    {
        $res = new Result();
        try {
            $suppliers = Supplier::whereHas('categorys', function ($q) use ($id) {
                $q->where('category_id', $id);
            })->paginate($per_page);

            $res->success($suppliers);
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
     *      path="/getCategorysupplierDelivery/{id}/{per_page}",
     *      operationId="getCategorysupplierDelivery",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="Get the supplier with the Category ID && delivered.",
     *      description="Returns all  supplier with the Category ID && delivered.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
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
     *  @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getCategorysupplierDelivery($id, $per_page)
    {
        $res = new Result();
        try {
            $suppliers = Supplier::whereHas('categorys', function ($q) use ($id) {
                $q->where('category_id', $id);
            })
                ->Where('delivery', '=', 1)
                ->paginate($per_page);
            $res->success($suppliers);
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
     *      path="/getCategorysupplierTakeaway/{id}/{per_page}",
     *      operationId="getCategorysupplierTakeaway",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="Get the supplier with the Category ID && OR delivered && OR to Take away.",
     *      description="Returns all  supplier with the Category ID && OR delivered && OR to Take away.",
     *    @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
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
     *  @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getCategorysupplierTakeaway($id, $per_page)
    {
        $res = new Result();
        try {
            $suppliers = Supplier::whereHas('categorys', function ($q) use ($id) {
                $q->where('category_id', $id);
            })
                ->Where('take_away', '=', 1)
                ->paginate($per_page);
            $res->success($suppliers);
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
     *      path="/getCategory/{per_page}",
     *      operationId="getCategory",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of categories",
     *      description="Returns all categories and associated provinces.",
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
    public function all($per_page = 10, Request $request)
    {

        $res = new Result();
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:firstName,lastName,id' // complete the akak list
            ]);
            $orderBy = $request->orderBy;
        }
        if ($request->has('orderByType') && $request->orderByType != null) {
            $this->validate($request, [
                'orderByType' => 'required|in:ASC,DESC' // complete the akak list
            ]);
            $orderByType = $request->orderByType;
        }
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            // $supplier = Supplier::all();
            $categorys = Category::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $categorys = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success($categorys);
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
     *      path="/allCategoryParent",
     *      operationId="allCategoryParent",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="Get the list of categories in which the parent ID exists. ",
     *      description="Returns all categories in which the parent ID exists.",
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
    public function allCategoryParent(Request $request)
    {
        $res = new Result();
        try {
            $categorys = Category::where("parent_id", 0)
                ->get();
            $res->success($categorys);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
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
        $category = Category::where('name', 'like', "%$keyword%")
            ->orderBy($orderBy, $orderByType)
            ->get();
        return $category;
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
     *      path="/updateCategory/{id}",
     *      operationId="updateCategory",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="update category ",
     *      description="update category",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *         @OA\Parameter (
     *     in="query",
     *     name="name",
     *     required=true,
     *     description="name",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="parent_id",
     *     required=true,
     *     description="parent_id",
     *     @OA\Schema (type="string")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="order_id",
     *     required=true,
     *     description="order_id",
     *     @OA\Schema (type="string")
     *      ),
     * *     @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=false,
     *     description="description",
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
        $res = new Result();
        $category = Category::find($id);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'parent_id' => 'required',
                'order_id' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            } else {
                $category->name = $request->name;
                $category->parent_id = $request->parent_id;
                $category->order_id = $request->order_id;
                $category->description = $request->description;
                $category->update();

                $response['category'] = [
                    "id"         =>  $category->id,
                    "name"     =>  $category->name,
                    "parent_id"     =>  $category->parent_id,
                    "order_id"     =>  $category->order_id,
                    "description"     =>  $category->description,
                ];
            }

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
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
     *      path="/deleteCategory/{id}",
     *      operationId="deleteCategory",
     *      tags={"Category"},
     *     security={{"Authorization":{}}},
     *      summary="delete category",
     *      description="delete one category.",
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
        /** @var Category $category */
        $res = new Result();
        $category = Category::find($id);
        try {
            $category->delete();
            $res->success('Deleted');
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
}
