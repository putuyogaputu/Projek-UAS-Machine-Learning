<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

// 1. Halaman utama (Menampilkan form input kalender)
Route::get('/', [StockController::class, 'index']);

// 2. Menerima data dari tombol Submit (POST) dan menampilkan grafik
Route::post('/predict', [StockController::class, 'predict'])->name('predict');

// 3. PERBAIKAN: Mencegah error jika user me-refresh halaman grafik (F5) atau mengetik URL manual
Route::get('/predict', function () {
    return redirect('/');
});
