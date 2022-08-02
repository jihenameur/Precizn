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
