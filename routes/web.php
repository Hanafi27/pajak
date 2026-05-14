<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ObjekPajakController;
use App\Http\Controllers\PbbController;
use App\Http\Controllers\WajibPajakController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login.form'));

Route::get('/login', [LoginController::class, 'show'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');
Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [LoginController::class, 'processForgotPassword'])->name('password.forgot.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth.session');

Route::middleware('auth.session')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::middleware('role:petugas')->group(function (): void {
        Route::get('/wajib-pajak', [WajibPajakController::class, 'index'])->name('wajib-pajak.index');
        Route::post('/wajib-pajak', [WajibPajakController::class, 'store'])->name('wajib-pajak.store');
        Route::put('/wajib-pajak/{id_wp}', [WajibPajakController::class, 'update'])->name('wajib-pajak.update');
        Route::delete('/wajib-pajak/{id_wp}', [WajibPajakController::class, 'destroy'])->name('wajib-pajak.destroy');

        Route::get('/objek-pajak', [ObjekPajakController::class, 'index'])->name('objek-pajak.index');
        Route::post('/objek-pajak', [ObjekPajakController::class, 'store'])->name('objek-pajak.store');
        Route::put('/objek-pajak/{id_objek}', [ObjekPajakController::class, 'update'])->name('objek-pajak.update');
        Route::delete('/objek-pajak/{id_objek}', [ObjekPajakController::class, 'destroy'])->name('objek-pajak.destroy');

        Route::get('/pbb', [PbbController::class, 'index'])->name('pbb.index');
        Route::post('/pbb', [PbbController::class, 'store'])->name('pbb.store');
        Route::put('/pbb/{id_pbb}', [PbbController::class, 'update'])->name('pbb.update');
        Route::delete('/pbb/{id_pbb}', [PbbController::class, 'destroy'])->name('pbb.destroy');
    });

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanController::class, 'print'])->name('laporan.print');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
});
