<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Helpers\CommandHelper;
use App\Helpers\PaymentGetWay;
use App\Models\Client;
use App\Models\Command;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function makePayment(Command $command) // dependencies injection
    {
        $res = new Result();
        try {

               $client = Client::find(auth()->user()->userable_id);
                if(($client->id == $command->client_id) && ($command->status == 0)){
                    // calculate total
                    $command_helper = new CommandHelper();
                    $total = $command_helper->CalculateTotale($command);

                    // treat payement by method && return response
                    $paymentGetWay = new PaymentGetWay();
                    $response = $paymentGetWay->checkout($command,$total, $client, $command->mode_pay);
                    return new JsonResponse($response, $response->code);

                }

                $res->fail('Commande not found');
                return new JsonResponse($res, $res->code);

        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
    }


    public function verifyPayment(Request $request)
    {
        $res = new Result();

        $this->validate($request,[
           'orderId' => 'required'
        ]);

        $code = $request->input('orderId');

        $payment = Payment::where('code',$code)->first();
        if($payment){
            if(!$payment->status){
                $paymentGetWay = new PaymentGetWay();
                $response= $paymentGetWay->verifyPayment($payment);
                if($response->success){
                    if($payment->target == 'wallet'){
                        try {

                            $client = Client::find($payment->client_id);
                            $client->incrementDecrementBalance($payment->amount,true);
                            $payment->state = 2;
                            $payment->status = 1;
                            $payment->save();
                        }catch (\Exception $exception) {
                             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
                        }
                    }
                    if($payment->target == 'command'){
                        try {

                            $payment->state = 2;
                            $payment->status = 1;
                            $payment->save();
                        }catch (\Exception $exception) {
                             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
                        }
                    }
                }
                return new JsonResponse($response, $response->code);
            }else{
                $res->fail('Payment already processed');

                return new JsonResponse($res, $res->code);
            }
        }

        $res->fail('An error has ben  accrued');
        return new JsonResponse($res, $res->code);
    }


    public function RechargeBalance(Request $request)
    {
        $this->validate($request,[
           'amount' => 'required|numeric|gt:0',
        ]);

        $res = new Result();

        try {
            $client = Client::find(auth()->user()->userable_id);

            $paymentGetWay = new PaymentGetWay();
            $response = $paymentGetWay->checkout(null,$request->input('amount'), $client, 2);
            return new JsonResponse($response, $response->code);


        }catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
    }
}
