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
        $notes = Note::where('user_name', $this->getUserName())->latest()->get();
        return view('notes.index', compact('notes'));
    }

    public function create()
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        return view('notes.create');
    }

    public function store(Request $request)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $request->validate([
            'title' => 'required',
            'created_day' => 'required'
        ]);

        $note = Note::create([
            'user_name' => $this->getUserName(),
            'title' => $request->title,
            'created_day' => $request->created_day
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

    public function addSection(Request $request, $id)
    {
        Section::create([
            'note_id' => $id,
            'section_title' => $request->section_title,
            'section_content' => $request->section_content
        ]);
        return back()->with('success', 'Divisão adicionada com sucesso!');
    }

    public function editSection($id, $sectionId)
    {
        if (!session('user_name')) {
            return redirect()->route('login');
        }
        $note = Note::with('sections')
            ->where('user_name', $this->getUserName())
            ->findOrFail($id);
        $editingSection = Section::findOrFail($sectionId);
        return view('notes.show', compact('note', 'editingSection'));
    }

    public function updateSection(Request $request, $id, $sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $section->update([
            'section_title' => $request->section_title,
            'section_content' => $request->section_content
        ]);
        return redirect()->route('notes.show', $id)->with('success', 'Divisão atualizada com sucesso!');
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