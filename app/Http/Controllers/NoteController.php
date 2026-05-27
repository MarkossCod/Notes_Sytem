<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Section;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::latest()->get();
        return view('notes.index', compact('notes'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'created_day' => 'required'
        ]);

        $note = Note::create([
            'title' => $request->title,
            'created_day' => $request->created_day
        ]);

        return redirect()->route('notes.show', $note->id);
    }

    public function show($id)
    {
        $note = Note::with('sections')->findOrFail($id);
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
        $note = Note::with('sections')->findOrFail($id);
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
        Note::destroy($id);
        return redirect()->route('notes.index');
    }
}