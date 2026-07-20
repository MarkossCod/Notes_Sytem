<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_name', 'action', 'subject_type', 'subject_id', 'description', 'metadata'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    /** Centralizes labels, icons and groups so every dashboard component stays consistent. */
    public static function presentation(string $action): array
    {
        return match ($action) {
            'login' => ['label' => 'Acesso realizado', 'icon' => '↗', 'group' => 'acessos'],
            'account_created' => ['label' => 'Conta criada', 'icon' => '👤', 'group' => 'acessos'],
            'note_created' => ['label' => 'Nota criada', 'icon' => '＋', 'group' => 'notas'],
            'note_updated' => ['label' => 'Nota atualizada', 'icon' => '✎', 'group' => 'notas'],
            'note_completed' => ['label' => 'Nota concluída', 'icon' => '✓', 'group' => 'notas'],
            'note_deleted' => ['label' => 'Nota movida para a lixeira', 'icon' => '⌫', 'group' => 'lixeira'],
            'note_restored' => ['label' => 'Nota restaurada', 'icon' => '↶', 'group' => 'lixeira'],
            'note_permanently_deleted' => ['label' => 'Nota excluída definitivamente', 'icon' => '×', 'group' => 'lixeira'],
            'trash_emptied' => ['label' => 'Lixeira esvaziada', 'icon' => '⌫', 'group' => 'lixeira'],
            'category_created' => ['label' => 'Categoria criada', 'icon' => '▰', 'group' => 'categorias'],
            'category_updated' => ['label' => 'Categoria atualizada', 'icon' => '✎', 'group' => 'categorias'],
            'category_toggled' => ['label' => 'Status da categoria alterado', 'icon' => '◉', 'group' => 'categorias'],
            'category_deleted' => ['label' => 'Categoria removida', 'icon' => '×', 'group' => 'categorias'],
            'user_created' => ['label' => 'Usuário cadastrado', 'icon' => '＋', 'group' => 'administracao'],
            'user_updated' => ['label' => 'Usuário atualizado', 'icon' => '⚙', 'group' => 'administracao'],
            'user_password_reset' => ['label' => 'Senha administrativa redefinida', 'icon' => '🔑', 'group' => 'administracao'],
            default => ['label' => 'Movimentação registrada', 'icon' => '•', 'group' => 'outros'],
        };
    }

    /** Returns the human-readable action label. */
    public function getLabelAttribute(): string
    {
        return self::presentation($this->action)['label'];
    }

    /** Returns the compact icon associated with the action. */
    public function getIconAttribute(): string
    {
        return self::presentation($this->action)['icon'];
    }

    /** Returns the activity filter group. */
    public function getGroupAttribute(): string
    {
        return self::presentation($this->action)['group'];
    }
}
