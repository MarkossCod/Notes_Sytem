<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Guarda os metadados dos arquivos privados associados a cada nota. */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('tags');
        });
    }

    /** Remove a coluna de metadados ao desfazer a migração. */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->dropColumn('attachments');
        });
    }
};
