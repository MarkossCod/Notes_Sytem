<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    private function getUserName()
    {
        return session('user_name');
    }

    public function index()
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $notes = Note::with('category')
            ->where('user_name', $this->getUserName())
            ->latest()
            ->get();

        $totalNotes = $notes->count();
        $recentNotesCount = $notes->filter(function ($n) {
            return \Carbon\Carbon::parse($n->created_day)->gte(now()->subDays(7));
        })->count();

        return view('notes.index', compact('notes', 'totalNotes', 'recentNotesCount'));
    }

    public function create()
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $categories = \App\Models\Category::where('user_name', $this->getUserName())
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('notes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $request->validate([
            'title'       => 'required',
            'created_day' => 'required',
        ]);

        $note = Note::create([
                'user_name'   => $this->getUserName(),
                'title'       => $request->title,
                'created_day' => $request->created_day,
                'content'     => $request->content,
                'category_id' => $request->category_id ?: null,
                'status'      => $request->status ?: 'em_andamento',
                'priority'    => $request->priority ?: 'media',
                'tags'        => $request->tags ? json_decode($request->tags, true) : [],
        ]);

        return redirect()->route('notes.show', $note->id);
    }

    public function show($id)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $note = Note::with('sections')
            ->where('user_name', $this->getUserName())
            ->findOrFail($id);
        $categories = \App\Models\Category::where('user_name', $this->getUserName())
            ->where('active', true)->orderBy('name')->get();
        return view('notes.show', compact('note', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $note = Note::where('user_name', $this->getUserName())->findOrFail($id);
        $request->validate(['title' => 'required', 'created_day' => 'required']);
        $note->update([
            'title' => $request->title,
            'created_day' => $request->created_day,
            'content' => $request->content,
            'category_id' => $request->category_id ?: null,
            'status' => $request->status ?: 'em_andamento',
            'priority' => $request->priority ?: 'media',
            'tags' => $request->tags ? json_decode($request->tags, true) : [],
        ]);
        return redirect()->route('notes.show', $note->id)->with('success', 'Nota atualizada!');
    }

    public function destroy($id)
    {
        Note::where('user_name', $this->getUserName())->findOrFail($id)->delete();
        return redirect()->route('notes.index');
    }
}
