<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Jobs\SendMessageJob;

class CampaignController extends Controller
{
    public function index()
    {
        return Campaign::where('user_id', auth()->id())->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'message' => 'nullable',
            'type' => 'required'
        ]);

        $data['user_id'] = auth()->id();

        return Campaign::create($data);
    }


    public function send($campaignId)
    {
       $campaign = Campaign::where('user_id', auth()->id())
           ->findOrFail($campaignId);

       $contacts = Contact::where('user_id', auth()->id())
           ->where('opt_in', true)
           ->where('ativo', true)
           ->whereNotNull('phone')
           ->get();

      if ($contacts->isEmpty()) {
         return response()->json([
            'error' => 'Nenhum contato ativo com opt-in encontrado'
        ], 400);
    }

    $total = 0;

    foreach ($contacts as $contact) {
        $message = Message::create([
            'campaign_id' => $campaign->id,
            'contact_id'  => $contact->id,
            'phone'       => $contact->phone,
            'message'     => str_replace('{{name}}', $contact->name, $campaign->message),
            'status'      => 'pending',
        ]);

        SendMessageJob::dispatch($message->id)
            ->delay(now()->addSeconds($total * 5));

        $total++;
    }

    return response()->json([
        'message' => 'Campanha adicionada à fila de envio',
        'campaign_id' => $campaign->id,
        'total_contatos' => $total
    ]);
}

    public function show(Campaign $campaign)
    {
        return $campaign;
    }

    public function update(Request $request, Campaign $campaign)
    {
        //
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return response()->json(['message' => 'Campanha deletada']);
    }
}
