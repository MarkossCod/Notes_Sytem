<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Associa cada nota ao nome usado para isolar os dados do usuario. */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('user_name')->default('admin')->after('id');
        });
    }

    /** Remove a associacao da nota ao usuario. */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('user_name');
        });
    }
};