<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Annonces;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Tag(
 *     name="Annonce",
 *     description="Gestion annonce",
 *
 * )
 */
class AnnonceController extends Controller
{
     /**
     * @OA\Post(
     *      path="/createAnnonce",
     *      operationId="createAnnonce",
     *      tags={"Annonce"},
     *     security={{"Authorization":{}}},
     *      summary="create Annonce" ,
     *      description="create Annonce",
     *    @OA\Parameter (
     *     in="query",
     *     name="description",
     *     required=false,
     *     description="description",
     *     @OA\Schema (type="text")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="supplier_id ",
     *     required=true,
     *     description="supplier_id ",
     *     @OA\Schema (type="integer",
     *           format="bigint(20)")
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
    public function __construct(Request $request, Annonces $model,  Controller $controller = null)
    {
        $this->model = $model;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllAnnonces()
    {
        if(!Auth::user()->isAuthorized(['admin','client'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $client =  Auth::user();
            $annnonces = Annonces::all();
            // dd($client);
            if ($client["role_id"] == 5) {
                $tous_annnonces = [];
                foreach ($annnonces as $annonce) {
                    $showAnnonce = $client->userable["firstname"] . "," . $annonce['description'];

                    array_push($tous_annnonces, $showAnnonce);
                }
                $res->success($tous_annnonces);
            } else {
                $res->success($annnonces);
            }
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
                "description" => "required"
            ]);
            if ($validator->fails()) {
                return ($validator->errors());

                //return back()->withInput()->withErrors($validator);
            }
                $annonce = new Annonces($request->all());
                $annonce = $this->model->create($request->all());
                $annonce->save();

            $res->success($annonce);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/showAnnonce/{id}",
     *     tags={"Annonce"},
     *     security={{"Authorization":{}}},
     *      operationId="showAnnonce",
     *      summary="Get annonce by annonce id. ",
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
    public function show($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier','client'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $annonce = Annonces::find($id);
            $res->success($annonce);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $allRequestAttributes = $request->all();
            $annonce = Annonces::find($id);
            $annonce->fill($allRequestAttributes);
            $annonce->update();
            $res->success($annonce);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Auth::user()->isAuthorized(['admin','supplier'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $annonce = Annonces::find($id);
            $annonce->delete();
            $res->success('deleted');
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    // effaser tout annonces
    public function destroyAllAnnonces()
    {
        if(!Auth::user()->isAuthorized(['admin'])){
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ],403);
        }
        $res = new Result();
        try {
            $annonces = Annonces::All();
            foreach ($annonces as $annonce) {
                $annonce->delete();
            }
            $res->success('destroyed');
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
    }
}
