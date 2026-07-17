<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

final class StrongPassword
{
    /** Returns the shared password policy used by administrators and recovery. */
    public static function rule(): Password
    {
        return Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols();
    }
}
