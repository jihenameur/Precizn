<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmployeeController extends Controller
{   public function __construct(
    Request $request,
    Employee $model,
    Result $res
) {
    $this->request = $request;
    $this->model = $model;
    $this->res = $res;
}
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::all();
        return response()->json([
        "success" => true,
        "message" => " employees List",
        "data" => $employees
        ]);
    }
    public function all($per_page, Request $request)
    {
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:firstName,lastName,id' // complete the akak list
            ]);
            $orderBy = $request->orderBy;
        }
        if ($request->has('orderByType') && $request->orderByType != null) {
            $this->validate($request, [
                'orderByType' => 'required|in:ASC,DESC' // complete the akak list
            ]);
            $orderByType = $request->orderByType;
        }
        $res = new Result();
        try {
            $employess = Employee::orderBy($orderBy, $orderByType)->paginate($per_page);
          
            $res->success($employess);
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
    {        $res = new Result();

       try {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'phone' => 'required',
                'address'=>'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return ($validator->errors());

            }
            $allRequestAttributes = $request->all();
            $role_id = \App\Models\Role::where('short_name', config('roles.backadmin.employee'))->first()->id;
            $user = new User($allRequestAttributes);
            //$user->password = bcrypt($request->password);
            $user->password = bcrypt($request->password);
            $employee = $this->model->create($allRequestAttributes);
            $employee->user()->save($user);

            $role = Role::find($role_id);
            $user->roles()->attach($role);
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);
            $user->token = $token;
            $user->update();
            $employee = [
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
                'address' => $user['address'],
                'role' => $role['id'],
                'post'=>$employee['post'],
            ];
            $response = [
                'Employee' => $employee
            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            else {$res->fail('erreur serveur 500');}
        }
        return new JsonResponse($res, $res->code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
            if (is_null($user)) {
            return $this->sendError('User not found.');
            }
            return response()->json([
            "success" => true,
            "message" => "User retrieved successfully.",
            "data" => $user
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone'=>'required',
            'status'=>'required',
            'email'=>'required',
            'password'=>'required',
            'address'=>'required',

        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $user = User::findOrFail($id);

        if (!is_null($user)){
        $user->first_name = $input['first_name'];
        $user->last_name = $input['last_name'];
        $user->phone = $input['phone'];      
        $user->status = $input['status'];
        $user->email = $input['email'];
        $user->password = $input['password'];
        $user->address = $input['address'];

        }
        $user->save();
        return response()->json([
        "success" => true,
        "message" => "User updated successfully.",
        "data" => $user
        ]);
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
        $user = User::find($id);
        if (!is_null($user)) {
            $user->delete();
            return response()->json([
            "success" => true,
            "message" => "user deleted successfully.",
            "data" => $user
            ]);
        }else{
            return response()->json([ "message" => "user not found.", ]);

        }
    }
}
