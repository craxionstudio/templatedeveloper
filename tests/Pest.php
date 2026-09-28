<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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
