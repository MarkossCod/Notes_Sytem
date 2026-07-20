<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adiciona o estado de conclusao das antigas divisoes. */
    public function up()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->boolean('completed')->default(false);
        });
    }

    /** Remove o estado de conclusao das antigas divisoes. */
    public function down()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('completed');
        });
    }
};