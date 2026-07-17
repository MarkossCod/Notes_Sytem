<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class TrashController extends Controller
{
    /** Number of days a note remains available for restoration. */
    private const RETENTION_DAYS = 7;

    /** Displays the current user's deleted notes and trash statistics. */
    public function index(Request $request): View|RedirectResponse
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }

        $loadError = null;

        try {
            $this->purgeExpiredNotes();
            $requestedPerPage = (int) $request->integer('per_page', 10);
            $perPage = in_array($requestedPerPage, [10, 20, 50], true) ? $requestedPerPage : 10;
            $direction = $request->string('order')->value() === 'oldest' ? 'asc' : 'desc';

            $query = Note::onlyTrashed()
                ->with('category')
                ->where('user_name', session('user_name'));

            if ($search = trim($request->string('search')->value())) {
                $query->where('title', 'like', '%' . $search . '%');
            }

            $notes = $query->orderBy('deleted_at', $direction)
                ->paginate($perPage)
                ->withQueryString();

            $allDeletedNotes = Note::onlyTrashed()
                ->where('user_name', session('user_name'))
                ->get();
            $stats = [
                'total' => $allDeletedNotes->count(),
                'retention' => self::RETENTION_DAYS,
                'size' => $this->formatBytes($allDeletedNotes->sum(fn (Note $note) => $note->estimatedSizeInBytes())),
            ];
        } catch (Throwable $exception) {
            Log::error('Falha ao carregar a lixeira.', ['exception' => $exception]);
            $loadError = 'Não foi possível carregar a lixeira. Tente novamente em instantes.';
            $notes = new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]);
            $stats = ['total' => 0, 'retention' => self::RETENTION_DAYS, 'size' => '0 B'];
        }

        return view('notes.trash', compact('notes', 'stats', 'loadError'));
    }

    /** Restores one deleted note owned by the current user. */
    public function restore(int $id): RedirectResponse
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }

        try {
            $this->findOwnedDeletedNote($id)->restore();
            return back()->with('success', 'Nota restaurada com sucesso.');
        } catch (Throwable $exception) {
            Log::error('Falha ao restaurar nota.', ['note_id' => $id, 'exception' => $exception]);
            return back()->with('error', 'Não foi possível restaurar a nota.');
        }
    }

    /** Permanently removes one deleted note after user confirmation. */
    public function destroy(int $id): RedirectResponse
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }

        try {
            $this->findOwnedDeletedNote($id)->forceDelete();
            return back()->with('success', 'Nota excluída permanentemente.');
        } catch (Throwable $exception) {
            Log::error('Falha ao excluir nota permanentemente.', ['note_id' => $id, 'exception' => $exception]);
            return back()->with('error', 'Não foi possível excluir a nota permanentemente.');
        }
    }

    /** Permanently removes all deleted notes owned by the current user. */
    public function empty(): RedirectResponse
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }

        try {
            DB::transaction(function (): void {
                Note::onlyTrashed()
                    ->where('user_name', session('user_name'))
                    ->forceDelete();
            });

            return back()->with('success', 'Lixeira esvaziada com sucesso.');
        } catch (Throwable $exception) {
            Log::error('Falha ao esvaziar a lixeira.', ['exception' => $exception]);
            return back()->with('error', 'Não foi possível esvaziar a lixeira.');
        }
    }

    /** Permanently removes notes whose restoration window has expired. */
    private function purgeExpiredNotes(): void
    {
        Note::onlyTrashed()
            ->where('user_name', session('user_name'))
            ->where('deleted_at', '<=', now()->subDays(self::RETENTION_DAYS))
            ->get()
            ->each->forceDelete();
    }

    /** Finds a deleted note while enforcing ownership. */
    private function findOwnedDeletedNote(int $id): Note
    {
        return Note::onlyTrashed()
            ->where('user_name', session('user_name'))
            ->findOrFail($id);
    }

    /** Converts bytes into a compact human-readable label. */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }
}
