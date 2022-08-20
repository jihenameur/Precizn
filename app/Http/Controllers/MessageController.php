<?php

namespace App\Http\Controllers;

use App\BaseModel\Result;
use App\Events\Admin\MessageSent;
use App\Http\Resources\DeliverySocketResource;
use App\Jobs\Admin\NotifyNewClientMessage;
use App\Jobs\Client\NotifyNewAdminMessage;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
/**
 * @OA\Tag(
 *     name="Message",
 *     description="Gestion message.",
 *
 * )
 */
class MessageController extends Controller
{
    /**
     * Persist message to database
     *
     * @param  Request $request
     * @return Response
     */
    /**
     * @OA\Post(
     *      path="/sendMessage",
     *      operationId="sendMessage",
     *      tags={"Message"},
     *     security={{"Authorization":{}}},
     *      summary="create message.",  
     *    @OA\Parameter (
     *     in="query",
     *     name="id",
     *     required=true,
     *     description="id client",
     *     @OA\Schema( type="integer",
     *           format="bigint(20)"),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="sendclient",
     *     required=false,
     *     description="sendclient",
     *     @OA\Schema( type="string" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="message",
     *     required=false,
     *     description="massage",
     *     @OA\Schema( type="string" ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="bad request",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
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

            event(new \App\Events\Admin\MessageSent($client->firstname, $message->message));
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
 /**
     * @OA\Post(
     *      path="/createmessage",
     *      operationId="createmessage",
     *      tags={"Message"},
     *     security={{"Authorization":{}}},
     *      summary="create message.",  
     *  @OA\Parameter(
     *     in="query",
     *     name="message",
     *     required=false,
     *     description="message",
     *     @OA\Schema( type="string" ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="bad request",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *     )
     */

    public function createMessage(Request $request)
    {
        $this->validate($request, [
            'message' => 'required|max:255'
        ]);

        $message = new Message();
        $message->message = $request->input('message');
        $message->send = 0;
        $message->client_id = auth()->user()->userable_id;
        $message->date = date('Y-m-d H:i:s');

        $message->save();
        $fromUser = Client::where('id', auth()->user()->userable_id)->first();
        $this->dispatch(new NotifyNewClientMessage($fromUser, $message));

        $toUser  = Admin::find(1);
        $toUser->notify(new MessageNotification($message, $fromUser));
        // Notification::send($toUser, new MessageNotification($message,auth()->user()));

        $res = new Result();

        $res->success('ok', 'sent');

        return new JsonResponse($res, $res->code);
    }

    public function createReply(Request $request)
    {
        $this->validate($request, [
            'message' => 'required|max:255'
        ]);

        $message = new Message();
        $message->message = $request->input('message');
        $message->send = 1;
        $message->client_id = $request->client_id;
        $message->date = date('Y-m-d H:i:s');

        $message->save();

        $fromUser = Admin::find(auth()->user()->userable_id);
        $toUser = Client::find($request->client_id);
        $this->dispatch(new NotifyNewAdminMessage($toUser, $message));
        $toUser->notify(new MessageNotification($message, $fromUser));

        //Notification::send($toUser, new MessageNotification(auth()->user()));
        $res = new Result();

        $res->success('ok', 'sent');

        return new JsonResponse($res, $res->code);
    }
}
