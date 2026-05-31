<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMessageJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        $message = Message::find($this->messageId);

        if (!$message) {
            return;
        }

        if ($message->status === 'sent') {
            return;
        }

        try {
            $message->update([
                'status' => 'processing',
                'error' => null,
            ]);

            /*
             * Aqui entra o envio real pelo WhatsApp.
             * Por enquanto, vamos simular sucesso.
             */

            sleep(rand(1, 3));

            $message->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error' => null,
            ]);

        } catch (Throwable $e) {
            $message->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
