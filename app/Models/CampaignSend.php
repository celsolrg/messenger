<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSend extends Model
{
    protected $fillable = [
        'user_id',
        'campaign_id',
        'status',
        'total_contacts',
        'total_pending',
        'total_sent',
        'total_failed',
        'delay_seconds',
        'started_at',
        'finished_at',
        'error',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contacts()
    {
        return $this->hasMany(CampaignSendContact::class);
    }

    public function pendingContacts()
    {
        return $this->hasMany(CampaignSendContact::class)
            ->where('status', 'pending');
    }

    public function sentContacts()
    {
        return $this->hasMany(CampaignSendContact::class)
            ->where('status', 'sent');
    }

    public function failedContacts()
    {
        return $this->hasMany(CampaignSendContact::class)
            ->where('status', 'failed');
    }
}