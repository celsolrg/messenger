<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('EVOLUTION_API_URL'), '/');
        $this->apiKey = env('EVOLUTION_API_KEY');
        $this->instance = env('EVOLUTION_INSTANCE');
    }

    public function sendText(string $number, string $text): array
    {
        $url = "{$this->baseUrl}/message/sendText/{$this->instance}";

        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'number' => $this->normalizeNumber($number),
            'textMessage' => [
                'text' => $text,
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Erro Evolution API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Erro ao enviar mensagem pela Evolution API: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    private function normalizeNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);

        if (!str_starts_with($number, '55')) {
            $number = '55' . $number;
        }

        return $number;
    }
}
