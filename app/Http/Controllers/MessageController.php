<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return Message::with(['contact', 'campaign'])
            ->whereHas('campaign', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('id', 'desc')
            ->limit(500)
            ->get();
    }

    public function show($id)
    {
        return Message::with(['contact', 'campaign'])
            ->whereHas('campaign', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->findOrFail($id);
    }

    public function stats()
    {
        $base = Message::whereHas('campaign', function ($q) {
            $q->where('user_id', auth()->id());
        });

        return [
            'total' => $base->count(),
            'sent' => (clone $base)->where('status', 'sent')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
            'queued' => (clone $base)->where('status', 'queued')->count(),
        ];
    }
}
