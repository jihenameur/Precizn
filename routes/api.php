<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubTaskController;
use App\Models\User;
use App\Http\Controllers\EmployeeController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Auth::routes(['verify' => true]);
\Illuminate\Support\Facades\Broadcast::routes();
/*
Route::post('/broadcasting/auth', function (Request $request){
    return Auth::user();
})->middleware('jwt.verify'); */
/**
 * public api
 */
/**
 * public Supplier
 */

Route::post('/loginSuperAdmin', [AuthController::class, "loginAdmin"]);
Route::post('/refreshtoken', [AuthController::class, "refreshtoken"]);
Route::post('/register', [AuthController::class, "doRegister"]);


//Route::group(['middleware' => ['jwt.verify']], function () {
 
    Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
    Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');


    Route::get('/home', 'HomeController@index')->middleware('verified');


    Route::get('profile', function () {
        // Only verified users may enter...
    })->middleware('verified');

    Route::get('/', function () {
        $user = User::first();
    });
    Route::get('/get_admins/{per_page}', [AdminController::class, 'all']);


    Route::post('/logout', [AuthController::class, "logout"]);
    Route::get('/get_user', [AuthController::class, 'get_user']);
  


    Route::get('getAllClient/{per_page}', [ClientController::class, 'all']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/createUser', [UserController::class, 'store']);
    Route::put('/updateUser/{id}', [UserController::class, 'edit']);
    Route::delete('/destroy_user/{id}', [UserController::class, 'destroy']);
    
    
    
    // api project
    Route::get('/projects', [ProjectController::class, 'allProjects']);
    Route::get('/projects/{per_page}', [ProjectController::class, 'index']);
    Route::get('/project/{id}', [ProjectController::class, 'show']);
    Route::post('/createProject', [ProjectController::class, 'store']);
    Route::put('/updateProject/{id}', [ProjectController::class, 'update']);
    Route::delete('/destroy_project/{id}', [ProjectController::class, 'destroy']);
    
    // api employee
    Route::get('/employees/{per_page}', [EmployeeController::class, 'all']);
    Route::post('/createEmployee', [EmployeeController::class, 'store']);

    //api task
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/task/{id}', [TaskController::class, 'show']);
    Route::post('/createTask', [TaskController::class, 'store']);
    Route::put('/updateTask/{id}', [TaskController::class, 'edit']);
    Route::delete('/destroy_task/{id}', [TaskController::class, 'destroy']);
    Route::get('/project_task/{project_id}', [TaskController::class, 'TaskwhereProject']);
    
    //api sub task
    Route::get('/sub_tasks', [SubTaskController::class, 'index']);
    Route::get('/sub_task/{id}', [SubTaskController::class, 'show']);
    Route::post('/createSubTask', [SubTaskController::class, 'store']);
    Route::put('/updateSubTask/{id}', [SubTaskController::class, 'edit']);
    Route::delete('/destroy_subtask/{id}', [SubTaskController::class, 'destroy']);
    
//});


 