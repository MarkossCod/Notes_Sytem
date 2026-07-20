<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cria as divisoes historicas vinculadas a uma nota com exclusao em cascata. */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->onDelete('cascade');
            $table->string('section_title');
            $table->longText('section_content');
            $table->timestamps();
        });
    }

    /** Remove a tabela historica de divisoes. */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};