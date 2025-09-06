<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_messages' => $this->resource['total_messages'],
            'sent_messages' => $this->resource['sent_messages'],
            'pending_messages' => $this->resource['pending_messages'],
            'failed_messages' => $this->resource['failed_messages'],
            'success_rate' => $this->resource['success_rate'],
        ];
    }
}
