<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adds the timestamp used by Eloquent's reversible deletion workflow. */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /** Removes reversible deletion support. */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
