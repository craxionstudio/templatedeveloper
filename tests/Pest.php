<?php

use App\Models\Cluster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Helper JSON-LD (dipakai SeoTest & ClusterWithoutTypesTest)
|--------------------------------------------------------------------------
*/

/**
 * @return list<array<string, mixed>>
 */
function jsonLd(TestResponse $response): array
{
    $page = $response->viewData('page');

    return $page['props']['meta']['jsonLd'];
}

/**
 * @param  list<array<string, mixed>>  $graphs
 * @return array<string, mixed>|null
 */
function ofType(array $graphs, string $type): ?array
{
    return collect($graphs)->first(fn (array $g) => $g['@type'] === $type || (is_array($g['@type']) && in_array($type, $g['@type'], true)));
}

/**
 * Foto cluster (wajib di form admin sejak "Admin ringkas"): satu item galeri dengan gambar palsu.
 */
function withClusterPhoto(Cluster $cluster): Cluster
{
    if ($cluster->galleryItems()->doesntExist()) {
        $item = $cluster->galleryItems()->create(['alt' => 'Foto '.$cluster->name, 'sort_order' => 0]);
        $item->addMedia(UploadedFile::fake()->image('foto.jpg', 800, 600))->toMediaCollection('image');
    }

    return $cluster;
}

/**
 * State repeater foto untuk form Create cluster.
 *
 * @return array<string, array<string, mixed>>
 */
function newClusterPhoto(): array
{
    return ['foto' => ['image' => [UploadedFile::fake()->image('foto.jpg', 800, 600)], 'caption' => null]];
}
