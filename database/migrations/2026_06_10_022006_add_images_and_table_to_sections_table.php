<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adiciona imagens e tabelas as antigas divisoes de nota. */
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->longText('images')->nullable()->after('section_content');
            $table->longText('table_data')->nullable()->after('images');
        });
    }

    /** Remove os dados estruturados das antigas divisoes. */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['images', 'table_data']);
        });
    }
};