<?php

namespace App\Jobs;

use App\Models\CampaignSend;
use App\Models\CampaignSendContact;
use App\Models\Message;
use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(EvolutionApiService $evolution): void
    {
        $message = Message::with(['campaign', 'contact'])->find($this->messageId);

        if (!$message) {
            return;
        }

        $sendContact = CampaignSendContact::where('message_id', $message->id)->first();

        try {
            $phone = $message->phone;

            if (!$phone && $message->contact) {
                $phone = ($message->contact->ddd ?? '') . ($message->contact->phone ?? '');
            }

            if (!$phone) {
                throw new \Exception('Telefone vazio para envio.');
            }

            $evolution->sendText($phone, $message->message);

            $message->update([
                'status' => 'sent',
                'error' => null,
                'sent_at' => now(),
            ]);

            if ($sendContact) {
                $sendContact->update([
                    'status' => 'sent',
                    'error' => null,
                    'sent_at' => now(),
                ]);

                $this->refreshCampaignSendTotals($sendContact->campaign_send_id);
            }

        } catch (\Throwable $e) {
            $message->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            if ($sendContact) {
                $sendContact->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'failed_at' => now(),
                ]);

                $this->refreshCampaignSendTotals($sendContact->campaign_send_id);
            }

            // Não relança o erro para não jogar o job em failed_jobs.
            return;
        }
    }

    private function refreshCampaignSendTotals(int $campaignSendId): void
    {
        $send = CampaignSend::find($campaignSendId);

        if (!$send) {
            return;
        }

        $pending = CampaignSendContact::where('campaign_send_id', $campaignSendId)
            ->whereIn('status', ['pending', 'queued'])
            ->count();

        $sent = CampaignSendContact::where('campaign_send_id', $campaignSendId)
            ->where('status', 'sent')
            ->count();

        $failed = CampaignSendContact::where('campaign_send_id', $campaignSendId)
            ->where('status', 'failed')
            ->count();

        $status = $pending === 0 ? 'finished' : 'running';

        $send->update([
            'status' => $status,
            'total_pending' => $pending,
            'total_sent' => $sent,
            'total_failed' => $failed,
            'finished_at' => $pending === 0 ? now() : null,
        ]);
    }
}