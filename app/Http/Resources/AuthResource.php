<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'real_password' => $this->real_password,
            'is_verified' => $this->is_verified,
            'shop' => $this->shop,
            'role' => $this->role()->name,
            'permissions' => $this->role()->permissions->select('name'),
        ];
    }
}
