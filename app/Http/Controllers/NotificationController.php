<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function createNotif(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'data' => 'required'
            // 'price' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return $validator->errors();

            //return back()->withInput()->withErrors($validator);
            // validation failed redirect back to form

        } else {

            $notif = new Notification();
            $notif->type = $request->type;
            $notif->data = json_encode($request->data);

            $notif->save();

            return response()->json(['type' => $request->type, 'data' => json_encode($request->data)]);
        }
    }
    public function getNotif(Request $request)
    {
        $notifs = Notification::where('type', $request->type)->get();
        return $notifs;
    }
}
