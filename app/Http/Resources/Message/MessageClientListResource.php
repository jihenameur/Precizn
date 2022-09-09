<?php

namespace App\Http\Resources\Message;

use App\Http\Resources\FileResource;
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
            'image' => new FileResource($this->image),
            'expert' => Str::limit($this->messages()->get()->last()->message,20)
        ];
    }
}
