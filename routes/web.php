<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClusterController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KawasanController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PropertyListingController;
use App\Http\Controllers\SeoFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// File crawler (Milestone 5): sitemap index + 3 sitemap, robots.txt dinamis, RSS.
Route::get('/sitemap.xml', [SeoFileController::class, 'sitemapIndex'])->name('sitemap');
Route::get('/sitemap-{name}.xml', [SeoFileController::class, 'sitemap'])->whereIn('name', ['pages', 'properti', 'artikel'])->name('sitemap.part');
Route::get('/robots.txt', [SeoFileController::class, 'robots'])->name('robots');

// Properti. Route kawasan & cluster-lainnya WAJIB didaftarkan sebelum /properti/{cluster} (brief bagian 3).
Route::get('/properti', [PropertyListingController::class, 'clusters'])->name('properti.index');
Route::get('/properti/kawasan', [PropertyListingController::class, 'kawasans'])->name('properti.kawasan');
Route::get('/properti/cluster-lainnya', [PropertyListingController::class, 'others'])->name('properti.lainnya');
Route::get('/properti/kawasan/{slug}', [KawasanController::class, 'show'])->name('kawasan.show');
Route::get('/properti/{slug}', [ClusterController::class, 'show'])->name('cluster.show');

Route::get('/fasilitas', FacilityController::class)->name('fasilitas');

Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/feed.xml', [SeoFileController::class, 'feed'])->name('artikel.feed');
Route::get('/artikel/kategori/{slug}', [ArticleController::class, 'category'])->name('artikel.kategori');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('artikel.show');

Route::get('/tentang-kami', AboutController::class)->name('tentang');
Route::get('/kebijakan-privasi', [LegalController::class, 'privacy'])->name('privasi');
// Form lead dihapus (semua lewat WhatsApp): halaman terima kasih lama diarahkan ke beranda.
Route::permanentRedirect('/terima-kasih', '/');
// Halaman Kontak dihapus: info kontak ada di footer semua halaman, menu Kontak membuka WhatsApp.
Route::permanentRedirect('/kontak', '/');

// Pratinjau draft untuk admin: URL bertanda tangan (1 jam, dari tombol "Pratinjau" di form admin), selalu noindex.
Route::middleware('signed')->prefix('pratinjau')->group(function () {
    Route::get('/artikel/{article}', [ArticleController::class, 'preview'])->name('artikel.preview');
    Route::get('/properti/{cluster}', [ClusterController::class, 'preview'])->name('cluster.preview');
    Route::get('/kawasan/{kawasan}', [KawasanController::class, 'preview'])->name('kawasan.preview');
});
