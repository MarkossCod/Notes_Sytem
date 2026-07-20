<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** Campos autorizados para criacao e edicao pela tela de categorias. */
    protected $fillable = [
        'user_name',
        'name',
        'description',
        'icon',
        'color',
        'active',
    ];

    /** Mantem o estado ativo como valor booleano em toda a aplicacao. */
    protected $casts = [
        'active' => 'boolean',
    ];

    /** Retorna as notas associadas a esta categoria. */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
