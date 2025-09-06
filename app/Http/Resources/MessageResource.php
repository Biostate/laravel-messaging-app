<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'campaign_id' => $this->campaign_id,
            'recipient_id' => $this->recipient_id,
            'phone_number' => $this->recipient->phone_number,
            'message_content' => $this->campaign->message,
            'status' => $this->status->value,
            'sent_at' => $this->sent_at?->toISOString(),
            'failure_reason' => $this->failure_reason,
            'message_id' => $this->when($this->message_id, $this->message_id),
            'cached_message_id' => $this->when($this->message_id && $this->getCachedData(), function () {
                $cachedData = $this->getCachedData();

                return $cachedData['messageId'] ?? null;
            }),
            'cached_sent_at' => $this->when($this->message_id && $this->getCachedData(), function () {
                $cachedData = $this->getCachedData();

                return $cachedData['sent_at'] ?? null;
            }),
        ];
    }

    /**
     * Get cached message data from Redis
     */
    private function getCachedData(): ?array
    {
        return \Illuminate\Support\Facades\Cache::get("message_id_{$this->id}");
    }
}
