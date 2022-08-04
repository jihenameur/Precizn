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

class CategoryController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Category $model,  Controller $controller = null)
    {
        $this->model = $model;
    }

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
                'name' => 'required',
                'parent_id' => 'required',
                'order_id' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            } else {

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
            }

            $res->success($response);
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
    public function getCategoryChildren(Request $request, $id)
    {
        $res = new Result();
        try {
            $category = Category::findOrFail($id);
            $children = $category->children;

            $res->success($children);
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
    public function getCategoryParent(Request $request, $id)
    {
        $res = new Result();
        try {
            $category = Category::findOrFail($id);
            $parent = $category->parent;
            $res->success($parent);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * Filter or get all
     *
     * @return Collection|Model[]|mixed|void
     */
    public function all($per_page = 10, Request $request)
    {
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            // $supplier = Supplier::all();
            $categorys = Category::paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $categorys = $this->getFilterByKeywordClosure($keyword);
            }
            $res->success($categorys);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function allCategoryParent(Request $request)
    {
        $res = new Result();
        try {
            $categorys = Category::where("parent_id", 0)
                ->get();
            $res->success($categorys);
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
        $category = Category::where('name', 'like', "%$keyword%")
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
    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
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
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

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
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        /** @var Category $category */
        $res = new Result();
        $category = Category::find($id);

        try {
            $category->delete();
            $res->success($category);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
