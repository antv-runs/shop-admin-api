<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float) $this->price,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', function () {
                return new CategoryResource($this->category);
            }),
            'image_url' => $this->image ? url('storage/' . $this->image) : null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
