<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use App\Services\ActivityLogger;
use App\Support\NoteContent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class NoteController extends Controller
{
    /** Limite total de anexos mantidos por nota. */
    private const MAX_ATTACHMENTS = 5;

    /** Coordena o ciclo de vida das notas e registra suas movimentacoes no painel. */
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    /** Retorna a chave usada para isolar as notas do usuario autenticado. */
    private function getUserName()
    {
        return session('user_name');
    }

    /** Lista as notas ativas e calcula os indicadores exibidos na pagina inicial. */
    public function index()
    {
        if (! session('user_name')) {
            return redirect()->route('login');
        }
        $notes = Note::with('category')
            ->where('user_name', $this->getUserName())
            ->latest()
            ->get();

        $totalNotes = $notes->count();
        $recentNotesCount = $notes->filter(function ($n) {
            return Carbon::parse($n->created_day)->gte(now()->subDays(7));
        })->count();
        $categoriesCount = Category::where('user_name', $this->getUserName())->count();
        $trashNotesCount = Note::onlyTrashed()
            ->where('user_name', $this->getUserName())
            ->count();

        return view('notes.index', compact(
            'notes',
            'totalNotes',
            'recentNotesCount',
            'categoriesCount',
            'trashNotesCount'
        ));
    }

    /** Prepara o formulario de criacao com as categorias ativas do usuario. */
    public function create()
    {
        if (! session('user_name')) {
            return redirect()->route('login');
        }
        $categories = Category::where('user_name', $this->getUserName())
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('notes.create', compact('categories'));
    }

    /** Valida, persiste e registra a criacao de uma nova nota. */
    public function store(Request $request)
    {
        if (! session('user_name')) {
            return redirect()->route('login');
        }
        $request->validate($this->validationRules());

        $note = Note::create([
            'user_name' => $this->getUserName(),
            'title' => $request->title,
            'created_day' => $request->created_day,
            'content' => NoteContent::normalizeHtml($request->content),
            'category_id' => $request->category_id ?: null,
            'status' => $request->status ?: 'em_andamento',
            'priority' => $request->priority ?: 'media',
            'tags' => $request->tags ? json_decode($request->tags, true) : [],
            'attachments' => [],
        ]);

        $storedAttachments = [];

        try {
            $storedAttachments = $this->storeAttachments($request->file('attachments', []), $note);
            $note->update(['attachments' => $storedAttachments]);
        } catch (Throwable $exception) {
            $this->deleteStoredAttachments($storedAttachments);
            $note->forceDelete();
            throw $exception;
        }

        $this->activityLogger->record('note_created', "Criou a nota \"{$note->title}\".", $note);

        return redirect()->route('notes.show', $note->id);
    }

    /** Carrega uma nota do usuario para visualizacao ou edicao na interface unificada. */
    public function show($id)
    {
        if (! session('user_name')) {
            return redirect()->route('login');
        }
        $note = Note::with('sections')
            ->where('user_name', $this->getUserName())
            ->findOrFail($id);
        $categories = Category::where('user_name', $this->getUserName())
            ->where('active', true)->orderBy('name')->get();

        return view('notes.show', compact('note', 'categories'));
    }

    /** Atualiza a nota e diferencia no historico uma edicao de uma conclusao. */
    public function update(Request $request, $id)
    {
        $note = Note::where('user_name', $this->getUserName())->findOrFail($id);
        $previousStatus = $note->status;
        $request->validate($this->validationRules());
        $currentAttachments = $note->attachments ?? [];
        $incomingFiles = array_values(array_filter($request->file('attachments', [])));

        if (count($currentAttachments) + count($incomingFiles) > self::MAX_ATTACHMENTS) {
            throw ValidationException::withMessages([
                'attachments' => 'Cada nota pode ter no máximo ' . self::MAX_ATTACHMENTS . ' anexos.',
            ]);
        }

        $storedAttachments = [];

        try {
            $storedAttachments = $this->storeAttachments($incomingFiles, $note);
            $note->update([
                'title' => $request->title,
                'created_day' => $request->created_day,
                'content' => NoteContent::normalizeHtml($request->content),
                'category_id' => $request->category_id ?: null,
                'status' => $request->status ?: 'em_andamento',
                'priority' => $request->priority ?: 'media',
                'tags' => $request->tags ? json_decode($request->tags, true) : [],
                'attachments' => array_merge($currentAttachments, $storedAttachments),
            ]);
        } catch (Throwable $exception) {
            $this->deleteStoredAttachments($storedAttachments);
            throw $exception;
        }
        $action = $previousStatus !== 'concluida' && $note->status === 'concluida'
            ? 'note_completed'
            : 'note_updated';
        $description = $action === 'note_completed'
            ? "Concluiu a nota \"{$note->title}\"."
            : "Atualizou a nota \"{$note->title}\".";
        $this->activityLogger->record($action, $description, $note);

        return redirect()->route('notes.show', $note->id)->with('success', 'Nota atualizada!');
    }

    /** Entrega um anexo privado somente ao proprietário da nota. */
    public function showAttachment(int $id, int $attachment): StreamedResponse
    {
        $note = Note::where('user_name', $this->getUserName())->findOrFail($id);
        $metadata = ($note->attachments ?? [])[$attachment] ?? null;

        abort_unless(is_array($metadata) && ! empty($metadata['path']), 404);

        $disk = $metadata['disk'] ?? config('filesystems.default', 'local');
        $path = $metadata['path'];
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response(
            $path,
            $metadata['name'] ?? basename($path),
            [
                'Content-Type' => $metadata['mime'] ?? 'application/octet-stream',
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /** Aplica exclusao logica para que a nota possa ser restaurada pela Lixeira. */
    public function destroy($id)
    {
        if (! session('user_name')) {
            return redirect()->route('login');
        }

        $note = Note::where('user_name', $this->getUserName())->findOrFail($id);
        $noteTitle = $note->title;
        $note->delete();
        $this->activityLogger->record('note_deleted', "Moveu a nota \"{$noteTitle}\" para a lixeira.", $note);

        return redirect()
            ->route('notes.index')
            ->with('success', "A nota \"{$noteTitle}\" foi movida para a lixeira.");
    }

    /** Regras compartilhadas pelos formulários de criação e edição. */
    private function validationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'created_day' => ['required', 'date'],
            'status' => ['nullable', Rule::in(['em_andamento', 'pendente', 'concluida'])],
            'attachments' => ['nullable', 'array', 'max:' . self::MAX_ATTACHMENTS],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,txt,png,jpg,jpeg,gif,webp',
            ],
        ];
    }

    /** Salva os arquivos no disco configurado e devolve apenas seus metadados. */
    private function storeAttachments(array $files, Note $note): array
    {
        $disk = config('filesystems.default', 'local');
        $stored = [];

        try {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->store("notes/{$note->id}", $disk);
                $stored[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'disk' => $disk,
                    'mime' => $file->getMimeType() ?: 'application/octet-stream',
                    'size' => $file->getSize(),
                ];
            }
        } catch (Throwable $exception) {
            $this->deleteStoredAttachments($stored);
            throw $exception;
        }

        return $stored;
    }

    /** Remove arquivos gravados durante uma operação que não pôde ser concluída. */
    private function deleteStoredAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (! empty($attachment['path'])) {
                Storage::disk($attachment['disk'] ?? config('filesystems.default', 'local'))
                    ->delete($attachment['path']);
            }
        }
    }
}
