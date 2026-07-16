<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TrashController;

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

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
Route::put('/notes/{id}', [NoteController::class, 'update'])->name('notes.update');
Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');

Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
Route::patch('/trash/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
Route::delete('/trash/{id}', [TrashController::class, 'destroy'])->name('trash.destroy');
Route::delete('/trash', [TrashController::class, 'empty'])->name('trash.empty');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
Route::patch('/categories/{id}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
