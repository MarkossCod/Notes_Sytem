<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteUser extends Model
{
    /** Dados mantidos pelo cadastro publico e pela administracao. */
    protected $fillable = [
        'user_name',
        'password',
        'secret_question',
        'secret_answer',
        'role',
        'active',
        'last_login_at',
    ];

    /** Impede que credenciais e respostas de recuperacao sejam serializadas. */
    protected $hidden = [
        'password',
        'secret_answer',
    ];

    /** Normaliza os campos de estado e ultimo acesso. */
    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /** Informa se a conta possui permissao administrativa. */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Informa se a conta esta liberada para autenticacao. */
    public function isActive(): bool
    {
        return $this->active;
    }
}
