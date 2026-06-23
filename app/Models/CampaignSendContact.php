<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSendContact extends Model
{
    protected $fillable = [
        'campaign_send_id',
        'campaign_id',
        'contact_id',
        'message_id',
        'phone',
        'status',
        'error',
        'queued_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at'   => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function send()
    {
        return $this->belongsTo(CampaignSend::class, 'campaign_send_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}