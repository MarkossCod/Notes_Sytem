<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Note extends Model
{
    use SoftDeletes;

    /** Campos que podem ser preenchidos pelos fluxos de criação e edição. */
    protected $fillable = [
        'user_name',
        'title',
        'created_day',
        'content',
        'category_id',
        'status',
        'priority',
        'tags',
        'attachments',
    ];

    /** Converte automaticamente listas JSON do banco em arrays PHP. */
    protected $casts = [
        'tags' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Remove os arquivos físicos somente quando a nota é excluída em definitivo.
     * A exclusão comum mantém os anexos disponíveis para uma futura restauração.
     */
    protected static function booted(): void
    {
        static::forceDeleted(function (Note $note): void {
            $note->deleteAttachmentFiles();
        });
    }

    /** Divisões antigas mantidas apenas por compatibilidade com registros existentes. */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /** Categoria escolhida para organizar a nota. */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Estima o espaço ocupado pelo texto, etiquetas e arquivos da nota. */
    public function estimatedSizeInBytes(): int
    {
        $attachmentBytes = collect($this->attachments ?? [])
            ->sum(fn (array $attachment): int => (int) ($attachment['size'] ?? 0));

        return strlen((string) $this->title)
            + strlen((string) $this->content)
            + strlen((string) json_encode($this->tags ?? []))
            + $attachmentBytes;
    }

    /** Formata o tamanho estimado para exibição na Lixeira. */
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

    /** Apaga do disco todos os anexos registrados nesta nota. */
    private function deleteAttachmentFiles(): void
    {
        foreach ($this->attachments ?? [] as $attachment) {
            $path = $attachment['path'] ?? null;
            $disk = $attachment['disk'] ?? config('filesystems.default', 'local');

            if (! $path) {
                continue;
            }

            try {
                Storage::disk($disk)->delete($path);
            } catch (Throwable) {
                // A nota deve continuar sendo excluída mesmo se o arquivo já não existir.
            }
        }
    }
}
