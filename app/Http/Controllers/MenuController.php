<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Menu $model,  Controller $controller = null)
    {
        $this->model = $model;
    }

    public function create(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                // 'price' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                //return $validator->errors();
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }

            $menu = new Menu();
            $menu->name = $request->name;
            $menu->description = $request->description;
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Menu'), $filename);
                $menu['image'] = $filename;
            }

            $menu->save();

            $res->success($menu);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getMenuProducts($id, $per_page)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $products = Product::whereHas('menu', function ($q) use ($id) {
                $q->where('menu_id', $id);
            })->paginate($per_page);
            $res->success($products);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getMenuByid($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $menu = Menu::find($id);
            $res->success($menu);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function update($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());
        }
        $res = new Result();
        try {
            $menu = Menu::find($id);

            $menu->name = $request->name;
            $menu->description = $request->description;
            if ($request->file('image')) {
                $file = $request->file('image');
                $filename = $file->getClientOriginalName();
                //dd( $filename);

                $file->move(public_path('public/Menu'), $filename);
                $menu['image'] = $filename;
            }
            $menu->update();

            $res->success($menu);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function delete($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $menu = Menu::find($id);
            $menu->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
