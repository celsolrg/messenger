<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();

            $table->string('ddd', 5)->nullable();
            $table->string('telefone', 30);
            $table->string('tipo_telefone', 50)->nullable();
            $table->boolean('whatsapp')->default(true);
            $table->boolean('principal')->default(false);

            $table->timestamps();

            $table->unique(['contact_id', 'ddd', 'telefone'], 'unique_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_phones');
    }
};
