<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

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

    /** Estimates the storage occupied by the note's persisted text fields. */
    public function estimatedSizeInBytes(): int
    {
        return strlen((string) $this->title)
            + strlen((string) $this->content)
            + strlen((string) json_encode($this->tags ?? []));
    }

    /** Formats the estimated note size for display. */
    public function estimatedSizeLabel(): string
    {
        $bytes = $this->estimatedSizeInBytes();

        if ($bytes < 1024) {
            return max($bytes, 1) . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }
}
