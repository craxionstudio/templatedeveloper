<?php

use App\Models\Article;
use App\Models\Cluster;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Models\Redirect;
use Illuminate\Database\QueryException;

it('menghitung ulang rentang cluster dari tipe yang dipublikasikan saja', function () {
    $cluster = Cluster::factory()->create();
    HouseType::factory()->for($cluster)->create(['slug' => 'a', 'price_from' => 900_000_000, 'land_area' => 60, 'bedrooms' => 2]);
    $expensive = HouseType::factory()->for($cluster)->create(['slug' => 'b', 'price_from' => 2_000_000_000, 'land_area' => 120, 'bedrooms' => 4]);

    expect($cluster->refresh())
        ->house_types_count->toBe(2)
        ->price_max->toBe(2_000_000_000)
        ->land_area_max->toBe(120);

    $expensive->update(['is_published' => false]);

    expect($cluster->refresh())
        ->house_types_count->toBe(1)
        ->price_max->toBe(900_000_000)
        ->bedrooms_max->toBe(2);
});

it('memisahkan cluster mandiri dari cluster di kawasan', function () {
    $kawasan = Kawasan::factory()->create(['slug' => 'garden']);
    $inside = Cluster::factory()->create(['kawasan_id' => $kawasan->id]);
    $standalone = Cluster::factory()->create();

    expect(Cluster::standalone()->pluck('id')->all())->toBe([$standalone->id])
        ->and(Cluster::inKawasan('mandiri')->pluck('id')->all())->toBe([$standalone->id])
        ->and(Cluster::inKawasan('garden')->pluck('id')->all())->toBe([$inside->id])
        ->and(Cluster::inKawasan(null)->count())->toBe(2)
        ->and($kawasan->clusters()->pluck('id')->all())->not->toContain($standalone->id);
});

it('menjadikan cluster mandiri saat kawasannya dihapus permanen', function () {
    $kawasan = Kawasan::factory()->create();
    $cluster = Cluster::factory()->create(['kawasan_id' => $kawasan->id]);

    $kawasan->forceDelete();

    expect($cluster->refresh()->kawasan_id)->toBeNull();
});

it('hanya menampilkan kawasan yang punya minimal satu cluster dipublikasikan', function () {
    $empty = Kawasan::factory()->create();
    $draftOnly = Kawasan::factory()->create();
    Cluster::factory()->create(['kawasan_id' => $draftOnly->id, 'is_published' => false]);
    $visible = Kawasan::factory()->create();
    Cluster::factory()->create(['kawasan_id' => $visible->id]);

    expect(Kawasan::visible()->pluck('id')->all())->toBe([$visible->id]);
});

it('menolak "kawasan" sebagai slug cluster', function () {
    Cluster::factory()->create(['slug' => 'kawasan']);
})->throws(InvalidArgumentException::class);

it('membuat redirect 301 saat slug berubah', function () {
    $cluster = Cluster::factory()->create(['slug' => 'lama']);
    $cluster->update(['slug' => 'baru']);
    $cluster->update(['slug' => 'terbaru']);

    expect(Redirect::where('from_path', '/properti/lama')->value('to_path'))->toBe('/properti/terbaru')
        ->and(Redirect::where('from_path', '/properti/baru')->value('to_path'))->toBe('/properti/terbaru')
        ->and(Redirect::where('from_path', '/properti/terbaru')->exists())->toBeFalse();

    $kawasan = Kawasan::factory()->create(['slug' => 'k-lama']);
    $kawasan->update(['slug' => 'k-baru']);

    expect(Redirect::where('from_path', '/properti/kawasan/k-lama')->first())
        ->to_path->toBe('/properti/kawasan/k-baru')
        ->status_code->toBe(301);
});

it('mewajibkan slug tipe unik per cluster, bukan global', function () {
    [$a, $b] = Cluster::factory()->count(2)->create();
    HouseType::factory()->for($a)->create(['slug' => 'deneb']);
    HouseType::factory()->for($b)->create(['slug' => 'deneb']);

    expect(fn () => HouseType::factory()->for($a)->create(['slug' => 'deneb']))->toThrow(QueryException::class);
});

it('menyanitasi isi artikel dan menghitung waktu baca', function () {
    $article = Article::factory()->create([
        'body' => '<p onclick="x()">Halo</p><script>alert(1)</script><h2>Sub</h2>'.str_repeat('<p>'.str_repeat('kata ', 100).'</p>', 5),
    ]);

    expect($article->body)
        ->not->toContain('<script>')
        ->not->toContain('onclick')
        ->toContain('<h2>Sub</h2>')
        ->and($article->reading_minutes)->toBe(3);
});

it('menyembunyikan konten yang belum terbit', function () {
    Article::factory()->create(['is_published' => false]);
    Article::factory()->create(['published_at' => now()->addDay()]);
    $visible = Article::factory()->create();

    expect(Article::published()->pluck('id')->all())->toBe([$visible->id]);
});
