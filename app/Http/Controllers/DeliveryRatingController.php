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

class DeliveryRatingController extends Controller
{
    public function createDeliveryRating(Request $request)
    {
        $res = new Result();
        try {
            $validator = Validator::make($request->all(), [
                'rating' => 'required'

            ]); // create the validations
            if ($validator->fails())   //check all validations are fine, if not then redirect and show error messages
            {
                throw new Exception($validator->errors());

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
            $res->fail($exception->getMessage());
        }
        return new JsonResponse($res, $res->code);
    }
}
