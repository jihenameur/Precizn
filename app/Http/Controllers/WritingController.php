<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Writing;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Contracts\Service\Attribute\Required;

class WritingController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Writing $model,  Controller $controller = null)
    {
        $this->model = $model;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll($per_page)
    {
        $writing = Writing:: paginate($per_page);
        return $writing;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {




    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeWriting(Request $request)
    {

        $validator = Validator::make($request->all(), [
               "note"=>"required|max:5",

               "comment"=>"required",
               "client_id"=>"required",
               "supplier_id"=>"required"

        ]);
        if ($validator->fails())
        {
            return back()->withInput()->withErrors($validator);

        } else {
            if($request->note > 5 | $request->note < 1){
                   return "note doit être inférieur à 5 et supérieur à 1";
            }else{
            $writing = new Writing($request->all());
           $writing = $this->model->create($request->all());
           $writing->save();
            }
        }
        if($writing->save()){
            $supplier = Supplier::find( $writing->supplier_id);
            if($supplier) {
                $TotalNote=[];
                $WritingTotal = DB::table('writings')->select()
                ->where('writings.supplier_id','=',$writing->supplier_id)
                ->count();
                $WritingGlobal = DB::table('writings')->select()
                ->where('writings.supplier_id','=',$writing->supplier_id)
                ->distinct()
                ->get();
                 foreach($WritingGlobal as $obj){
                    array_push($TotalNote,$obj->note);
                 }
                $Note= array_sum($TotalNote);
                $supplier->star = $Note   /  $WritingTotal ;
                $supplier->update();
            }
        }
        return $writing;

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $writing = Writing::find($id);
        return $writing;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

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
        $allRequestAttributes = $request->all();
        $writing = Writing::find($id);
        $writing->fill($allRequestAttributes);
        $writing->update();
        return $writing;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $writing = Writing::find($id);
        $writing->delete();
        return 'deleted';

    }
}
