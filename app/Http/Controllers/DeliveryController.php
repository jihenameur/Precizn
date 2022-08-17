<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Jobs\Admin\ChangeDeliveryPositionJob;
use App\Models\Admin;
use App\Models\Command;
use App\Models\Delivery;
use App\Models\Delivery_Hours;
use App\Models\RequestDelivery;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Supplier;
use App\Notifications\DeliveryDispoNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewDeliveryNotify;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Type\Decimal;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Http\Controllers\Auth\VerificationApiController;
use App\Models\File;

use function PHPUnit\Framework\returnSelf;

class DeliveryController extends Controller
{
    protected $controller;

    public function __construct(
        Request $request,
        Delivery $model,
        Controller $controller = null,
        LocationController $locationController,
        VerificationApiController $verificationApiController
    ) {
        $this->model = $model;
        $this->locationController = $locationController;
        $this->verificationApiController = $verificationApiController;
    }

    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required',
                'lastName' => 'required',
                'vehicle' => 'required',
                'Mark_vehicle' => 'required',
                'start_worktime' => 'required',
                'end_worktime' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',
                'tel' => 'required',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $role_id = Role::where('short_name', config('roles.backadmin.delivery'))->first();
            $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
            if (is_array($latlong) && $latlong[0]['long'] > 0) {
                $request['lat'] = $latlong[0]['lat'];
                $request['long'] = $latlong[0]['long'];
            } else {
                return "Err: address not found";
            }
            $chekphoneExist = $this->verificationApiController->checkPhoneExists($request->tel);
            if ($chekphoneExist == "phone exists") {
                $res->fail("phone exists");
                return new JsonResponse($res, $res->code);
            }

            $allRequestAttributes = $request->all();
            $user = new User($allRequestAttributes);
            $user->password = bcrypt($request->password);
            /** @var Delivery $delivery */


            $delivery =  $this->model->create($allRequestAttributes);

