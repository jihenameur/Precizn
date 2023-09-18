<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\sub_task;
use App\Models\task;
use Illuminate\Http\Request;
use Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = task::all();
        return response()->json([
        "success" => true,
        "message" => " sub task List",
        "data" => $tasks
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
        'task_name'=>'required',
        'description' => 'required',
        'status' => 'required',
        'priority' => 'required',
        'start_date'=>'required',
        'end_date'=>'required',
        'project_id'=>'required',
        'remart'=>''
        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $task = task::create($input);
        return response()->json([
        "success" => true,
        "message" => "Task created successfully.",
        "data" => $task
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\task  $task
     * @return \Illuminate\Http\Response
     */
    public function show( $id)
    {
        $task = sub_task::find($id);
            if (is_null($task)) {
            return $this->sendError('task not found.');
            }
            return response()->json([
            "success" => true,
            "message" => "task retrieved successfully.",
            "data" => $task
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'task_name'=>'required',
            'description' => 'required',
            'status' => 'required',
            'priority' => 'required',
            'start_date'=>'required',
            'end_date'=>'required',
            'project_id'=>'required',
            'remart'=>''

        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $task = task::findOrFail($id);

        if (!is_null($task)){
        $task->task_name=$input['task_name']       ;
        $task->description = $input['description'];
        $task->status = $input['status'];
        $task->priority = $input['priority'];      
        $task->start_date = $input['start_date'];
        $task->end_date = $input['end_date'];
        $task->remark = $input['remark'];
        $task->project_id = $input['project_id'];

        }
        $task->save();
        return response()->json([
        "success" => true,
        "message" => "task updated successfully.",
        "data" => $task
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        $task = task::find($id);
        if (!is_null($task)) {
            $task->delete();
            return response()->json([
            "success" => true,
            "message" => "task deleted successfully.",
            "data" => $task
            ]);
        }else{
            return response()->json([ "message" => "task not found.", ]);

        }
    }
    /**
     * function get tasks with project id 
    */
    public function TaskwhereProject($id_project){
        $project = Project::where('id', $id_project)->first();
        $task = task::whereIn('project_id', [$project->id])->get();
          if (is_null($task)) {
            return $this->sendError('task not found.');
            }
            return response()->json([
            "success" => true,
            "message" => "task retrieved successfully.",
            "data" => $task
            ]);


    }
}
