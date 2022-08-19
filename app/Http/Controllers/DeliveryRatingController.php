<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BaseModel\Result;
use App\Models\Client;
use App\Models\Delivery;
use App\Models\DeliveryRating;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
 /**
     * @OA\Tag(
     *     name="Rating",
     *     description="Gestion rating delivery",
     *
     * )
   */
class DeliveryRatingController extends Controller
{
      /**
     * @OA\Post(
     *      path="/createDeliveryRating",
     *      operationId="createDeliveryRating",
     *      tags={"Rating"},
     *     security={{"Authorization":{}}},
     *      summary="create rating delivery" ,
     *      description="create rating delivery",
     *    @OA\Parameter (
     *     in="query",
     *     name="comment",
     *     required=false,
     *     description="comment",
     *     @OA\Schema (type="string")
     *      ),
     *     @OA\Parameter (
     *     in="query",
     *     name="rating",
     *     required=true,
     *     description="rating",
     *     @OA\Schema (type="integer",
     *           format="int(11))")
     *      ),
     *   @OA\Parameter (
     *     in="query",
     *     name="client_id",
     *     required=true,
     *     description="client_id",
     *     @OA\Schema (type="integer",
     *           format="int(11)")
     *      ),
     *  @OA\Parameter (
     *     in="query",
     *     name="delivery_id",
     *     required=true,
     *     description="delivery_id",
     *     @OA\Schema ( type="integer",
     *           format="bigint(20)" )
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *    @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *  )
     */
    public function createDeliveryRating(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'rating' => 'required',
                'client_id' => 'required',
                'delivery_id' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                return ($validator->errors());

            }
            $user =  Auth::user();
            $client = Client::find($user->userable_id);
            $delivery = Delivery::find($request->delivery_id);

            $request["client_id"]=$client->id;
            $deliveryRating =  DeliveryRating::create($request->all());

            $ratings = DeliveryRating::where('delivery_id',$request->delivery_id)->get();
            $ratingValues = [];

            foreach ($ratings as $aRating) {
                $ratingValues[] = $aRating->rating;
            }

            $ratingAverage = collect($ratingValues)->sum() / $ratings->count();
            $delivery->rating=$ratingAverage;
            $delivery->update();
            $res->success($deliveryRating);
        } catch (\Exception $exception) {
             if(env('APP_DEBUG')){
                $res->fail($exception->getMessage());
            }
            $res->fail('erreur serveur 500');
        }
        return new JsonResponse($res, $res->code);
    }
}
