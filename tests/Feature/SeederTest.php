<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Cluster;
use App\Models\Facility;
use App\Models\FutureDevelopment;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Models\Promo;
use App\Support\Rupiah;

beforeEach(fn () => $this->seed());

it('mengisi data dummy sesuai brief bagian 10', function () {
    expect(Kawasan::count())->toBe(3)
        ->and(Cluster::count())->toBe(9)
        ->and(Cluster::standalone()->pluck('name')->sort()->values()->all())->toBe(['Hana Residence', 'Kalea Townhouse'])
        ->and(HouseType::count())->toBe(20)
        ->and(Facility::count())->toBe(9)
        ->and(FutureDevelopment::count())->toBe(4)
        ->and(Article::count())->toBe(9)
        ->and(ArticleCategory::count())->toBe(5)
        ->and(Promo::count())->toBe(2);
});

it('menaruh cluster di kawasan yang benar', function (string $kawasan, array $clusters) {
    expect(Kawasan::where('slug', $kawasan)->first()->clusters()->ordered()->pluck('name')->all())->toBe($clusters);
})->with([
    ['arunika-garden', ['Vega Garden', 'Lyra Residence', 'Orion Park']],
    ['arunika-hills', ['Kirana Hills', 'Nara Village']],
    ['arunika-lakeside', ['Sora Terrace', 'Sora Terrace II']],
]);

it('menghitung rentang kartu cluster persis seperti desain 02a', function (string $slug, int $types, string $price, string $installment) {
    $cluster = Cluster::where('slug', $slug)->first();

    expect($cluster->house_types_count)->toBe($types)
        ->and(Rupiah::range($cluster->price_min, $cluster->price_max))->toBe($price)
        ->and(Rupiah::short($cluster->installment_min))->toBe($installment);
})->with([
    ['vega-garden', 3, 'Rp 1,1 – 2,2 M', 'Rp 6,9 jt'],
    ['lyra-residence', 2, 'Rp 1,0 – 1,3 M', 'Rp 6,2 jt'],
    ['orion-park', 4, 'Rp 2,2 – 3,6 M', 'Rp 13,5 jt'],
    ['kirana-hills', 2, 'Rp 2,2 – 2,9 M', 'Rp 13,5 jt'],
    ['nara-village', 2, 'Rp 1,4 – 1,8 M', 'Rp 8,6 jt'],
    ['sora-terrace', 2, 'Rp 580 – 820 jt', 'Rp 3,6 jt'],
    ['kalea-townhouse', 1, 'Rp 1,9 M', 'Rp 11,6 jt'],
    ['sora-terrace-ii', 2, 'Rp 850 jt – 1,1 M', 'Rp 5,3 jt'],
    ['hana-residence', 2, 'Rp 1,5 – 2,0 M', 'Rp 9,2 jt'],
]);

it('memakai nama tipe dan kavling dari desain untuk Vega Garden', function () {
    $types = Cluster::where('slug', 'vega-garden')->first()->houseTypes;

    expect($types->map(fn (HouseType $t) => "{$t->name} · {$t->lot_size}")->all())
        ->toBe(['Altair · 6×12', 'Deneb · 7×15', 'Rigel · 8×15'])
        ->and($types->map->bedroomsLabel()->all())->toBe(['3', '3+1', '4+1']);
});
