<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Events\MessageSent;
use App\Http\Resources\DeliverySocketResource;
use App\Models\Client;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Persist message to database
     *
     * @param  Request $request
     * @return Response
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'sendclient' => 'required',
            'id' => 'required',

        ]); // create the validations
        if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
        {
            return back()->withInput()->withErrors($validator);
            // validation failed redirect back to form

        } else {
            $client = Client::find($request['id']);

            $message = $client->messages()->create([
                'message' => $request->input('message'),
                'send' => $request->input('sendclient'),
                'date' => date('Y-m-d H:i:s'),

            ]);

            event (new \App\Events\MessageSent($client->firstname,$message->message));
            return ['status' => 'Message Sent!'];
        }
    }
    public function getMessage(Request $request)
    {
        $messages = Message::whereHas('client', function ($q) use ($request) {
            $q->where('id', $request['id_client']);
        })
            ->orderBy('date', 'desc')
            ->get();
        return $messages;
    }


    public function createMessage(Request $request)
    {
            $this->validate($request,[
                'message' => 'required|max:255'
            ]);

            $message = new Message();
            $message->message = $request->input('message');
            $message->send = 0;
            $message->client_id = auth()->user()->userable_id;
            $message->date = date('Y-m-d H:i:s');

            $message->save();

            event (new \App\Events\MessageSent(new DeliverySocketResource(auth()->user()->userable),$request->input('message')));

            $res = new Result();

            $res->success('ok','sent');

            return new JsonResponse($res, $res->code);
    }

    public function createReply(Request $request){

    }
}
