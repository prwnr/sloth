<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class Billing
 * @package App\Http\Resources
 */
class Billing extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $array = [];
        foreach ($this->resource as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }
}
