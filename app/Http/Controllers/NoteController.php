<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
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
        $request->validate([
            'title' => 'required',
            'created_day' => 'required',
            'status' => ['nullable', Rule::in(['em_andamento', 'pendente', 'concluida'])],
        ]);

        $note = Note::create([
            'user_name' => $this->getUserName(),
            'title' => $request->title,
            'created_day' => $request->created_day,
            'content' => $request->content,
            'category_id' => $request->category_id ?: null,
            'status' => $request->status ?: 'em_andamento',
            'priority' => $request->priority ?: 'media',
            'tags' => $request->tags ? json_decode($request->tags, true) : [],
        ]);

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
        $request->validate([
            'title' => 'required',
            'created_day' => 'required',
            'status' => ['nullable', Rule::in(['em_andamento', 'pendente', 'concluida'])],
        ]);
        $note->update([
            'title' => $request->title,
            'created_day' => $request->created_day,
            'content' => $request->content,
            'category_id' => $request->category_id ?: null,
            'status' => $request->status ?: 'em_andamento',
            'priority' => $request->priority ?: 'media',
            'tags' => $request->tags ? json_decode($request->tags, true) : [],
        ]);
        $action = $previousStatus !== 'concluida' && $note->status === 'concluida'
            ? 'note_completed'
            : 'note_updated';
        $description = $action === 'note_completed'
            ? "Concluiu a nota \"{$note->title}\"."
            : "Atualizou a nota \"{$note->title}\".";
        $this->activityLogger->record($action, $description, $note);

        return redirect()->route('notes.show', $note->id)->with('success', 'Nota atualizada!');
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
}
