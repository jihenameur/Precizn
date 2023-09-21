<?php

namespace App\Http\Controllers;

use App\Models\sub_task;
use Illuminate\Http\Request;
use Validator;

class SubTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sub_tasks = sub_task::all();
        return response()->json([
        "success" => true,
        "message" => " sub task List",
        "data" => $sub_tasks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
        'subtask_name'=> 'required',
        'description' => 'required',
        'status' => 'required',
        'priority' => 'required',
        'start_date'=>'required',
        'end_date'=>'required',
        'task_id'=>'required',
        'user_id',
        'claim'=>''
        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $sub_task = sub_task::create($input);
        return response()->json([
        "success" => true,
        "message" => "Sub Task created successfully.",
        "data" => $sub_task
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\sub_task  $sub_task
     * @return \Illuminate\Http\Response
     */
    public function show( $id)
    {
        $sub_task = sub_task::find($id);
        if (is_null($sub_task)) {
        return $this->sendError('sub_task not found.');
        }
        return response()->json([
        "success" => true,
        "message" => "sub_task retrieved successfully.",
        "data" => $sub_task
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\sub_task  $sub_task
     * @return \Illuminate\Http\Response
     */
    public function edit(sub_task $sub_task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\sub_task  $sub_task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'subtask_name'=>'required',
            'description' => 'required',
            'status' => 'required',
            'priority' => 'required',
            'start_date'=>'required',
            'end_date'=>'required',
            'task_id'=>'required',
            'claim'=>'',
            'user_id'=>''

        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $sub_task = sub_task::findOrFail($id);

        if (!is_null($sub_task)){
        $sub_task->subtask_name= $input['subtask_name'];
        $sub_task->description = $input['description'];
        $sub_task->status = $input['status'];
        $sub_task->priority = $input['priority'];      
        $sub_task->start_date = $input['start_date'];
        $sub_task->end_date = $input['end_date'];
        $sub_task->task_id = $input['task_id'];
        $sub_task->claim = $input['claim'];
        $sub_task->user_id = $input['user_id'];

        }
        $sub_task->save();
        return response()->json([
        "success" => true,
        "message" => "sub task updated successfully.",
        "data" => $sub_task
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\sub_task  $sub_task
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        $sub_task = sub_task::find($id);
        if (!is_null($sub_task)) {
            $sub_task->delete();
            return response()->json([
            "success" => true,
            "message" => "sub_task deleted successfully.",
            "data" => $sub_task
            ]);
        }else{
            return response()->json([ "message" => "sub_task not found.", ]);

     }
    }
}
