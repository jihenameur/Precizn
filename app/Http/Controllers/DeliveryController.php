<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Models\Admin;
use App\Models\Command;
use App\Models\Delivery;
use App\Models\Delivery_Hours;
use App\Models\RequestDelivery;
use App\Models\User;
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

use function PHPUnit\Framework\returnSelf;

class DeliveryController extends Controller
{
    protected $controller;

    public function __construct(Request $request, Delivery $model,  Controller $controller = null, LocationController $locationController)
    {
        $this->model = $model;
        $this->locationController = $locationController;
    }

    public function create(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required',
                'email' => 'required|email|unique:users,email',   // required and email format validation
                'password' => 'required|min:8', // required and number field validation
                'confirm_password' => 'required|same:password',

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());
                //return $validator->errors();
                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form

            }
            $role_id = Role::where('short_name', config('roles.backadmin.delivery'))->first();
            $latlong = $this->locationController->GetLocationWithAdresse($request->street, $request->postcode, $request->city, $request->region);
            if (is_array($latlong) && $latlong[0]['long'] > 0) {
                $request['lat'] = $latlong[0]['lat'];
                $request['long'] = $latlong[0]['long'];
            } else {
                return "Err: address not found";
            }
            if ($request->file('photo')) {
                $file = $request->file('photo');
                $filename = $file->getClientOriginalName();
                //dd( $filename);

                $file->move(public_path('public/Deliverys'), $filename);
                $request['image'] = $filename;
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
            $res->success($delivery);
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
    public function all($per_page, Request $request)
    {
        $res = new Result();
        try {
            $keyword = $request->has('keyword') ? $request->get('keyword') : null;

            $delivery = Delivery::paginate($per_page);

            if ($keyword !== null) {
                $keyword = $this->cleanKeywordSpaces($keyword);

                return ($this->getFilterByKeywordClosure($keyword));
            }
            $res->success($delivery);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function getByid($id)
    {
        $res = new Result();
        try {

            $delivery = Delivery::find($id);

            $res->success($delivery);
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
    private function getFilterByKeywordClosure($keyword)
    {
        $res = new Result();
        try {
            $delivery = Delivery::where('firstName', 'like', "%$keyword%")
                // ->orWhere('lastname', 'like', "%$keyword%")
                // ->orWhereRaw("CONCAT(lastname,' ',firstname) like '%$keyword%'")
                // ->orWhereRaw("CONCAT(firstname,' ',lastname) like '%$keyword%'")
                ->orWhere('lastName', 'like', "%$keyword%")
                ->get();
            $res->success($delivery);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
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
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Delivery')->first();
            $user->is_deleted = true;
            $user->update();
            $res->success($user);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function acceptCommand(Request $request)
    {

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

        return true;
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function rejectCommand(Request $request)
    {
        $delivery = Delivery::find($request['delivery_id']);
        $command = Command::find($request['command_id']);
        $delivReq = RequestDelivery::where('delivery_id', $request['delivery_id'])
            ->where('command_id', $request['command_id'])
            ->get();

        $command->delivery_id = $delivery->id;
        $command->update();
        $delivReq[0]->accept = 0;
        $delivReq[0]->update();

        return true;
    }
    public function ListCommandDelivered($per_page, Request $request)
    {
        $res = new Result();
        try {
            $commands = Command::whereHas('requestDelivery', function ($q) use ($request) {
                $q->where('delivery_id', $request['delivery_id']);
                $q->where('accept', 1);
            })
                ->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function ListCommandRejected($per_page, Request $request)
    {
        $res = new Result();
        try {
            $commands = Command::whereHas('requestDelivery', function ($q) use ($request) {
                $q->where('delivery_id', $request['delivery_id']);
                $q->where('accept', 0);
                $q->orwhere('accept', null);
            })->paginate($per_page);
            $res->success($commands);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function gainCommands(Request $request)
    {
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function CommandDelivered(Request $request)
    {
        $delivery = Delivery::find($request['delivery_id']);
        $command = Command::find($request['command_id']);
        $command->status = 2;
        $command->update();
        $delivery->available = 1;
        $delivery->update();

        return true;
    }
    public function generateInvoicePDF()
    {
        $pdf = PDF::loadView('myPDF');
        return $pdf->download('nicesnippets.pdf');
    }
    public function hoursWork(Request $request)
    {
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
                $toUser->notify(new DeliveryDispoNotification($fromUser,$status));
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
                $toUser->notify(new DeliveryDispoNotification($fromUser,$status));
            } else {
                $res->fail("erreur action");
                return new JsonResponse($res, $res->code);
            }
            $res->success($hoursWork);
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }

    public function statisDeliv(Request $request)
    {
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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
    public function sendDeliveryPosition(Request $request)
    {
        $delivery =  Auth::user()->userable;
        $value=Redis::set('deliveryPostion'.$delivery->id,json_encode([
            'id' => $delivery->id,
            'long' => $request->long,
            'lat' => $request->long,

        ]));

        // brodcast to admins
        event(new \App\Events\DeliveryPosition(json_decode(Redis::get('deliveryPostion'.$delivery->id))));

        return response()->json(json_decode(Redis::get('deliveryPostion'.$delivery->id)));

    }
}
