<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'note_id',
        'section_title',
        'section_content',
        'completed',
    ];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}