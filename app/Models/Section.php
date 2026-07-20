<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    /** Campos historicos das antigas divisoes de nota mantidos por compatibilidade. */
    protected $fillable = [
        'note_id',
        'section_title',
        'section_content',
        'completed',
        'images',
        'files',
        'table_data',
    ];

    /** Converte os dados estruturados armazenados em texto para tipos de dominio. */
    protected $casts = [
        'completed'  => 'boolean',
        'images'     => 'array',
        'files'      => 'array',
    ];

    /** Retorna a nota proprietaria desta divisao legada. */
    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}