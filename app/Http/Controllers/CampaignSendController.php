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
    /**
     * Lista os envios do usuário
     */
    public function index()
    {
        return CampaignSend::with('campaign')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    /**
     * Cria um envio
     */
    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',

            'min_delay_seconds' => 'nullable|integer|min:5|max:300',
            'max_delay_seconds' => 'nullable|integer|min:5|max:600',

            'pause_every' => 'nullable|integer|min:1|max:100',
            'pause_seconds' => 'nullable|integer|min:0|max:3600',
        ]);


        $campaign = Campaign::findOrFail($request->campaign_id);

        $contacts = Contact::where('user_id', Auth::id())
            ->where('opt_in', true)
            ->get();

        $send = CampaignSend::create([
            'user_id' => Auth::id(),
            'campaign_id' => $campaign->id,

            'status' => 'pending',

            'total_contacts' => $contacts->count(),
            'total_pending' => $contacts->count(),

            'min_delay_seconds' => $request->min_delay_seconds ?? 20,
            'max_delay_seconds' => $request->max_delay_seconds ?? 60,

            'pause_every' => $request->pause_every ?? 20,
            'pause_seconds' => $request->pause_seconds ?? 300,
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
                'campaign_id'      => $campaign->id,
                'contact_id'       => $contact->id,
                'phone'            => $phone,
                'status'           => 'pending',
            ]);
        }

        ProcessCampaignSendJob::dispatch($send->id);
        return response()->json([
            'success' => true,
            'send_id' => $send->id,
            'contacts' => $contacts->count(),
        ]);
    }

    /**
     * Detalhes do envio
     */
    public function show(CampaignSend $campaignSend)
    {
        return $campaignSend->load([
            'campaign',
            'contacts.contact',
        ]);
    }
}