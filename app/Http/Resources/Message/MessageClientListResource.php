<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class MessageClientListResource extends JsonResource
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
            'name' => $this->firstname.' '.$this->lastname,
            'expert' => Str::limit($this->messages()->get()->last()->message,20)
        ];
    }
}
