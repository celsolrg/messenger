<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessageJob;
use App\Models\Campaign;
use App\Models\CampaignMedia;
use App\Models\Contact;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index()
    {
        return Campaign::with('media')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'nullable|string',
            'type' => 'required|string|in:text,image,video,audio,document',
            'media' => 'nullable|file|max:51200',
        ]);

        if ($request->type !== 'text' && !$request->hasFile('media')) {
            return response()->json([
                'message' => 'Arquivo obrigatório para este tipo de campanha.'
            ], 422);
        }

        $campaign = Campaign::create([
            'name' => $request->name,
            'message' => $request->message,
            'type' => $request->type,
            'user_id' => Auth::id(),
        ]);

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $path = $file->store('campaigns', 'public');

            CampaignMedia::create([
                'campaign_id' => $campaign->id,
                'media_type' => $request->type,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return response()->json([
            'message' => 'Campanha criada com sucesso.',
            'campaign' => $campaign->load('media'),
        ], 201);
    }

public function update(Request $request, $id)
{
    $campaign = Campaign::where('user_id', Auth::id())->findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'message' => 'nullable|string',
        'type' => 'required|string|in:text,image,video,audio,document',
        'media' => 'nullable|file|max:51200',
    ]);

    $campaign->update([
        'name' => $request->name,
        'message' => $request->message,
        'type' => $request->type,
    ]);

    if ($request->hasFile('media')) {
        $campaign->media()->delete();

        $file = $request->file('media');
        $path = $file->store('campaigns', 'public');

        CampaignMedia::create([
            'campaign_id' => $campaign->id,
            'media_type' => $request->type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    return response()->json([
        'message' => 'Campanha atualizada com sucesso.',
        'campaign' => $campaign->load('media'),
    ]);
}

public function copy($id)
{
    $campaign = Campaign::with('media')
        ->where('user_id', Auth::id())
        ->findOrFail($id);

    $newCampaign = Campaign::create([
        'name' => $campaign->name . ' - Cópia',
        'message' => $campaign->message,
        'type' => $campaign->type,
        'user_id' => Auth::id(),
    ]);

    foreach ($campaign->media as $media) {
        CampaignMedia::create([
            'campaign_id' => $newCampaign->id,
            'media_type' => $media->media_type,
            'file_name' => $media->file_name,
            'file_path' => $media->file_path,
            'mime_type' => $media->mime_type,
            'file_size' => $media->file_size,
        ]);
    }

    return response()->json([
        'message' => 'Campanha copiada com sucesso.',
        'campaign' => $newCampaign->load('media'),
    ], 201);
}

public function destroy($id)
{
    $campaign = Campaign::where('user_id', Auth::id())->findOrFail($id);

    $campaign->media()->delete();
    $campaign->delete();

    return response()->json([
        'message' => 'Campanha excluída com sucesso.',
    ]);
}

    public function send($id)
    {
        $campaign = Campaign::with('media')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $contacts = Contact::where('user_id', Auth::id())
            ->where('opt_in', 1)
            ->where('ativo', 1)
            ->get();

        $totalQueued = 0;
        $totalSkipped = 0;

        foreach ($contacts as $contact) {
            $phone = $this->formatPhone($contact);

            if (!$phone) {
                $totalSkipped++;
                continue;
            }

            $body = $this->parseMessage($campaign->message ?? '', $contact);

            $message = Message::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'phone' => $phone,
                'message' => $body,
                'status' => 'pending',
            ]);

            SendMessageJob::dispatch($message->id);

            $totalQueued++;
        }

        return response()->json([
            'message' => 'Campanha enviada para a fila.',
            'total_contacts' => $contacts->count(),
            'total_queued' => $totalQueued,
            'total_skipped' => $totalSkipped,
        ]);
    }

    private function formatPhone(Contact $contact): ?string
    {
        $ddd = preg_replace('/\D/', '', (string) ($contact->ddd ?? ''));
        $telefone = preg_replace('/\D/', '', (string) ($contact->telefone ?? ''));

        $phone = $ddd . $telefone;

        if (!$phone) {
            return null;
        }

        if (!str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        return $phone;
    }

    private function parseMessage(string $message, Contact $contact): string
    {
        return str_replace(
            [
                '{{name}}',
                '{{nome}}',
                '{{phone}}',
                '{{telefone}}',
            ],
            [
                $contact->nome ?? '',
                $contact->nome ?? '',
                $contact->telefone ?? '',
                $contact->telefone ?? '',
            ],
            $message
        );
    }
}
