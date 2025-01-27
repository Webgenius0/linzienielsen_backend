<?php

namespace App\Http\Resources\API\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
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
            'token' => $data['token'] ?? null,
            'user' => [
                'id' => $data['user']['id'] ?? null,
                'email' => $data['user']['email'] ?? null,
                'handle' => $data['user']['handle'] ?? null,
                'name' => $data['user']['name'] ?? null,
                'avatar' => $data['user']['avatar'] ?? null,
                'role' => $data['user']['role'] ?? null,
            ],
            'verify' => $data['verify'] ?? false,
        ];
    }
}