            $delivery->user()->save($user);
            // $user->sendApiEmailVerificationNotification();
            $delivery = $this->model->find($delivery->id);
            $role = Role::find($role_id);
            $user->roles()->attach($role);
            //$this->verificationApiController->toOrange($user->id, $request->phone);

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function addImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                return $validator->errors();
            }
            $delivery = Delivery::find(Auth::user()->userable_id);
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Deliverys'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Deliverys/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $delivery->file_id = $file->id;
            $delivery->update();
            $response['delivery'] = [
                "id"         =>  $delivery->id,
                "firstname"     =>  $delivery->firstName,
                "lastname"     =>  $delivery->lastName,
                "image"     =>  $file->path

            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function updateImage(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return $validator->errors();
            }
            $delivery = Delivery::find(Auth::user()->userable_id);
            if ($request->file('image')) {
                $image = File::find($delivery->file_id);
                unlink('public/Deliverys/' . $image->name);
                $image->delete();
                $file = $request->file('image');
                $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('public/Deliverys'), $name); // your folder path
                $file = new File();
                $file->name = $name;
                $file->path = asset('public/Deliverys/' . $name);
                $file->user_id = Auth::user()->id;
                $file->save();
            }
            $delivery->file_id = $file->id;
            $delivery->update();
            $response['client'] = [
                "id"         =>  $delivery->id,
                "firstname"     =>  $delivery->firstName,
                "lastname"     =>  $delivery->lastName,
                "image"     =>  $file->path

            ];

            $res->success($response);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * Filter or get all
     *
     * @return Collection|Model[]|mixed|void
     */
    public function all($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        // $this->validate($request, [
        //     'available' => 'numeric',
        //     'region' => 'numeric'
        // ]);
        $orderBy = 'created_at';
        $orderByType = "DESC";
        if ($request->has('orderBy') && $request->orderBy != null) {
            $this->validate($request, [
                'orderBy' => 'required|in:firstName,lastName,region,created_at' // complete the akak list
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

            $keyword = $request->has('keyword') ? $request->get('keyword') : null;
            $disponible = $request->has('available') ? $request->available :  null;
            $region = $request->has('region') ? $request->region :  null;
            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword, $orderBy, $orderByType));
            }

            $delivery =  Delivery::query();

            if (!empty($disponible)) {
                $delivery->where('available', 'like', '%' . $disponible . '%');
            }
            if (!empty($region)) {
                $delivery->where('region', 'like', '%' . $region . '%');
            }

            $delivery = $delivery->orderBy($orderBy, $orderByType)->paginate($per_page);

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function getByid($id)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {

            $delivery = Delivery::find($id);

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
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
    private function getFilterByKeywordClosure($keyword, $orderBy, $orderByType)
    {
        $res = new Result();
        try {
            $delivery = Delivery::whereHas('user', function ($q) use ($keyword) {
                $q->where('email', 'like', "%$keyword%");
            })
                ->orWhere('firstName', 'like', "%$keyword%")
                ->orWhere('lastName', 'like', "%$keyword%")
                ->orderBy($orderBy, $orderByType)
                ->get();
            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    /**
     * @inheritDoc
     *
     * @param null $id
     * @param null $params
     * @return Client|mixed|void
     */
    public function update($id, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'vehicle' => 'required',
            'Mark_vehicle' => 'required',
            'start_worktime' => 'required',
            'end_worktime' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            /** @var Delivery $delivery */
            if ($request->file('photo')) {
                $file = $request->file('photo');
                $filename = $file->getClientOriginalName();
                //dd( $filename);

                $file->move(public_path('public/Deliverys'), $filename);
                $request['image'] = $filename;
            }
            $allRequestAttributes = $request->all();
            $delivery = Delivery::find($id);
            $user = $delivery->user;
            $user->fill($allRequestAttributes);
            $delivery->fill($allRequestAttributes);
            $user->update();
            $delivery->update();

            $res->success($delivery);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
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
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Delivery')->first();
            $delivery = Delivery::find($id);
            $delivery->delete();
            $user->delete();
            $res->success("Deleted");
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function acceptCommand(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'command_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $delivery = Delivery::find($request['delivery_id']);
            $command = Command::find($request['command_id']);
            $delivReq = RequestDelivery::where('delivery_id', $request['delivery_id'])
                ->where('command_id', $request['command_id'])
                ->first();
            $command->delivery_id = $delivery->id;
            $command->update();
            $delivReq->accept = 1;
            $delivReq->update();
            $delivery->available = 0;
            $delivery->update();
            $res->success("command accepted");

        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);

    }
    public function notifCommand(Request $request)
    {
        $res = new Result();
        try {
            $deliverys = Delivery::where('available', 1)->get();
            $command = Command::find($request['command_id']);
            $supplier = Supplier::where('id', $command->supplier_id)
                ->get();
            $distance = $this->locationController->getdistances($supplier[0], $deliverys);
            foreach ($distance as $key => $value) {
                if ($value['distance'] <= 6) {

                    $requetDeliv = new RequestDelivery();
                    $requetDeliv->command_id = $command->id;
                    $requetDeliv->delivery_id = $value['User']->id;
                    $requetDeliv->date = date("Y-m-d H:i:s");
                    $requetDeliv->save();

                    // Notification::route('id', $value['User']->id) //Sending mail to subscriber
                    //     ->notify(new NewDeliveryNotify($command)); //With new post
                    sleep(10);
                    $deliv = RequestDelivery::where('delivery_id', $value['User']->id)
                        ->where('command_id', $request['command_id'])
                        ->first();

                    if ($deliv['accept'] == 1) {
                        $res->success($deliv);
                        return new JsonResponse($res, $res->code);
                    }
                }
            }
            // return 'No Delivery disp';
            $res->fail('No Delivery disp');
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function rejectCommand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'command_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $delivery = Delivery::find($request['delivery_id']);
            $command = Command::find($request['command_id']);
            $delivReq = RequestDelivery::where('delivery_id', $request['delivery_id'])
                ->where('command_id', $request['command_id'])
                ->get();

            $command->delivery_id = $delivery->id;
            $command->update();
            $delivReq[0]->accept = 0;
            $delivReq[0]->update();
            $res->success("command rejected");

        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function ListCommandDelivered($per_page, Request $request)
    {

        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $commands = Command::whereHas('requestDelivery', function ($q) use ($request) {
                $q->where('delivery_id', $request['delivery_id']);
                $q->where('accept', 1);
            })
                ->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function ListCommandRejected($per_page, Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required'
        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $commands = Command::whereHas('requestDelivery', function ($q) use ($request) {
                $q->where('delivery_id', $request['delivery_id']);
                $q->where('accept', 0);
                $q->orwhere('accept', null);
            })->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function gainCommands(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'date' => 'required|date'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $date = new DateTime($request['date']);
            $commands = Command::where('delivery_id', $request['delivery_id'])
                ->whereDate('date', $date->format('Y-m-d'))
                ->get();
            $daygain = 0;
            foreach ($commands as $key => $value) {
                $daygain = $daygain + $value->delivery_price;
            }
            // return $daygain;
            $res->success($daygain);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function CommandDelivered(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required',
            'command_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
        $delivery = Delivery::find($request['delivery_id']);
        $command = Command::find($request['command_id']);
        $command->status = 2;
        $command->update();
        $delivery->available = 1;
        $delivery->update();

        $res->success("command delivered");
    } catch (\Exception $exception) {
         if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
    }    }
    public function generateInvoicePDF()
    {
        $pdf = PDF::loadView('myPDF');
        return $pdf->download('nicesnippets.pdf');
    }
    public function hoursWork(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'action' => 'required',
            'delivery_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $currentTime = Carbon::now()->setTimezone('Europe/paris')->format('Y-m-d H:i');

            $currentDate = Carbon::now()->setTimezone('Europe/paris')->format('Y-m-d');

            if ($request['action'] == 'start') {
                $hoursWork = new Delivery_Hours();
                $hoursWork->delivery_id = $request->delivery_id;
                $hoursWork->date =  $currentDate;
                $hoursWork->start_hour =  $currentTime;
                $hoursWork->end_hour =  $currentTime;
                $hoursWork->hours = 0;
                $hoursWork->save();
                $fromUser = Delivery::find($hoursWork->delivery_id);
                $toUser  = Admin::find(1);
                $status = "start Work";
                $toUser->notify(new DeliveryDispoNotification($fromUser, $status));
            } else if ($request['action'] == 'end') {
                $hoursWork = Delivery_Hours::where('delivery_id', $request['delivery_id'])
                    ->where('hours', 0)->first();
                $startTime = Carbon::parse($hoursWork->start_hour);
                $diff_in_hours = $startTime->diff($currentTime)->format('%H:%I:%S');
                $hoursWork->end_hour =  $currentTime;
                $hoursWork->hours =  $diff_in_hours;
                $hoursWork->update();
                $fromUser = Delivery::find($hoursWork->delivery_id);
                $toUser  = Admin::find(1);
                $status = "end Work";
                $toUser->notify(new DeliveryDispoNotification($fromUser, $status));
            } else {
                $res->fail("erreur action");
                return new JsonResponse($res, $res->code);
            }
            $res->success($hoursWork);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }

    public function statisDeliv(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin', 'delivery'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date',
            'delivery_id' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $from = new DateTime($request['from']);
            $to = new DateTime($request['to']);
            $commands = Command::where('delivery_id', $request['delivery_id'])
                ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                ->get();
            $hoursWork = Delivery_Hours::where('delivery_id', $request['delivery_id'])
                ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                ->get();
            $gain = [];
            $totalgain = 0;
            $tips = 0;
            $hours = '00:00';
            for ($i = $from; $i <= $to; $i->modify('+1 day')) {
                $commandsDay = Command::where('delivery_id', $request['delivery_id'])
                    ->whereDate('date', $i->format('Y-m-d'))
                    ->get();
                $daygains = 0;
                //dd($commandsDay);

                foreach ($commandsDay as $key => $value) {
                    $daygains = $daygains + $value->delivery_price + $value->tip;
                    $totalgain = $totalgain + $value->delivery_price;
                    $tips = $tips + $value->tip;
                }
                $temp = [
                    'date' => $i->format('Y-m-d'),
                    'gain' => $daygains
                ];
                array_push($gain, $temp);
            }
            $sumSeconds = 0;
            foreach ($hoursWork as $key => $value) {

                //$hours = Carbon::createFromFormat('H:i',$hours)->addHours(intval($value->hours))->format('H:I');
                $explodedTime = explode(':', $value->hours);
                $seconds = $explodedTime[0] * 3600 + $explodedTime[1] * 60 + $explodedTime[2];

                $sumSeconds = $sumSeconds + $seconds;
            }
            $hours = floor($sumSeconds / 3600);
            $minutes = floor(($sumSeconds % 3600) / 60);
            $seconds = (($sumSeconds % 3600) % 60);
            $sumTime = $hours . ':' . $minutes;
            // dd($sumTime);

            // foreach ($commands as $key => $value) {
            //     $totalgain = $totalgain + $value->delivery_price;
            //     $tips=$tips+$value->tip;
            // }
            //dd($totalgain);
            // return $daygain;
            $stat = ["gainsDay" => $gain, "enLigne" => $sumTime, "courses" => count($commands), "priceCourses" => $totalgain, "tips" => $tips, "total" => $totalgain + $tips];
            $res->success($stat);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
    public function sendDeliveryPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'long' => 'required',
            'lat' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $delivery =  Auth::user()->userable;
        $value = Redis::set('deliveryPostion' . $delivery->id, json_encode([
            'id' => $delivery->id,
            'lng' => $request->long,
            'lat' => $request->lat,

        ]));

        // brodcast to admins
        event(new \App\Events\Admin\DeliveryPosition(json_decode(Redis::get('deliveryPostion' . $delivery->id))));
        dispatch(new ChangeDeliveryPositionJob($delivery,json_decode(Redis::get('deliveryPostion' . $delivery->id))));

        return response()->json(json_decode(Redis::get('deliveryPostion' . $delivery->id)));
    }
    public function statusDelivery(Request $request)
    {
        if (!Auth::user()->isAuthorized(['admin'])) {
            return response()->json([
                'success' => false,
                'massage' => 'unauthorized'
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required', // required and number field validation
            'status_id' => 'required'

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();
        }
        $res = new Result();
        try {
            $user = User::where('userable_id', $request->id)
                ->where('userable_type', 'App\Models\Delivery')->first();
            User::where('id', $user->id)->update([
                'status_id' => $request->status_id
            ]);


            $res->success($user);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
