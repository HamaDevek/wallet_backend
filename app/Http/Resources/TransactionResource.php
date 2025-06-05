<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'sender' => $this->whenLoaded('sender', function () {
                return new UserResource($this->sender);
            }),
            'receiver' => $this->whenLoaded('receiver', function () {
                return new UserResource($this->receiver);
            }),
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'created_at' => $this->created_at->toISOString(),
            // 'updated_at' => $this->updated_at->toISOString(),a
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'server_time' => now()->toISOString(),
        ];
    }
}
