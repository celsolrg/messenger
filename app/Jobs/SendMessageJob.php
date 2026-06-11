<?php

namespace App\Jobs;

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

    public function handle(EvolutionApiService $evolution)
    {
        $message = Message::find($this->messageId);

            if (!$message) {
            return;
        }

        try {
            $message->update([
                'status' => 'sending',
                'error' => null,
            ]);

            $evolution->sendText($message->phone, $message->message);

            $message->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error' => null,
            ]);

        } catch (\Throwable $e) {
            $message->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}