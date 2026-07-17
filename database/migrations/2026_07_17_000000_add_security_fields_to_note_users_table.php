<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adds authorization and account-control fields without invalidating existing users. */
    public function up(): void
    {
        Schema::table('note_users', function (Blueprint $table): void {
            $table->string('role', 20)->default('user')->index()->after('secret_answer');
            $table->boolean('active')->default(true)->index()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('active');
        });

        $adminUpdated = DB::table('note_users')
            ->where('user_name', 'Markos')
            ->update(['role' => 'admin']);

        if ($adminUpdated === 0) {
            $firstUserId = DB::table('note_users')->orderBy('id')->value('id');

            if ($firstUserId) {
                DB::table('note_users')->where('id', $firstUserId)->update(['role' => 'admin']);
            }
        }

        DB::table('note_users')
            ->select(['id', 'secret_answer'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    if ($user->secret_answer && !str_starts_with($user->secret_answer, '$2y$')) {
                        DB::table('note_users')
                            ->where('id', $user->id)
                            ->update(['secret_answer' => Hash::make(strtolower(trim($user->secret_answer)))]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('note_users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'active', 'last_login_at']);
        });
    }
};
