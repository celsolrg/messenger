<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPhone extends Model
{
    protected $fillable = [
        'contact_id',
        'ddd',
        'telefone',
        'tipo_telefone',
        'whatsapp',
        'principal',
    ];

    protected $casts = [
        'whatsapp' => 'boolean',
        'principal' => 'boolean',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
