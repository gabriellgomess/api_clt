<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientTokenController;
use Illuminate\Support\Facades\Route;

// Redireciona raiz para o painel admin
Route::get('/', fn () => redirect()->route('admin.tokens.index'));

// Autenticação do painel admin
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Painel admin (protegido por autenticação)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.tokens.index'));

    Route::resource('tokens', ClientTokenController::class, [
        'parameters' => ['tokens' => 'clientToken'],
    ])->except(['show']);

    Route::post('tokens/{clientToken}/regenerate', [ClientTokenController::class, 'regenerate'])
        ->name('tokens.regenerate');

    Route::patch('tokens/{clientToken}/toggle', [ClientTokenController::class, 'toggle'])
        ->name('tokens.toggle');
});
