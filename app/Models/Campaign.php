<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'type',
        'file_path',
        'user_id'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
