<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdsResource extends JsonResource
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
            "id"         =>  $this->id,
            "start_date"      =>  $this->start_date,
            "end_date"     =>  $this->end_date,
            "price"     =>  $this->price,
            'image' => $this->file->path,
            'supplier' => $this->supplier_id ?? null,
            'product' => $this->product_id ?? null,
            'adsarea' => $this->adsarea->title,
            'menu' => $this->menu_id ?? null
        ];
    }
}
