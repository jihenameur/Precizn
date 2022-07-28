<?php


namespace App\Helpers;


use App\BaseModel\Result;
use App\Helpers\Payments\Ctp;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PaymentGetWay
{

    private $methods = [
        0 => 'outdoorPayment',
        1 => 'walletPayment',
        2 => 'ClickToPayUrlGenerator'
    ];
    public function checkout($command, $total, $client, $method){
        $res = new Result();
        try {
            if (array_key_exists($method,$this->methods)){
                $func = $this->methods[$method];
                return $this->$func($command, $total, $client);
            }else{
                $res->fail('payment method does not exist');
                return $res;
            }

        }catch (\Exception $exception){
            $res->fail($exception->getMessage());
            return $res;
        }

    }
    public function outdoorPayment($command, $total, $client)
    {
        $res = new Result();

        // create payment model instance
        $payment = new Payment();
        $payment->code = Str::random(32); // generate unique random code
        $payment->type = 'outdoor';
        $payment->target = 'command';
        $payment->status = 1;
        $payment->state = 0;
        $payment->amount = $total;
        $payment->command_id = $command->id;
        $payment->save();

        $command->status = 1;
        $command->save();
        $res->success([
            'payement_mode' => 0,
            'commande' => $command
        ],"This order is in progress");

        return $res;

    }

    public function walletPayment($command, $total, $client)
    {
        $res = new Result();
        // create payment model instance
        $payment = new Payment();
        $payment->code = Str::random(32); // generate unique random code
        $payment->type = 'wallet';
        $payment->target = 'command';
        $payment->status = 0;
        $payment->state = 0;
        $payment->amount = $total;
        $payment->command_id = $command->id;
        $payment->save();

        try {
            if($client->balance < $total){
                $payment->state = 6;
                $payment->save();
                return $res->fail('insufficient balance');
            }else{
                $client->incrementDecrementBalance($total,false);
                $payment->status = 1;
                $payment->state = 2;
                $payment->save();
            }
        }catch (\Exception $exception){
            $res->fail($exception->getMessage());
            return $res;
        }

        // to do wallet process
        $command->status = 1;
        $command->save();
        $res->success([
            'payement_mode' => 1,
            'commande' => $command
        ],"Payement within wallet processed successfully ");

        return $res;
    }

    public function ClickToPayUrlGenerator($command, $total, $client)
    {
        $res = new Result();

        // create payment model instance
        $payment = new Payment();
        $payment->code = Str::random(32); // generate unique random code
        $payment->type = 'ctp';
        $payment->target = $command ? 'command' : 'wallet';
        $payment->status = 0;
        $payment->state = 0;
        $payment->amount = $total;
        $payment->command_id = $command ? $command->id : null;
        $payment->client_id = $client ? $client->id : null;
        $payment->save();

        $ctp_helper = new Ctp();
        $protal_url = $ctp_helper->makePayment($payment);
        if($protal_url instanceof \Exception) {
            $payment->state = 6;
            $payment->save();
            $res->fail($protal_url->getMessage());
            return $res;
        }

        $res->success([
            'payement_mode' => 2,
            'uri' => $protal_url,
            'target' => $command ? ['command' => $command] : ['client' => $client]
        ],"Redirect to payment getway");

        return $res;
    }


    public function verifyPayment(Payment $payment)
    {
        $res = new Result();
            if($payment->type == 'ctp'){
                $ctp_helper = new Ctp();
                if($ctp_helper->processPayment($payment)){
                    $res->success([
                        'payement_mode' => 2,
                    ],"payment verified successfully ");
                    return $res;
                }else{
                    $res->fail("payment error ");
                    return $res;
                }
            }
    }
}
