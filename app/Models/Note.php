<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'user_name',
        'title',
        'created_day',
        'content'
    ];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}