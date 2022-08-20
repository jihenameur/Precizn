<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
/**
 * @OA\Tag(
 *     name="Notification",
 *     description="Gestion notification",
 *
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Post(
     *      path="/createNotif",
     *      operationId="createNotif",
     *      tags={"Notification"},
     *     security={{"Authorization":{}}},
     *      summary="create notification.",  
     *    @OA\Parameter (
     *     in="query",
     *     name="type",
     *     required=true,
     *     description="type",
     *     @OA\Schema( type="string" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="data",
     *     required=false,
     *     description="data",
     *     @OA\Schema( type="string" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="notifiable_type ",
     *     required=false,
     *     description="notifiable_type ",
     *     @OA\Schema( type="string" ),
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="notifiable_id",
     *     required=false,
     *     description="notifiable_id  ",
     *     @OA\Schema( type="integer",
     *           format="bigint(20)"),
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
   /** @OA\Get(
        *      path="/getNotif",
        *     tags={"Notification"},
        *     security={{"Authorization":{}}},
        *      operationId="getNotif",
        *      summary="Get notification  by notification  type",
        *      @OA\Response(
        *          response=200,
        *          description="Successful operation",
        *      ),
        *  @OA\Parameter(
        *         in="query",
        *        name="type",
        *     required=true,
        *     description="type",
        *     @OA\Schema( type="string" ),
        *      ),
        *      @OA\Response(
        *          response=401,
        *          description="Unauthenticated",
        *      ),
        *      @OA\Response(
        *          response=403,
        *          description="Forbidden"
        *      ),
        * @OA\Response(
        *      response=400,
        *      description="Bad Request"
        *   ),
        * @OA\Response(
        *      response=404,
        *      description="not found"
        *   ),
        * )
        */
    public function getNotif(Request $request)
    {
        $notifs = Notification::where('type', '%'.$request->type.'%')->get();
        return $notifs;
    }
}
