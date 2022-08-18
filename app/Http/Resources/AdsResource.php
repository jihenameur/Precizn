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
            'image' => new FileResource($this->file),
            'supplier' => new SupplierResource($this->supplier),
            'product' => new ProductResource($this->product),
            'adsarea' => new AdsareaResource($this->adsarea),
            'menu' => new MenuResource($this->menu)
        ];
    }
}
