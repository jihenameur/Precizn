<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(64);

        DB::table('reset_password')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        $newpw = "123456";
        Mail::send([], ['token' => $token], function ($message) use ($request, $newpw) {
            $message->to($request->email);
            $message->subject('Reset Password');
            // $message->setBody($newpw);
        });

        return  'We have e-mailed your password reset link!';
    }


    /**
     * Write code on Method
     *
     * @return response()
     */
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $updatePassword = DB::table('reset_password')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();
        if (!$updatePassword) {
            return ('Invalid token!');
        }

        $user = User::where('email', $request->email)
            ->update(['password' =>  bcrypt($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return ('Your password has been changed!');
    }
}
