<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'default_price' => $this->default_price,
            'private' => $this->private,
            'min_period_time' => $this->min_period_time,
            'max_period_time' => $this->max_period_time,
            'available' => $this->available,
            'unit_type' => $this->unit_type,
            'unit_limit' => $this->unit_limit,
            'weight' => $this->weight,
            'dimension' => $this->dimension,
            'options' => ProductOptionsResource::collection($this->options),
            'image' => FileResource::collection($this->files),
            'type_product' => TypeProductResource::collection($this->typeproduct),
            'tags' => TagResource::collection($this->tag),
            'menu' => MenuResource::collection($this->menu),
            'hours' => new ProductHourResource($this->product_hours),
            'supplier' => SupplierResource::collection($this->suppliers)
        ];
    }
}
