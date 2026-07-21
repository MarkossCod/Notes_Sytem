<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PanelController;

/* Pagina de entrada e entrega local dos icones da aplicacao. */
Route::get('/', function () {
    return view('splash');
});

Route::get('/icons/{file}', function ($file) {
    $path = public_path('icons/' . $file);
    if (!file_exists($path)) abort(404);
    return response()->file($path);
});

/* Fluxos publicos de autenticacao, cadastro e recuperacao de acesso. */
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->name('register.store');

Route::get('/recover', [LoginController::class, 'showRecover'])->name('recover');
Route::post('/recover', [LoginController::class, 'recoverQuestion'])->name('recover.question');
Route::get('/recover/answer', function() { return redirect()->route('recover'); });
Route::post('/recover/answer', [LoginController::class, 'recoverReset'])->name('recover.reset');

/* Recursos privados disponiveis somente para contas autenticadas e ativas. */
Route::middleware('note.auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');

    /* Criacao, consulta, atualizacao e envio de notas para a Lixeira. */
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{id}/attachments/{attachment}', [NoteController::class, 'showAttachment'])
        ->whereNumber(['id', 'attachment'])
        ->name('notes.attachments.show');
    Route::get('/notes/{id}', [NoteController::class, 'show'])->name('notes.show');
    Route::put('/notes/{id}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');

    /* Restauracao e exclusao definitiva de notas removidas logicamente. */
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::patch('/trash/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/{id}', [TrashController::class, 'destroy'])->name('trash.destroy');
    Route::delete('/trash', [TrashController::class, 'empty'])->name('trash.empty');

    /* Organizacao das notas por categorias exclusivas de cada usuario. */
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{id}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    /* Administracao de contas protegida por permissao de administrador. */
    Route::prefix('admin')->name('admin.')->middleware('note.admin')->group(function (): void {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('users.password');
    });
});
