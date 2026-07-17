<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteUser extends Model
{
    protected $fillable = [
        'user_name',
        'password',
        'secret_question',
        'secret_answer',
        'role',
        'active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'secret_answer',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
