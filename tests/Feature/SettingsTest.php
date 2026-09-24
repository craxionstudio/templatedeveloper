<?php

use App\Filament\Pages\Settings\PageSettingsPage;
use App\Settings\PageSettings;

it('memuat semua settings halaman dengan isi awal', function (string $class) {
    $settings = app($class);

    foreach (array_keys($class::defaults()) as $key) {
        expect($settings->{$key})->toBeArray();
    }
})->with(fn () => collect(glob(__DIR__.'/../../app/Settings/*Settings.php'))
    ->map(fn (string $file) => 'App\\Settings\\'.basename($file, '.php'))
    ->reject(fn (string $class) => $class === PageSettings::class)
    ->values()
    ->all());

it('mengisi fallback hanya untuk teks kosong, bukan untuk list', function () {
    $result = PageSettings::withFallback(
        ['title' => '', 'items' => [], 'nested' => ['label' => null, 'on' => false]],
        ['title' => 'Default', 'items' => ['a'], 'nested' => ['label' => 'Label', 'on' => true]],
    );

    expect($result)->toBe(['title' => 'Default', 'items' => [], 'nested' => ['label' => 'Label', 'on' => false]]);
});

it('menggabungkan data form tanpa menghapus key lain dan mengganti list utuh', function () {
    $merged = PageSettingsPage::mergeSettings(
        ['hero' => ['title' => 'Lama', 'url' => '/x'], 'links' => [['a'], ['b']]],
        ['hero' => ['title' => 'Baru'], 'links' => [['c']]],
    );

    expect($merged)->toBe(['hero' => ['title' => 'Baru', 'url' => '/x'], 'links' => [['c']]]);
});
