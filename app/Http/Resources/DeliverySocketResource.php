<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliverySocketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstName ?? $this->firstname,
            'lastname' => $this->lastName ?? $this->lastname,
            'image' => $this->image,
            'role' => $this->user->roles ? $this->user->roles[0]->name : null
        ];
    }
}
