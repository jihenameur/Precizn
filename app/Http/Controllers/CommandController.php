<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Http\Resources\Command\CommandResource;
use App\Jobs\Admin\SendCommandAdminNotification;
use App\Jobs\Admin\SendCommandSupplierNotification;
use App\Jobs\SendCommandSupplierNotification as JobsSendCommandSupplierNotification;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Command;
use App\Models\Coupon;
use App\Models\Delivery;
use App\Models\Panier;
use App\Models\Product;
use App\Models\Supplier;
use App\Notifications\CommandNotification;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Tag(
 *     name="Commande",
 *     description="Gestion Commande ",
 *
 * )
 */
class CommandController extends Controller
{
    protected $controller;

    public function __construct(
        Request $request,
        Command $model,
        Controller $controller = null,
        LocationController $locationController,
        PanierController $panierController
    ) {
        $this->model = $model;
        $this->locationController = $locationController;
        $this->panierController = $panierController;
    }
    /**
     * @OA\Post(
     *      path="/addCommand",
     *      operationId="addCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="create commande" ,
     *      description="create command",
     *     @OA\Parameter (
     *     in="query",
     *     name="date",
     *     required=true,
     *     description="date",
     *    @OA\Schema(
     *           type="string",
     *           format="date-time"
     *        ),
     *
     *
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="client_id",
     *     required=true,
     *     description="client id",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="supplier_id ",
     *     required=true,
     *     description="supplier id ",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="addresse_id ",
     *     required=true,
     *     description="addresse id ",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="tip",
     *     required=false,
     *     description="tip",
     *     @OA\Schema (type="decimal(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="mode_pay",
     *     required=true,
     *     description="mode de paiment",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="codepromo",
     *     required=true,
     *     description="codepromo",
     *     @OA\Schema (type="integer")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request. User ID must be an integer and bigger than 0",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   )
     */
    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }

            $command = new Command();
            $command->date = $request->date;
            $command->tip = $request->tip;
            $command->mode_pay = $request->mode_pay;
            $command->status = 0;
            $command->codepromo = $request->codepromo;
            //$supplier = Supplier::find($request->supplier_id);
            $client =  Auth::user();
            // dd($client->userable->id);
            $command->client_id = $client->userable->id;
            // $delivery = Delivery::find($request->delivery_id);
            // $command->delivery_id = $delivery->id;
            $panier = Panier::find($request->panier_id);

            $command->panier_id = $panier->id;
            $command->total_price = $panier->price + $request['tip'] + $request['delivery_price'];
            $command->addresse_id = $request->addresse_id;
            $client = Client::find($command->client->id);
            $address = Address::find($command->addresse_id);
            if ($address) {
                $command->lat = $address->lat;
                $command->long = $address->long;
            } else {
                $address = ["lat" => $request->lat, "long" => $request->long,];
                $command->lat = $request->lat;
                $command->long = $request->long;
            }
            // foreach ($panier->products as $key => $prod) {
            $product = Product::find($panier->products[0]->id);
            $suppl = Supplier::whereHas('products', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->first();
            $command->supplier_id = $suppl->id;
            // foreach ($panier->products as $key => $prod) {

            // $supplier = Supplier::find($suppl[0]->id);
            // $command->suppliers()->attach($supplier);
            // }
            // foreach ($request->suppliers as $key => $supp) {
            //     $supplier = Supplier::find($supp['id']);
            //     $command->suppliers()->attach($supplier);
            // }
            /*foreach ($request->address as $key => $address) {

                $client = Client::find($address['idclient']);
                $command->address()->attach($client, ['name' => $address['addressName']]);
            }*/

            // $client = Client::find($addDeliveryControllerress['idclient']);
            // $command->address()->attach($client->userable, ['name' => $address['addressName']]);
            // }
            $suppliers = [];
            array_push($suppliers, $suppl);
            if ($request['delivery'] == 1) {
                $distance = $this->locationController->getdistances($address, $suppliers);
                if ($distance[0]['deliveryprice'] > 0) {
                    $command->delivery_price = $distance[0]['deliveryprice'];
                    $command->total_price = $panier->price + $request['tip'] + $distance[0]['deliveryprice'];
                    $command->distance = $distance[0]['distance'];
                } else {
                    $command->delivery_price = 0;
                    $command->total_price = $panier->price + $request['tip'];
                }
            }
            $command->total_price_coupon = $command->total_price;

            $command->save();

            if ($command->save()) {
                $supplier = Supplier::find($command->supplier_id);
                if ($supplier) {
                    $supplier->qantityVente =  $supplier->qantityVente + 1;
                    $supplier->update();
                }
            }

            $fromUser = Client::find(auth()->user()->userable_id);
            $toUser  = Supplier::find($command->supplier_id);
            SendCommandAdminNotification::dispatch($command,$fromUser);
            JobsSendCommandSupplierNotification::dispatch($command,$fromUser,$toUser);

            $res->success($command);
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
     *      path="/getAllCommand/{per_page}",
     *      operationId="getAllCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="Get List Of commandes",
     *      description="Returns all commades and associated provinces.",
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
    public function all(Request $request, $per_page)
    {
        //  $this->validate($request, [
        //   //   'status' => 'required',
        //      'from' => 'date',
        //      'to' => 'date'
        //  ]);
        $res = new Result();
        $orderBy = 'date';
        $orderByType = "DESC";
        if($request->has('orderBy') && $request->orderBy != null){
            $this->validate($request,[
                'orderBy' => 'required|in:date,id' // complete the akak list
            ]);
            $orderBy = $request->orderBy;
        }
        if($request->has('orderByType') && $request->orderByType != null){
            $this->validate($request,[
                'orderByType' => 'required|in:ASC,DESC' // complete the akak list
            ]);
            $orderByType = $request->orderByType;
        }
        try {

            $keyword = $request->has('keyword') ? $request->get('keyword') : null;

            $status = $request->status ?? 'null';
            $from = $request->from ?? 'null';
            $to = $request->to ?? 'null';
            $from = new DateTime($from == 'null' ? '2000-01-01' : $from);
            $to = new DateTime($to == 'null' ? Date::now() : $to);
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return $this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType);
            }
            $commands = Command::where('status', 'like', '%' . ($status == 'null' ? '' : $status) . '%')
                ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])->orderBy($orderBy, $orderByType)->paginate($per_page);

            $res->success($commands);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }


    private function getFilterByKeywordClosure($keyword, $orderBy, $orderByType)
    {
        $res = new Result();

        try {
            $commands = Command::whereHas('client', function ($q) use ($keyword) {
                $q->where('lastname', 'like', "%$keyword%");
                $q->orWhere('firstname', 'like', "%$keyword%");
            })
                ->orWhereHas('supplier', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                })
                ->orWhereHas('delivery', function ($q) use ($keyword) {
                    $q->where('lastName', 'like', "%$keyword%");
                    $q->orWhere('firstName', 'like', "%$keyword%");
                })
                ->orderBy($orderBy, $orderByType)
                ->get();

            $res->success($commands);
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
     *      path="/getCommandPanier/{id}",
     *      operationId="getCommandPanier",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="Get commande in panier by commande id.",
     *      description="Returns  commande in panier by commande id.",
     *    @OA\Parameter(
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
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getCommandPanier($id)
    {
        $res = new Result();
        try {
            $command = Command::find($id);
            $panier = $this->panierController->getPanier($command->panier_id);

            //return $panier;
            $res->success($panier);
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
     *      path="/getCommand/{id}",
     *      operationId="getCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="Get commande  by commande id.",
     *      description="Returns  commande  by commande id.",
     *    @OA\Parameter(
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
     * @OA\Response(
     *      response=500,
     *      description="erreur serveur 500"
     *   ),
     *  )
     */
    public function getCommand($id)
    {
        $res = new Result();
        try {
            $command = Command::find($id);
            $res->success($command);
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
     /**
     * @OA\Get(
     *      path="/getCommandsByKeyClientDelivery",
     *      operationId="getCommandsByKeyClientDelivery",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="Get commande  where key client.",
     *      description="Returns  commande  by key client.",
     *     @OA\Parameter (
     *     in="query",
     *     name="id",
     *     required=true,
     *     description="Identifiant client",
     *     @OA\Schema (type="integer")
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
    public function getCommandsByKeyClientDelivery(Request $request)
    {
        $id = $this->cleanKeywordSpaces($request->id);

        $client = Command::whereHas('client', function ($q) use ($id) {
            $q->where('id', $id);
        })
            ->get();
        $delivery = Command::whereHas('delivery', function ($q) use ($id) {
            $q->where('id', $id);
        })
            ->get();
        // $address = Address::where('client_id', $id)
        //     ->get();
        return ['client', $client, 'delivery', $delivery];
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Command|mixed|void
     */
    /**
     * @OA\Put(
     *      path="/updateCommand/{id}",
     *      operationId="updateCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="update commande" ,
     *      description="update command",
     *  @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="date",
     *     required=true,
     *     description="date",
     *    @OA\Schema(
     *           type="string",
     *           format="date-time"
     *        ),
     *
     *
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="client_id",
     *     required=true,
     *     description="client id",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="supplier_id ",
     *     required=true,
     *     description="supplier id ",
     *     @OA\Schema (type="integer")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="addresse_id ",
     *     required=true,
     *     description="addresse id ",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="tip",
     *     required=false,
     *     description="tip",
     *     @OA\Schema ( type="integer",format="decimal(8,2)")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="mode_pay",
     *     required=true,
     *     description="mode de paiment",
     *     @OA\Schema (type="integer")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="codepromo",
     *     required=true,
     *     description="codepromo",
     *     @OA\Schema (type="integer")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request. User ID must be an integer and bigger than 0",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *   )
     */
    public function update($id, Request $request)
    {
        $res = new Result();
        try {
            /** @var Command $command */
            $allRequestAttributes = $request->all();
            $command = Command::find($id);

            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'client_id' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $client = Client::find($request->client_id);
            $command->client_id = $client->id;
            $delivery = Delivery::find($request->delivery_id);
            $command->delivery_id = $delivery->id;
            $panier = Panier::find($request->panier_id);
            $command->panier_id = $panier->id;
            $command->total_price = $panier->price + $request['tip'] + $request['delivery_price'];
            $command->fill($allRequestAttributes);
            $command->update();
            // foreach ($request->products as $key => $prod) {
            $product = Product::find($panier->products[0]->id);
            // $command->products()->detach();
            $supplier = Supplier::whereHas('products', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->get();
            $supplier = Supplier::find($supplier[0]->id);
            $command->suppliers()->detach();
            $command->suppliers()->attach($supplier);
            // }
            // foreach ($request->suppliers as $key => $supp) {
            //     $supplier = Supplier::find($supp['id']);
            //     $command->suppliers()->detach();
            //     $command->suppliers()->attach($supplier);
            // }
            foreach ($request->address as $key => $address) {
                $client = Client::find($address['idclient']);
                $command->address()->detach();
                $command->address()->attach($client, ['name' => $address['addressName']]);
            }

            //return $command;
            $res->success($command);
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
     *      path="/commandStatus",
     *      operationId="commandStatus",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="Get commande by status.",
     *    @OA\Parameter (
     *     in="query",
     *     name="status",
     *     required=true,
     *     description="status",
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
    public function commandStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            /** @var Command $command */
            $command = Command::find($id);
            $command->status = $request->status;
            $command->update();
            // return $command;
            $res->success($command);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }
    public function CommandAssignedAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'command_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $admin = Admin::where('id', auth()->user()->userable_id)->first();
            $command = Command::find($request->command_id);
            $command->admin_id=$admin->id;
            $command->update();
            $res->success($command);
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
    /** @OA\Delete(
        *      path="/deleteCommand/{id}",
        *      operationId="deleteCommand",
        *      tags={"Commande"},
        *     security={{"Authorization":{}}},
        *      summary="delete commande",
        *      description="delete one commande.",
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
        $res = new Result();
        try {
            $command = Command::find($id);

            $command->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /** @OA\Post(
     *      path="/validatecommand",
     *      operationId="validateCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="validate commande",
     *      description="validate commande.",
     *     @OA\Parameter(
     *          name="command_id",
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
     *  )
     */
    public function validateCommand(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $this->validate($request,[
           'command_id' => 'required|exists:commands,id'
        ]);
        $res = new Result();
        try {

            $command = Command::find($request->command_id);
            $command->cycle = 'VERIFY';
            $command->save();
            $res->success($command);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);


    }

    /** @OA\Post(
     *      path="/authorizecommand",
     *      operationId="AuthorizeCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="authoeize commande",
     *      description="authorize commande.",
     *     @OA\Parameter(
     *          name="command_id",
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
     *  )
     */
    public function AuthorizeCommand(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin','supplier'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $this->validate($request,[
            'command_id' => 'required|exists:commands,id'
        ]);
        $res = new Result();
        try {

            $command = Command::find($request->command_id);
            $command->cycle = 'AUTHORIZED';
            $command->save();
            $res->success($command);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);


    }

    /** @OA\Post(
     *      path="/progressingcommand",
     *      operationId="ProgressingCommand",
     *      tags={"Commande"},
     *     security={{"Authorization":{}}},
     *      summary="progressing commande",
     *      description="progressing commande.",
     *     @OA\Parameter(
     *          name="command_id",
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
     *  )
     */
    public function ProgressingCommand(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin','delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $this->validate($request,[
            'command_id' => 'required|exists:command,id'
        ]);
        $res = new Result();
        try {

            $command = Command::find($request->command_id);
            $command->cycle = 'INPROGRESS';
            $command->save();
            $res->success($command);
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);


    }

    public function fetchAll()
    {
        $res = new Result();
        try {

            $commands = Command::whereNotiN('cycle',['SUCCESS','REJECTED'])->orderBy('created_at','ASC')->get();
            $res->success(CommandResource::collection($commands));
        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    public function createCommand(Request $request)
    {
        $this->validate($request,[
           "supplier_id" => "required|exists:suppliers,id",
           "addresse_id" => "exists:addresses,id",
           "delivery_price" => "required|numeric",
           "mode_pay" => "required",
            "total_price" => "required",
            "products" =>"required",
            "products.*.product_id" => "required|exists:products,id",
            "products.*.quantity" => "required|numeric",
            "tip" => "numeric",
            "total_price_coupon" => "numeric",
        ]);

        $res = new Result();
        if((!$request->has('addresse_id')) && (!$request->has('long')) && (!$request->has('lat')) ){
            $res->fail("addresse_id ou long and lat are mandatory");
            return new JsonResponse($res, $res->code);
        }


        try {
            $command = new Command();
            $command->date = now();
            $command->supplier_id = $request->supplier_id;
            $command->client_id = Client::find(auth()->user()->userable_id)->id;
            if($request->has("tip")){
                $command->tip = $request->tip;
            }
            $command->delivery_price = $request->delivery_price;
            $command->mode_pay = $request->has("mode_pay") ? $request->mode_pay : 0;
            $command->total_price = $request->has("total_price") ? $request->total_price : 0;
            $command->total_price_coupon = $request->has("total_price_coupon") ? $request->total_price_coupon : 0;
            if($request->has('addresse_id')){
                $command->addresse_id = $request->addresse_id;
                $command->lat = 0;
                $command->long = 0;
            }else{
                $command->lat = $request->lat;
                $command->long = $request->long;
            }
            $command->save();
            $total = 0;
            foreach ($request->products as $item){
                $product = Product::find($item["product_id"]);
                $command->products()->attach($product,["quantity" => $item["quantity"]]);
                $total += $product->default_price * ($product->unit_limit * $item["quantity"] );
            }
            $command->total_price = $total;
            if($request->has("codepromo")){
                $coupon = Coupon::where('code_coupon',$request->codepromo)->get()->first();
                if($coupon){
                    if($coupon->type == 'amount'){
                        $command->total_price_coupon = $command->total_price - $coupon->value;
                    }else{
                        $command->total_price_coupon = $command->total_price - (($command->total_price * $coupon->value )/100);
                    }
                }
            }
            $command->save();

            $res->success($command);


        } catch (\Exception $exception) {
            if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);



    }
}
