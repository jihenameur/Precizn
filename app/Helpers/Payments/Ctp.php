<?php


namespace App\Helpers\Payments;


use App\BaseModel\Result;
use App\Models\Payment;
use http\Client\Request;
use Illuminate\Support\Facades\Http;

class Ctp
{
    private $ctp_portal_url ;
    private $ctp_portal_result_url ;
    private $ctp_login ;
    private $ctp_password ;
    private $ctp_currency ;
    private $ctp_fail_url;
    private $ctp_success_url;
    private $ctp_language;
    public function __construct()
    {
        $this->ctp_portal_url = env('CTP_PORTAL_URL',"");
        $this->ctp_portal_result_url = env('CTP_PORTAL_RESULT_URL',"");
        $this->ctp_login = env('CTP_LOGIN',"");
        $this->ctp_password = env('CTP_PASSWORD',"");
        $this->ctp_currency = env('CTP_CURRENCY',"");
        $this->ctp_success_url = env('CTP_SUCCESS_URL',"");
        $this->ctp_fail_url = env('CTP_FAIL_URL',"");
        $this->ctp_language = env('CTP_LANGUAGE',"");
    }

    public function makePayment(Payment $payment)
    {
        $res = new Result();
        try{
            $http_request = Http::get($this->ctp_portal_url,[
                'password' => $this->ctp_password,
                'userName' => $this->ctp_login,
                'amount' => $payment->amount * 1000,
                'currency' => $this->ctp_currency,
                'orderNumber'  => $payment->code,
                'returnUrl' => $this->ctp_success_url,
                'wallet'=> $payment->target == 'command' ? 0 : 1,
                'failUrl' => $this->ctp_fail_url,
                'language' => $this->ctp_language,
            ]);
            $response = $http_request->json();
            if(array_key_exists('errorCode',$response)) { return (new \Exception('payment method internal error'));}
            $payment->code = $response['orderId'];
            $payment->save();
            return $response['formUrl'];
        }catch (\Exception $exception){
           return $res->fail($exception->getMessage());
        }
    }


    public function processPayment(Payment $payment)
    {
        $res = new Result();
        try{
            $http_request = Http::get($this->ctp_portal_result_url,[
                'orderId' => $payment->code,
                'userName' => $this->ctp_login,
                'password' => $this->ctp_password,
            ]);
            $response = $http_request->json();
            $status = $response['OrderStatus'];
            $payment->state = $status;
            $payment->status = 1;
            $payment->save();

            return $status == 2;
        }catch (\Exception $exception){
            $res->fail($exception->getMessage());
        }
    }

}
