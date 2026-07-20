<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Relaciona a nota a uma categoria opcional sem excluir a nota quando a categoria for removida. */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });
    }

    /** Remove a chave estrangeira de categoria das notas. */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
