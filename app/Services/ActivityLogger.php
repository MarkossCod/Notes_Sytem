<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogger
{
    /** Records a movement without allowing audit failures to interrupt the main action. */
    public function record(string $action, string $description, ?Model $subject = null, array $metadata = [], ?string $userName = null): void
    {
        $owner = $userName ?: session('user_name');
        if (! $owner) {
            return;
        }

        try {
            Activity::create([
                'user_name' => $owner,
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'metadata' => $metadata ?: null,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Nao foi possivel registrar uma movimentacao do usuario.', [
                'action' => $action,
                'user_name' => $owner,
                'exception' => $exception,
            ]);
        }
    }
}
