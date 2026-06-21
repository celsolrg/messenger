<?php

namespace App\Jobs;

use App\Models\CampaignSend;
use App\Models\CampaignSendContact;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessCampaignSendJob implements ShouldQueue
{
    use Queueable;

    protected int $campaignSendId;

    public function __construct(int $campaignSendId)
    {
        $this->campaignSendId = $campaignSendId;
    }

    public function handle(): void
    {
        $send = CampaignSend::with([
            'campaign.media',
            'contacts.contact',
        ])->find($this->campaignSendId);

        if (!$send) {
            return;
        }

        $send->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $delay = 0;
        $counter = 0;

        foreach ($send->contacts as $item) {

            if (!$item->phone) {
                $item->update([
                    'status' => 'failed',
                    'error' => 'Telefone não encontrado',
                    'failed_at' => now(),
                ]);

                continue;
            }

            $message = Message::create([
                'campaign_id' => $send->campaign_id,
                'contact_id' => $item->contact_id,
                'phone' => $item->phone,
                'message' => $send->campaign->message,
                'status' => 'pending',
            ]);

            $item->update([
                'message_id' => $message->id,
                'status' => 'queued',
                'queued_at' => now(),
            ]);

            SendMessageJob::dispatch($message->id)
                ->delay(now()->addSeconds($delay));

            $counter++;

            $delay += rand(
                $send->min_delay_seconds ?? 20,
                $send->max_delay_seconds ?? 60
            );

            if (
                ($send->pause_every ?? 20) > 0 &&
                $counter % ($send->pause_every ?? 20) === 0
            ) {
                $delay += $send->pause_seconds ?? 300;
            }
        }

        $send->update([
            'status' => 'queued',
        ]);
    }
}