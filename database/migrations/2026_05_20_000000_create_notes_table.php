<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cria a estrutura principal que armazena titulo, data e conteudo das notas. */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('created_day');
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    /** Remove a tabela principal de notas. */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};