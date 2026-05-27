<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;

Route::get('/', [NoteController::class, 'index']);

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');

Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');

Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');

Route::post('/notes/{id}/section', [NoteController::class, 'addSection'])->name('notes.section');

Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');

Route::get('/notes/{id}/section/{sectionId}/edit', [NoteController::class, 'editSection'])->name('notes.section.edit');

Route::put('/notes/{id}/section/{sectionId}', [NoteController::class, 'updateSection'])->name('notes.section.update');

Route::patch('/notes/{id}/section/{sectionId}/complete', [NoteController::class, 'completeSection'])->name('notes.section.complete');