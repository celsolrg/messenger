<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\EvolutionService;
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

    public function handle(EvolutionService $evolution): void
    {
        $message = Message::with(['campaign', 'contact'])->find($this->messageId);

        if (!$message) {
            return;
        }

        try {
            $phone = $message->phone;

            if (!$phone && $message->contact) {
                $phone = $message->contact->ddd . $message->contact->telefone;
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
        } catch (\Throwable $e) {
            $message->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}