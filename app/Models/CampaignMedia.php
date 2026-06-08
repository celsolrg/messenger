<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMedia extends Model
{
    protected $table = 'campaign_media';

    protected $fillable = [
        'campaign_id',
        'media_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
