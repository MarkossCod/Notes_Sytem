<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Section;
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
        $notes = Note::with('sections')
            ->where('user_name', $this->getUserName())
            ->latest()
            ->get();

        $totalNotes = $notes->count();
        $totalSections = $notes->sum(fn($n) => $n->sections->count());
        $recentNotesCount = $notes->filter(function ($n) {
            return \Carbon\Carbon::parse($n->created_day)->gte(now()->subDays(7));
        })->count();

        return view('notes.index', compact('notes', 'totalNotes', 'totalSections', 'recentNotesCount'));
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
            'created_day' => 'required'
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
        return view('notes.show', compact('note'));
    }

    public function createSection($id)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $note = Note::with('sections')
            ->where('user_name', $this->getUserName())
            ->findOrFail($id);
        return view('notes.section_create', compact('note'));
    }

    public function addSection(Request $request, $id)
    {
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('section_images', 'public');
                $imagePaths[] = $path;
            }
        }

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('section_files', 'public');
                $filePaths[] = ['name' => $file->getClientOriginalName(), 'path' => $path];
            }
        }

        Section::create([
            'note_id'         => $id,
            'section_title'   => $request->section_title,
            'section_content' => $request->section_content,
            'images'          => $imagePaths,
            'files'           => $filePaths,
        ]);

        return redirect()->route('notes.show', $id)->with('success', 'Divisão adicionada com sucesso!');
    }

    public function editSection($id, $sectionId)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $note = Note::with('sections')
            ->where('user_name', $this->getUserName())
            ->findOrFail($id);
        $section = Section::findOrFail($sectionId);
        return view('notes.section_edit', compact('note', 'section'));
    }

    public function updateSection(Request $request, $id, $sectionId)
    {
        $section = Section::findOrFail($sectionId);

        $imagePaths = json_decode($request->existing_images ?? '[]', true) ?? [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('section_images', 'public');
                $imagePaths[] = $path;
            }
        }

        $filePaths = $section->files ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('section_files', 'public');
                $filePaths[] = ['name' => $file->getClientOriginalName(), 'path' => $path];
            }
        }

        $section->update([
            'section_title'   => $request->section_title,
            'section_content' => $request->section_content,
            'images'          => $imagePaths,
            'files'           => $filePaths,
        ]);

        return redirect()->route('notes.show', $id)->with('success', 'Divisão atualizada!');
    }

    public function completeSection($id, $sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $section->update(['completed' => !$section->completed]);
        return back();
    }

    public function destroy($id)
    {
        Note::where('user_name', $this->getUserName())->findOrFail($id)->delete();
        return redirect()->route('notes.index');
    }
}