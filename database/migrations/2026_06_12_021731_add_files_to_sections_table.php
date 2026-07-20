<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adiciona anexos as antigas divisoes de nota. */
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->longText('files')->nullable()->after('images');
        });
    }

    /** Remove os anexos das antigas divisoes. */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('files');
        });
    }
};