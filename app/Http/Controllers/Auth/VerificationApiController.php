<?php

namespace App\Http\Controllers\Auth;

use App\BaseModel\Result;
use App\Helpers\Sms;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Client;
use App\Models\Message;
use App\Models\Role;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Models\User;
use DateTimeImmutable;
use Exception;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Octopush;
use Vonage;
use Oza75\OrangeSMSChannel\OrangeMessage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerificationApiController extends Controller
{
    use VerifiesEmails;
    /**
     * Show the email verification notice.
     *
     */

    public function show()
    {
        //
    }
    /**
     * Mark the authenticated user’s email address as verified.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $userID = $request['id'];
        $user = User::findOrFail($userID);
        $date = date("Y-m-d g:i:s");
        $user->email_verified_at = $date; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
        $user->save();
        return response()->json("Email verified!");
    }
    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('User already have verified email!', 422);
            // return redirect($this->redirectPath());
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json('The notification has been resubmitted');
        // return back()->with(‘resent’, true);
    }
    public function smsverify($id, Request $req)
    {
        $basic  = new Vonage\Client\Credentials\Basic("5380309e", "WIuDht0kHzcGd0ue");
        $client = new Vonage\Client($basic);
        //$client = new Octopush\Client('amel.meghe@gmail.com', 'qzR3aw5xOAuEvsSNn8PfHgy2Qd7VJcCX');

        // $request = new Octopush\Request\SmsCampaign\SendSmsCampaignRequest();
        // $request->setRecipients([
        //     [
        //         'phone_number' => $req['tel'],
        //         'param1' => 'Alex',
        //     ]
        // ]);
        // $request->setSender('thunder-express');
        // $request->setText(rand(100000, 999999));
        // $request->setType(Octopush\Constant\TypeEnum::SMS_PREMIUM);
        // // $cookie = cookie('codesms', $request->getText(), 5);
        // $codeverify = $request->getText();

        // // ---------------------------------
        // // optional
        // // ---------------------------------
        // $request->setPurpose(Octopush\Request\SmsCampaign\SendSmsCampaignRequest::ALERT_TRANSACTIONAL);
        // $request->setWithReplies(false);

        // $date = new DateTimeImmutable();
        // $isoDateWithTimeZone = $date->format(DATE_ISO8601); // 2021-01-01T00:01:00+0100
        // $request->setSendAt($isoDateWithTimeZone); // also works with "2021-01-01 00:01:00", (Central European TimeZone by default)
        //  $user_id = Auth::user()->id;
        // $codeverify = rand(100000, 999999);

        $phoneExists = $this->checkPhoneExists($req['tel']);
        if ($phoneExists) {
            return (['error' => 'phone exists']);
        } else {
            $receiverNumber = $req['tel'];
            $message = (string)rand(100000, 999999);
            $messages = $client->message()->send([
                'to' => $receiverNumber,
                'from' => 'Thunder Express',
                'text' => $message
            ]);


            User::where('id', $id)->update([
                'tel' => $req['tel'],
                'smscode' => $message
            ]);
            return ('SMS Sent Successfully.');

            // ---------------------------------
            // $content = $client->send($request);
            // return response($content);
            // ---------------------------------
            // Result example:
            // -------
        }
    }
    public function toOrange($id, $tel)
    {


        $config = array(
            'clientId' => "AqBU2cnxyHMDy7BWIjB8U00p7OgWT4Ka",
            'clientSecret' => "47kG53mINAAfTnfC",
        );

        $osms = new Sms($config);

        $data = $osms->getTokenFromConsumerKey();

        $message = (string)rand(100000, 999999);

        $response = $osms->sendSms(
            // sender
            "tel:+21654242402",
            // receiver
            "tel:" . $tel,
            // message
            $message,
            //name
            'Thunder Express'
        );
        User::where('id', $id)->update([
            'tel' => $tel,
            'smscode' => $message,
        ]);
        return $response;
    }

    public function vocalverify($id, Request $req)
    {
        $client = new Octopush\Client('amel.meghe@gmail.com', 'qzR3aw5xOAuEvsSNn8PfHgy2Qd7VJcCX');
        $request = new Octopush\Request\VocalCampaign\SendVocalCampaignRequest();
        $request->setRecipients([
            [
                'phone_number' => $req['tel'],
            ]
        ]);
        $request->setSender('thunder-express');
        $request->setText(rand(100000, 999999));
        $request->setType(Octopush\Constant\TypeEnum::VOCAL_SMS);
        $request->setVoiceGender('female');
        $request->setVoiceLanguage('fr-FR');
        $cookie = cookie('codesms', $request->getText(), 5);
        // ---------------------------------
        // optional
        // ---------------------------------
        $request->setPurpose(Octopush\Request\VocalCampaign\SendVocalCampaignRequest::ALERT_TRANSACTIONAL);

        $date = new  DateTimeImmutable();
        $isoDateWithTimeZone = $date->format(DATE_ISO8601); // 2021-01-01T00:01:00+0100
        $request->setSendAt($isoDateWithTimeZone); // also works with "2021-01-01 00:01:00", (Central European TimeZone by default)

        // ---------------------------------

        $phoneExists = $this->checkPhoneExists($req['tel']);
        if ($phoneExists) {
            return (['error' => 'phone exists']);
        } else {
            User::where('id', $id)->update([
                'tel' => $req['tel'],
                'updated_at' => $date,
            ]);
            $content = $client->send($request);
            return response($content)->cookie($cookie);

            // ---------------------------------
            // Result example:
            // -----
        }
    }
    public function verifySmscode($id, Request $request)
    {

        $res = new Result();
        try {

            // $this->validate($request, [
            //     'code' => 'size:6'
            // ]);
            $validator = Validator::make($request->all(), [
                'code' => 'size:6'
            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                // return $validator->errors();
                throw new Exception($validator->errors());

                //return back()->withInput()->withErrors($validator);
                // validation failed redirect back to form
            }
            $client = Client::find($id);
            $user = User::where('userable_id', $id)
                ->where('userable_type', 'App\Models\Client')->first();
            if ($request['code'] == $user->smscode) {
                //echo 'code verified';
                Client::where('id', $id)->update([
                    'verified' => true,
                ]);
                $user->update([
                    'status_id' => 1
                ]);
                $address = Address::where('user_id', $user->id)
                    ->where('status', 1)->first();
                $role = Role::whereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->first();

                $clt = [
                    'id' => $client['id'],
                    'firstname' => $client['firstname'],
                    'lastname' => $client['lastname'],
                    'image' => $client['image'],
                    'email' => $user['email'],
                    'gender' => $client['gender'],
                    'status' => $user['status_id'],
                    'tel' => $user['tel'],
                    'role' => $role['id'],
                    'street' => $address['street'],
                    'postcode' => $address['postcode'],
                    'city' => $address['city'],
                    'region' => $address['region'],
                ];
                $response = [
                    'token' => $user['token'],
                    // 'token_type' => 'bearer',
                    // 'expires_in' => auth()->factory()->getTTL() * 60,
                    'client' => $clt
                ];
                $res->success($response);
            } else {
                $res->fail('Code not verified');
            }
        } catch (\Exception $exception) {
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);

        //  $date = date_create();
        //  DB::table('users')->where('id', Auth::id())->update(['phone_verified_at' => date_format($date, 'Y-m-d H:i:s')]);

    }
    public function checkPhoneExists($tel)
    {
        $userExists = User::where('tel', $tel)->get();
        foreach ($userExists as $key => $value) {

            if ($value->tel == $tel) {

                return true;
            }
        }

        return false;
    }
}
