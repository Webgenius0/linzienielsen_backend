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
        return [
            'name' => $this->resource['name'] ?? null,
            'avatar' => $this->resource['avatar'] ?? null,
            'gender' => $this->resource['profile']['gender'] ?? null, // Fixed the error here
            'country' => $this->resource['profile']['country'] ?? null,
            'date_of_birth' => $this->resource['profile']['date_of_birth'] ?? null, // Corrected this too
        ];
    }
}
