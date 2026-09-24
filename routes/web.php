<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClusterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KawasanController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PropertyListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Properti. Route kawasan WAJIB didaftarkan sebelum /properti/{cluster} (brief bagian 3).
Route::get('/properti', [PropertyListingController::class, 'clusters'])->name('properti.index');
Route::get('/properti/kawasan', [PropertyListingController::class, 'kawasans'])->name('properti.kawasan');
Route::get('/properti/kawasan/{slug}', [KawasanController::class, 'show'])->name('kawasan.show');
Route::get('/properti/{slug}', [ClusterController::class, 'show'])->name('cluster.show');

Route::get('/fasilitas', FacilityController::class)->name('fasilitas');

Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/kategori/{slug}', [ArticleController::class, 'category'])->name('artikel.kategori');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('artikel.show');

Route::get('/tentang-kami', AboutController::class)->name('tentang');
Route::get('/kontak', ContactController::class)->name('kontak');
Route::get('/kebijakan-privasi', [LegalController::class, 'privacy'])->name('privasi');
Route::get('/terima-kasih', [LegalController::class, 'thankYou'])->name('terima-kasih');
