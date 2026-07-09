<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;

Route::get('/', function () {
    return view('splash');
});

Route::get('/icons/{file}', function ($file) {
    $path = public_path('icons/' . $file);
    if (!file_exists($path)) abort(404);
    return response()->file($path);
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->name('register.store');

Route::get('/password', [LoginController::class, 'showPassword'])->name('password');
Route::post('/password', [LoginController::class, 'checkPassword'])->name('password.check');

Route::get('/recover', [LoginController::class, 'showRecover'])->name('recover');
Route::post('/recover', [LoginController::class, 'recoverQuestion'])->name('recover.question');
Route::get('/recover/answer', function() { return redirect()->route('recover'); });
Route::post('/recover/answer', [LoginController::class, 'recoverReset'])->name('recover.reset');

Route::get('/notes/{id}/section/create', [NoteController::class, 'createSection'])->name('notes.section.create');
Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
Route::post('/notes/{id}/section', [NoteController::class, 'addSection'])->name('notes.section');
Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');
Route::get('/notes/{id}/section/{sectionId}/edit', [NoteController::class, 'editSection'])->name('notes.section.edit');
Route::put('/notes/{id}/section/{sectionId}', [NoteController::class, 'updateSection'])->name('notes.section.update');
Route::patch('/notes/{id}/section/{sectionId}/complete', [NoteController::class, 'completeSection'])->name('notes.section.complete');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
Route::patch('/categories/{id}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');