<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', fn() => view('login'))->name('login');
Route::get('/', fn() => view('app'));

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/', function () {
    return view('app');
});

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/contatos', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contatos/novo', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contatos', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contatos/importar', [ContactImportController::class, 'form'])->name('contacts.import.form');
    Route::post('/contatos/importar', [ContactImportController::class, 'import'])->name('contacts.import');
});
