<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/*
 * Simulasi script server deploy (tanpa server): rantai import data dan .env production.
 */

beforeEach(function () {
    $this->dir = storage_path('framework/testing/deploy-'.uniqid());
    File::ensureDirectoryExists($this->dir.'/data');
    File::ensureDirectoryExists($this->dir.'/bin');

    foreach (['bsd-city-data.json', 'bsd-city-update-2.json', 'bsd-city-update-3.json', 'bsd-city-update-4.json'] as $file) {
        File::put($this->dir.'/data/'.$file, '{"versi": 1}');
    }

    // "php artisan" palsu: catat perintah import (urutan), gagal kalau diminta lewat FAIL_FILE.
    File::put($this->dir.'/bin/artisan', <<<'SH'
        #!/usr/bin/env bash
        [ "$1" = "backup:run" ] && exit 0
        echo "$1 $(basename "$2") ${3:-}" >> "$LOG"
        [ -n "${FAIL_FILE:-}" ] && [ "$(basename "$2")" = "$FAIL_FILE" ] && exit 1
        exit 0
        SH);
    chmod($this->dir.'/bin/artisan', 0755);
});

afterEach(fn () => File::deleteDirectory($this->dir));

function runImports(string $dir, array $env = []): array
{
    File::delete($dir.'/log');
    $result = Process::path(base_path())->env([
        'ARTISAN' => $dir.'/bin/artisan',
        'MARK_DIR' => $dir.'/markers',
        'DATA_DIR' => $dir.'/data',
        'LOG' => $dir.'/log',
        ...$env,
    ])->run(['bash', 'scripts/server/run-imports.sh']);

    return [$result, File::exists($dir.'/log') ? array_values(array_filter(explode("\n", File::get($dir.'/log')))) : []];
}

it('mengimpor data → update-2 → update-3 → update-4 berurutan, --fresh hanya pertama kali', function () {
    [$result, $log] = runImports($this->dir);

    expect($result->successful())->toBeTrue()
        ->and($log)->toBe([
            'import:bsd-data bsd-city-data.json --fresh',
            'import:bsd-update bsd-city-update-2.json --force',
            'import:bsd-update bsd-city-update-3.json --force',
            'import:bsd-update bsd-city-update-4.json --force',
        ]);

    // Tidak ada yang berubah: tidak ada import.
    [, $log] = runImports($this->dir);
    expect($log)->toBe([]);

    // Hanya update-3 berubah: hanya update-3.
    File::put($this->dir.'/data/bsd-city-update-3.json', '{"versi": 2}');
    [, $log] = runImports($this->dir);
    expect($log)->toBe(['import:bsd-update bsd-city-update-3.json --force']);
});

it('mengimpor ulang semua update dengan urutan yang sama kalau data utama berubah', function () {
    runImports($this->dir);

    File::put($this->dir.'/data/bsd-city-data.json', '{"versi": 2}');
    [$result, $log] = runImports($this->dir);

    expect($result->successful())->toBeTrue()
        ->and($result->output())->toContain('marker update dihapus')
        ->and($log)->toBe([
            'import:bsd-data bsd-city-data.json --force',
            'import:bsd-update bsd-city-update-2.json --force',
            'import:bsd-update bsd-city-update-3.json --force',
            'import:bsd-update bsd-city-update-4.json --force',
        ]);
});

it('berhenti (deploy gagal) kalau import gagal, dan mencobanya lagi di deploy berikutnya', function () {
    [$result, $log] = runImports($this->dir, ['FAIL_FILE' => 'bsd-city-update-3.json']);

    expect($result->failed())->toBeTrue()
        ->and($log)->toBe([
            'import:bsd-data bsd-city-data.json --fresh',
            'import:bsd-update bsd-city-update-2.json --force',
            'import:bsd-update bsd-city-update-3.json --force',
        ]);

    [$result, $log] = runImports($this->dir);
    expect($result->successful())->toBeTrue()
        ->and($log)->toBe([
            'import:bsd-update bsd-city-update-3.json --force',
            'import:bsd-update bsd-city-update-4.json --force',
        ]);
});

it('memakai script import di deploy.yml, setelah migrate dan sebelum cache dibangun', function () {
    $deploy = File::get(base_path('.github/workflows/deploy.yml'));

    expect($deploy)->toContain('bash scripts/server/run-imports.sh')
        ->and(strpos($deploy, 'migrate --force'))->toBeLessThan(strpos($deploy, 'run-imports.sh'))
        ->and(File::get(base_path('scripts/server/run-imports.sh')))->toContain('bsd-city-update-4.json');
});

it('menyetel SESSION_SECURE_COOKIE=true di .env lewat ensure-env.sh (idempotent, tanpa baris dobel)', function () {
    File::put($this->dir.'/.env', "APP_ENV=production\nAPP_DEBUG=false\nLOG_LEVEL=warning\nINERTIA_DEVTOOLS_ENABLED=false\nSITE_INDEXABLE=false\nSESSION_SECURE_COOKIE=false\nSESSION_SECURE_COOKIE=false");
    File::put($this->dir.'/bin/php', "#!/bin/sh\nexit 0\n");
    chmod($this->dir.'/bin/php', 0755);
    $run = fn () => Process::path($this->dir)->env(['PATH' => $this->dir.'/bin:'.getenv('PATH')])->run(['bash', base_path('scripts/server/ensure-env.sh')]);

    $first = $run();
    expect($first->successful())->toBeTrue()
        ->and($first->output())->toContain('Diubah: SESSION_SECURE_COOKIE=true')
        ->and(substr_count(File::get($this->dir.'/.env'), 'SESSION_SECURE_COOKIE='))->toBe(1)
        ->and(File::get($this->dir.'/.env'))->toContain('SESSION_SECURE_COOKIE=true')
        ->and(File::get($this->dir.'/.env'))->toContain('SITE_INDEXABLE=false');

    expect($run()->output())->toContain('.env sudah sesuai');
});
