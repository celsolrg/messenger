<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'type',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(CampaignMedia::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function sends()
    {
        return $this->hasMany(CampaignSend::class);
    }

    public function sendContacts()
    {
        return $this->hasMany(CampaignSendContact::class);
    }
}