<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cria categorias personalizaveis e exclusivas por usuario. */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->default('📁');
            $table->string('color')->default('#ff7b00');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /** Remove a tabela de categorias. */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
