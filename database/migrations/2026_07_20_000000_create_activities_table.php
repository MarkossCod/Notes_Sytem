<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Creates the audit trail used by each user's activity dashboard. */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('user_name')->index();
            $table->string('action')->index();
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_name', 'created_at']);
        });
    }

    /** Removes the activity trail when the migration is rolled back. */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
