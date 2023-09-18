<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($per_page, Request $request)
    {
        $projects = Project::paginate($per_page);
        return response()->json([
        "success" => true,
        "message" => " projects List",
        "data" => $projects
        ]);
    }
    public function allProjects()
    {
        $projects = Project::all();
        return response()->json([
        "success" => true,
        "message" => " projects List",
        "data" => $projects
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
        'tax_registration' => 'required',
        'social_reason' => 'required',
        'manager_name' => 'required',
        'email' => 'required',
        'phone'=>'required',
        'password'=>'required',
        'domain'=>'required'
        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $project = Project::create($input);
        return response()->json([
        "success" => true,
        "message" => "Project created successfully.",
        "data" => $project
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show( $id)
    {
        $project = Project::find($id);
        if (is_null($project)) {
        return $this->sendError('Project not found.');
        }
        
        return response()->json([
        "success" => true,
        "message" => "project retrieved successfully.",
        "data" => $project
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'tax_registration' => 'required',
            'social_reason' => 'required',
            'manager_name' => 'required',
            'email' => 'required',
            'phone'=>'required',
            'domain'=>'required',
            'password'=>'required'

        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $project = Project::findOrFail($id);
        if (!is_null($project)){
        $project->social_reason = $input['social_reason'];
        $project->manager_name = $input['manager_name'];
        $project->tax_registration = $input['tax_registration'];
        $project->phone = $input['phone'];      
        $project->email = $input['email'];
        $project->domain = $input['domain'];
        $project->password = $input['password'];

        }
        $project->save();
        return response()->json([
        "success" => true,
        "message" => "project updated successfully.",
        "data" => $project
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        $project = Project::find($id);
        if (!is_null($project)) {
            $project->tasks()->each(function ($task) {
                $task->sub_tasks()->delete();
            });
            $project->tasks()->delete();
            $project->delete();
            return response()->json([
            "success" => true,
            "message" => "project deleted successfully.",
            "data" => $project
            ]);
        }else{
            return response()->json([ "message" => "project not found.", ]);

        }
    }
}
