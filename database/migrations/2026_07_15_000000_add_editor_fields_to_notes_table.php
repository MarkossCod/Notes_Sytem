<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adiciona status, prioridade e etiquetas usados pelo editor unificado. */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('status')->default('em_andamento')->after('category_id');
            $table->string('priority')->default('media')->after('status');
            $table->json('tags')->nullable()->after('priority');
        });
    }

    /** Remove os campos complementares do editor. */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority', 'tags']);
        });
    }
};
