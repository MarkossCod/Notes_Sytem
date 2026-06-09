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
    ];

    protected $hidden = [
        'password',
        'secret_answer',
    ];
}