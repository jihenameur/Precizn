<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Tag $model,  Controller $controller = null)
    {
        $this->model = $model;
    }

    public function create(Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }
                $tag = new Tag();
                $tag->name = $request->name;
                $tag->save();

                $response['tag'] = [
                    "id"         =>  $tag->id,
                    "name"     =>  $tag->name
                ];


            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
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
        $tag = Tag::where('name', 'like', "%$keyword%")
           ->orderBy($orderBy, $orderByType) ->get();
        return $tag;
    }
    public function getAllTags($per_page, Request $request)
    {
        // if(!Auth::user()->isAuthorized(['admin','supplier'])){
        //     return response()->json([
        //         'success' => false,
        //         'massage' => 'unauthorized'
        //     ],403);
        // }
        $res = new Result();
        try {
            $orderBy = 'created_at';
            $orderByType = "DESC";
            if($request->has('orderBy') && $request->orderBy != null){
                $this->validate($request,[
                    'orderBy' => 'required|in:name' // complete the akak list
                ]);
                $orderBy = $request->orderBy;
            }
            if($request->has('orderByType') && $request->orderByType != null){
                $this->validate($request,[
                    'orderByType' => 'required|in:ASC,DESC' // complete the akak list
                ]);
                $orderByType = $request->orderByType;
            }
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $tags = Tag::orderBy($orderBy, $orderByType)->paginate($per_page);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                $tags = $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $res->success($tags);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getTagByid($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $tag = Tag::find($id);
            $res->success($tag);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getSupplierTags($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier','client'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $ids = [];
        $tags = [];
        $res = new Result();
        try {
            $products = Product::whereHas('suppliers', function ($q) use ($id) {
                $q->where('supplier_id', $id);
            })->get();
            foreach ($products as $key => $product) {
                foreach ($product->tag as $key => $tag) {
                    if (!in_array($tag->id, $ids)) {
                        array_push($ids, $tag->id);
                        array_push($tags, $tag);
                    }
                }
            }

            $res->success(array_unique($tags));
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function update($id, Request $request)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $tag = Tag::find($id);
            $tag->name = $request->name;
            $tag->update();

            $response['tag'] = [
                "id"         =>  $tag->id,
                "name"     =>  $tag->name
            ];


            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function delete($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $tag = Tag::find($id);
            $tag->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
