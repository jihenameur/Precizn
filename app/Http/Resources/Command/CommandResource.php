<?php

namespace App\Http\Resources\Command;

use App\Helpers\RedisHelper;
use App\Models\Delivery;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $redis_hepler = new RedisHelper();
        return [
            'id' => $this->id,
            'cycle' => $this->cycle,
            'cycle_at' => $this->cycle_at,
            'made_at' => $this->created_at,
            'supplier' => new CommandSupplierResource($this->supplier),
            'delivery' => new CommandDeliveryResource($this->delivery) ?? false,
            'products' => CommandProductResource::collection($this->products),
            'client' => new CommandClientResource($this->client),
            'localisation' => ["lat" => $this->lat, "long" => $this->long],
            'total_price' =>$this->total_price,
            'pre_assinged_delivery' => $redis_hepler->getPreAssignedDeliveryToCommand($this->id) ? new CommandDeliveryResource(Delivery::find($redis_hepler->getPreAssignedDeliveryToCommand($this->id))) : false
        ];
    }
}
