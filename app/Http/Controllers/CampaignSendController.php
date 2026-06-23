<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCampaignSendJob;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\CampaignSendContact;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignSendController extends Controller
{
    public function index()
    {
        return CampaignSend::with('campaign')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',

            'target_type' => 'nullable|string|in:all,tag,state,city,ddd',
            'target_value' => 'nullable|string|max:255',

            'min_delay_seconds' => 'nullable|integer|min:5|max:300',
            'max_delay_seconds' => 'nullable|integer|min:5|max:600',

            'pause_every' => 'nullable|integer|min:1|max:100',
            'pause_seconds' => 'nullable|integer|min:0|max:3600',
        ]);

        $targetType = $data['target_type'] ?? 'all';
        $targetValue = $data['target_value'] ?? null;

        if ($targetType !== 'all' && empty($targetValue)) {
            return response()->json([
                'message' => 'Informe o valor do filtro de público.',
                'errors' => [
                    'target_value' => ['Informe o valor do filtro de público.']
                ]
            ], 422);
        }

        $campaign = Campaign::where('user_id', Auth::id())
            ->findOrFail($data['campaign_id']);

        $contactsQuery = Contact::with('mainPhone')
            ->where('user_id', Auth::id())
            ->where('opt_in', true)
            ->where('ativo', true);

        switch ($targetType) {
            case 'tag':
                $contactsQuery->where('tag', $targetValue);
                break;

            case 'state':
                $contactsQuery->where('uf', $targetValue);
                break;

            case 'city':
                $contactsQuery->where('cidade', $targetValue);
                break;

            case 'ddd':
                $contactsQuery->where('ddd', preg_replace('/\D/', '', $targetValue));
                break;

            case 'all':
            default:
                break;
        }

        $contacts = $contactsQuery->get();

        if ($contacts->isEmpty()) {
            return response()->json([
                'message' => 'Nenhum contato encontrado para o público selecionado.'
            ], 422);
        }

        $minDelay = $data['min_delay_seconds'] ?? 20;
        $maxDelay = $data['max_delay_seconds'] ?? 60;

        if ($maxDelay < $minDelay) {
            return response()->json([
                'message' => 'O intervalo máximo não pode ser menor que o intervalo mínimo.'
            ], 422);
        }

        $send = CampaignSend::create([
            'user_id' => Auth::id(),
            'campaign_id' => $campaign->id,

            'target_type' => $targetType,
            'target_value' => $targetValue,

            'status' => 'pending',

            'total_contacts' => $contacts->count(),
            'total_pending' => $contacts->count(),

            'min_delay_seconds' => $minDelay,
            'max_delay_seconds' => $maxDelay,

            'pause_every' => $data['pause_every'] ?? 20,
            'pause_seconds' => $data['pause_seconds'] ?? 300,
        ]);

        foreach ($contacts as $contact) {
            $phone = null;

            if (!empty($contact->phone)) {
                $phone = $contact->phone;
            } elseif ($contact->mainPhone) {
                $phone = $contact->mainPhone->numero;
            }

            CampaignSendContact::create([
                'campaign_send_id' => $send->id,
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'phone' => $phone,
                'status' => 'pending',
            ]);
        }

        ProcessCampaignSendJob::dispatch($send->id);

        return response()->json([
            'success' => true,
            'send_id' => $send->id,
            'contacts' => $contacts->count(),
            'target_type' => $targetType,
            'target_value' => $targetValue,
        ]);
    }

    public function show(CampaignSend $campaignSend)
    {
        if ($campaignSend->user_id !== Auth::id()) {
            abort(403);
        }

        return $campaignSend->load([
            'campaign',
            'contacts.contact',
        ]);
    }
}