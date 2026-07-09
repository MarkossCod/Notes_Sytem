<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'user_name',
        'name',
        'description',
        'icon',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
