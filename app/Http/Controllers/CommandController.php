<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Command;
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
    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                // 'supplier_id' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());
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
            $toUser->notify(new CommandNotification($command, $fromUser));
            $admins=Admin::all();
            foreach ($admins as $key => $value) {
                $value->notify(new CommandNotification($command, $fromUser));
            }
            $res->success($command);
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
    public function all(Request $request, $per_page)
    {
        //  $this->validate($request, [
        //   //   'status' => 'required',
        //      'from' => 'date',
        //      'to' => 'date'
        //  ]);
        $res = new Result();

        try {
            $orderBy = 'date';
            $orderByType = "ASC";
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
            $res->fail($exception->getMessage());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getCommandPanier($id)
    {
        $res = new Result();
        try {
            $command = Command::find($id);
            $panier = $this->panierController->getPanier($command->panier_id);

            //return $panier;
            $res->success($panier);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getCommand($id)
    {
        $res = new Result();
        try {
            $command = Command::find($id);
            $res->success($command);
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
                throw new Exception($validator->errors());

                // return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function commandStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            throw new Exception($validator->errors());
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function CommandAssignedAdmin($id)
    {
        $res = new Result();
        try {
            $admin = Admin::where('id', auth()->user()->userable_id)->first();
            $command = Command::find($id);
            $command->admin_id=$admin->id;
            $command->update();
            $res->success($command);
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
        $res = new Result();
        try {
            /** @var Command $command */
            $command = Command::find($id);
            $command->products()->detach();
            $command->suppliers()->detach();
            $command->address()->detach();
            $command->delete();
            $res->success($command);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
