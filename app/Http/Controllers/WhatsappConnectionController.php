<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class WhatsappConnectionController extends Controller
{
    private function apiUrl()
    {
        return rtrim(env('EVOLUTION_API_URL'), '/');
    }

    private function apiKey()
    {
        return env('EVOLUTION_API_KEY');
    }

    private function instance()
    {
        return env('EVOLUTION_INSTANCE', 'menssageria');
    }

    public function index()
    {
        return view('whatsapp.connection', [
            'instance' => $this->instance(),
        ]);
    }

    public function status()
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey(),
        ])->get($this->apiUrl() . '/instance/fetchInstances');

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar Evolution API',
                'error' => $response->body(),
            ], 500);
        }

        $instances = $response->json();

        $current = collect($instances)->firstWhere('name', $this->instance());

        return response()->json([
            'success' => true,
            'instance' => $this->instance(),
            'status' => $current['connectionStatus'] ?? 'not_found',
            'number' => $current['ownerJid'] ?? null,
            'data' => $current,
        ]);
    }

    public function create()
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey(),
        ])->post($this->apiUrl() . '/instance/create', [
            'instanceName' => $this->instance(),
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ]);

        return response()->json([
            'success' => $response->successful(),
            'data' => $response->json(),
            'raw' => $response->body(),
        ], $response->status());
    }

    public function qrcode()
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey(),
        ])->get($this->apiUrl() . '/instance/connect/' . $this->instance());

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar QR Code',
                'error' => $response->body(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $response->json(),
        ]);
    }
}
