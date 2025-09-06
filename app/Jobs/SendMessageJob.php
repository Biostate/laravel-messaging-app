<?php

namespace App\Jobs;

use App\Models\CampaignRecipient;
use App\Services\WebhookSiteService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendMessageJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;

    public $tries = 25;

    private $jobStartedAt;

    private $jobQueuedAt;

    public function __construct(
        public CampaignRecipient $campaignRecipient
    ) {
        $this->jobQueuedAt = now();
    }

    public function uniqueId(): string
    {
        return $this->campaignRecipient->id;
    }

    public function middleware(): array
    {
        // https://laravel.com/docs/12.x/queues#rate-limiting
        return [
            (new RateLimited('send-message'))->releaseAfter(5),
        ];
    }

    private function logJobEvent(string $event, array $data = []): void
    {
        $baseData = [
            'campaign_recipient_id' => $this->campaignRecipient->id,
            'campaign_id' => $this->campaignRecipient->campaign_id,
            'recipient_id' => $this->campaignRecipient->recipient_id,
            'phone_number' => $this->campaignRecipient->recipient->phone_number,
            'event' => $event,
            'timestamp' => now()->toISOString(),
        ];

        if ($this->jobStartedAt) {
            $baseData['waiting_time_seconds'] = $this->jobStartedAt->diffInSeconds($this->jobQueuedAt);
            $baseData['job_duration_seconds'] = now()->diffInSeconds($this->jobStartedAt);
        }

        Log::channel('send_message_job')->info("SendMessageJob: {$event}", array_merge($baseData, $data));
    }

    /**
     * @throws Exception
     */
    public function handle(WebhookSiteService $webhookService): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            $this->logJobEvent('job_cancelled_by_batch');

            return;
        }

        $this->jobStartedAt = now();

        $this->logJobEvent('job_started', [
            'queued_at' => $this->jobQueuedAt->toISOString(),
            'started_at' => $this->jobStartedAt->toISOString(),
            'attempt' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        try {
            $campaign = $this->campaignRecipient->campaign;
            $recipient = $this->campaignRecipient->recipient;

            $webhookStartTime = now();
            $response = $webhookService->sendMessage(
                $recipient->phone_number,
                $campaign->message
            );
            $webhookEndTime = now();

            if ($response && isset($response['messageId'])) {
                $sentAt = now();

                // test purposes only
                if ($response['messageId'] === '67f2f8a8-ea58-4ed0-a6f9-ff217df4d849') {
                    $response['messageId'] = Str::uuid();
                }

                $this->campaignRecipient->update([
                    'status' => \App\Enums\CampaignRecipientStatus::Sent,
                    'message_id' => $response['messageId'],
                    'sent_at' => $sentAt,
                ]);

                Cache::put(
                    "message_id_{$this->campaignRecipient->id}",
                    [
                        'messageId' => $response['messageId'],
                        'sent_at' => $sentAt->toISOString(),
                    ],
                    3600
                );

                $this->logJobEvent('message_sent_successfully', [
                    'message_id' => $response['messageId'],
                    'sent_at' => $sentAt->toISOString(),
                    'webhook_response_time_seconds' => $webhookEndTime->diffInSeconds($webhookStartTime),
                    'total_job_time_seconds' => $sentAt->diffInSeconds($this->jobStartedAt),
                    'total_queue_time_seconds' => $sentAt->diffInSeconds($this->jobQueuedAt),
                ]);
            } else {
                throw new Exception('Invalid response from webhook service');
            }
        } catch (Exception $e) {
            $failedAt = now();

            $this->campaignRecipient->update([
                'status' => \App\Enums\CampaignRecipientStatus::Failed,
                'failure_reason' => $e->getMessage(),
            ]);

            $this->logJobEvent('message_send_failed', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'failed_at' => $failedAt->toISOString(),
                'total_job_time_seconds' => $failedAt->diffInSeconds($this->jobStartedAt),
                'total_queue_time_seconds' => $failedAt->diffInSeconds($this->jobQueuedAt),
                'attempt' => $this->attempts(),
                'will_retry' => $this->attempts() < $this->tries,
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $failedAt = now();

        $this->campaignRecipient->update([
            'status' => \App\Enums\CampaignRecipientStatus::Failed,
            'failure_reason' => $exception->getMessage(),
        ]);

        $this->logJobEvent('job_failed_permanently', [
            'error_message' => $exception->getMessage(),
            'error_class' => get_class($exception),
            'failed_at' => $failedAt->toISOString(),
            'total_attempts' => $this->attempts(),
            'max_tries_reached' => $this->attempts() >= $this->tries,
            'total_queue_time_seconds' => $failedAt->diffInSeconds($this->jobQueuedAt),
        ]);
    }
}
