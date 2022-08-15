<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\In;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FilterController extends Controller
{
    protected $controller;

    public function __construct(Request $request,  Controller $controller = null, LocationController $locationController)
    {
        $this->locationController = $locationController;
    }
    //get suppliers
    public function FilterFournisseur(Request $request)
    {

        $res = new Result();
        try {
            $Result = [];
            $Populaire = $request->Populaire;
            $delaiLivraison = $request->delaiLivraison;
            $meilleureNote = $request->meilleureNote;
            $prix1 = $request->prix1;
            $prix2 = $request->prix2;
            $array = array();
            $distance = $request->distance;
            $SupplierProche = array();

            $select[] = DB::table('suppliers')->select('*')
                ->join('category_supplier', 'category_supplier.supplier_id', 'suppliers.id')
                ->join('categorys', 'categorys.id', 'category_supplier.category_id')
                ->join('product_supplier', 'product_supplier.supplier_id', 'suppliers.id')
                ->join('products', 'products.id', 'product_supplier.product_id')
                ->when($Populaire, function ($query, $Populaire) {
                    if (!empty($Populaire)) {
                        $supplier = DB::table('suppliers')->max('qantityVente');
                        $query->where('suppliers.qantityVente', '=', $supplier);
                    }
                })
                ->when($delaiLivraison, function ($query, $delaiLivraison) {
                    if (!empty($delaiLivraison)) {
                        $supplier2 = DB::table('suppliers')->min('min_period_time');
                        $query->orWhere('suppliers.min_period_time', '=', $supplier2);
                    }
                })
                ->when($meilleureNote, function ($query, $meilleureNote) {
                    if (!empty($meilleureNote)) {
                        $supplier1 = DB::table('suppliers')->max('star');
                        $query->orWhere('suppliers.star', '=', $supplier1);
                    }
                })
                ->when($prix1, function ($query, $prix1) {
                    if (!empty($prix1) && !empty($prix2)) {
                        $query->orWhereBetween('products.price', [$prix1, $prix2]);
                    }
                })
                ->distinct()
                ->get();
            $supps = [];
            foreach ($select as $supp) {
                array_push($supps, Supplier::find($supp[0]->id));
            }

            //    dd($supps);
            /* foreach($SupplierProche as $proche){
        foreach($select as $Supplier){
         // dd($Supplier);
          $object[] = current($Supplier);
         // dd($proche["supplier"]["id"]);
        if($proche["supplier"]["id"] == $object){

           unset($proche["supplier"]);


         }
         array_push($Result,$SupplierProche,$select) ;


       }
      }

       return $Result;*/
            $Supplierdistance =   $this->locationController->getSuppDistancesSuppliers($supps);
            //dd($Supplierdistance);
            foreach ($Supplierdistance as $obj) {
                if ($obj['distance'] <= $distance)
                    array_push($SupplierProche, $obj);
            }
            //return $SupplierProche;
            $res->success($SupplierProche);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->message);
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
