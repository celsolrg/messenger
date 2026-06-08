<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/', function () {
    return view('app');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Páginas SPA
|--------------------------------------------------------------------------
*/

Route::get('/contatos', function () {
    return view('contacts.index');
})->name('contacts.page');

Route::get('/campanhas', function () {
    return view('campaigns.index');
})->name('campaigns.page');

/*
|--------------------------------------------------------------------------
| Rotas Web de Contatos
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/contatos/novo', [ContactController::class, 'create'])
        ->name('contacts.create');

    Route::post('/contatos', [ContactController::class, 'store'])
        ->name('contacts.store');

    Route::get('/contatos/importar', [ContactImportController::class, 'form'])
        ->name('contacts.import.form');

    Route::post('/contatos/importar', [ContactImportController::class, 'import'])
        ->name('contacts.import');
});
