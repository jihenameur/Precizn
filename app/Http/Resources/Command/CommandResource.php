<?php

namespace App\Http\Resources\Command;

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
        return [
            'id' => $this->id,
            'cycle' => $this->cycle,
            'made_at' => $this->created_at,
            'supplier' => new CommandSupplierResource($this->supplier),
            'delivery' => new CommandDeliveryResource($this->delivery),
            'client' => new CommandClientResource($this->client),
            'localisation' => ["lat" => $this->lat, "long" => $this->long]
        ];
    }
}
