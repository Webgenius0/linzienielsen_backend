<?php

namespace App\Http\Resources\API\V1\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : (array)$this->resource;
        return [
            'name' => $data['name'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'gender' => $data['gender'] ?? null,
            'country' => $data['country'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ];
    }
}
