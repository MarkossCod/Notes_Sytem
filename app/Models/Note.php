<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'user_name',
        'title',
        'created_day',
        'content',
        'category_id',
        'status',
        'priority',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}