<?php

namespace App\Http\Resources\Command;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandClientResource extends JsonResource
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
            'name' => $this->firstname.' '.$this->lastname,
            'phone' => User::where('userable_id',$this->id)->where('userable_type','App\Models\Client')->first()->tel ?? '#',
        ];
    }
}
