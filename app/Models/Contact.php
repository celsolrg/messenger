<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'cpf',
        'name',
        'phone',
        'ddd',
        'tipo_telefone',
        'sexo',
        'email',
        'address',
        'bairro',
        'cidade',
        'uf',
        'cep',
        'data_nascimento',
        'nome_mae',
        'renda',
        'titulo_eleitor',
        'data_inclusao',
        'opt_in',
        'ativo',
        'user_id',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_inclusao' => 'datetime',
        'renda' => 'decimal:2',
        'opt_in' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function phones()
    {
        return $this->hasMany(\App\Models\ContactPhone::class);
    }

    public function mainPhone()
    {
        return $this->hasOne(\App\Models\ContactPhone::class)
            ->where('principal', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HISTÓRICO DE ENVIOS
    |--------------------------------------------------------------------------
    */

    public function campaignSendContacts()
    {
        return $this->hasMany(CampaignSendContact::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}