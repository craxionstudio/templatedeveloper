<?php

namespace Database\Seeders;

use App\Enums\DevelopmentStatus;
use App\Enums\PromoPlacement;
use App\Models\Cluster;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FutureDevelopment;
use App\Models\Kawasan;
use App\Models\Promo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 2 promo, 9 fasilitas (6 kategori), 4 pengembangan mendatang — teks dari docs/design.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPromos();
        $this->seedFacilities();
        $this->seedFutureDevelopments();
    }

    private function seedPromos(): void
    {
        Promo::query()->updateOrCreate(['title' => 'DP 0% dan gratis biaya KPR di semua cluster Arunika.'], [
            'label' => 'Promo September',
            'description' => 'Berlaku untuk pembelian hingga [TANGGAL]. Syarat & ketentuan berlaku.',
            'placement' => PromoPlacement::HomeBanner,
            'cta_label' => 'Lihat Unit Promo',
            'cta_url' => '/properti',
            'image_alt' => 'Visual promo DP 0% Arunika Land',
            'starts_at' => now()->subWeek()->startOfDay(),
            'ends_at' => now()->addMonth()->endOfDay(),
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $detail = Promo::query()->updateOrCreate(['title' => 'Promo rumah ini'], [
            'label' => 'Promo September',
            'period_label' => '[TANGGAL]',
            'placement' => PromoPlacement::Detail,
            'items' => [
                ['icon' => 'tag', 'title' => 'DP 0%', 'description' => 'Tanpa uang muka untuk pembelian via KPR bank rekanan.'],
                ['icon' => 'gift', 'title' => 'Gratis biaya KPR & AJB', 'description' => 'Biaya provisi, notaris, dan AJB ditanggung developer.'],
                ['icon' => 'zap', 'title' => 'Free upgrade listrik 4.400 VA', 'description' => 'Untuk 10 unit pertama di bulan ini.'],
                ['icon' => 'check', 'title' => 'Cashback hingga Rp 50 jt', 'description' => 'Untuk pembayaran cash bertahap 12x.'],
            ],
            'starts_at' => now()->subWeek()->startOfDay(),
            'ends_at' => now()->addMonth()->endOfDay(),
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $detail->clusters()->sync(
            Cluster::query()->whereIn('slug', ['vega-garden', 'lyra-residence', 'orion-park', 'sora-terrace'])->pluck('id'),
        );
    }

    private function seedFacilities(): void
    {
        $categories = collect([
            ['Olahraga & Rekreasi', 'dumbbell'],
            ['Pendidikan', 'graduation-cap'],
            ['Kesehatan', 'stethoscope'],
            ['Komersial', 'store'],
            ['Keamanan & Transportasi', 'shield'],
            ['Ibadah', 'church'],
        ])->mapWithKeys(fn (array $category, int $index) => [
            $category[0] => FacilityCategory::query()->updateOrCreate(
                ['slug' => Str::slug($category[0])],
                ['name' => $category[0], 'icon' => $category[1], 'sort_order' => $index + 1],
            ),
        ]);

        $kawasan = Kawasan::query()->pluck('id', 'slug');

        // [nama, kategori, kawasan (null = semua kawasan), ikon, deskripsi, unggulan di Home]
        $facilities = [
            ['Central Park', 'Olahraga & Rekreasi', 'arunika-garden', 'sprout', 'Taman 5 ha dengan danau, jogging track 2 km, dan area bermain anak.', true],
            ['Arunika Clubhouse', 'Olahraga & Rekreasi', 'arunika-garden', 'dumbbell', 'Kolam renang dewasa dan anak, gym, studio yoga, dan ruang serbaguna.', true],
            ['Arunika Walk', 'Komersial', 'arunika-garden', 'shopping-cart', 'Area kuliner dan ritel dengan supermarket, kafe, dan klinik.', true],
            ['Sekolah [NAMA]', 'Pendidikan', 'arunika-hills', 'graduation-cap', 'Sekolah TK hingga SMA dengan kurikulum nasional plus.', false],
            ['Klinik & Apotek 24 Jam', 'Kesehatan', 'arunika-garden', 'stethoscope', 'Layanan dokter umum, gigi, dan laboratorium di dalam kawasan.', false],
            ['Sport Center', 'Olahraga & Rekreasi', 'arunika-hills', 'dumbbell', 'Lapangan futsal, basket, badminton, dan padel.', false],
            ['Shuttle Kawasan', 'Keamanan & Transportasi', null, 'bus', 'Bus gratis ke stasiun KRL dan pusat kota di jam sibuk.', true],
            ['Keamanan Terpadu', 'Keamanan & Transportasi', null, 'shield', 'Satu gerbang per cluster, patroli, dan CCTV 24 jam.', false],
            ['Rumah Ibadah', 'Ibadah', 'arunika-lakeside', 'church', 'Masjid dan gereja di dalam kawasan, jalan kaki dari cluster.', false],
        ];

        foreach ($facilities as $index => [$name, $category, $kawasanSlug, $icon, $description, $featured]) {
            Facility::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'facility_category_id' => $categories[$category]->id,
                'kawasan_id' => $kawasanSlug ? $kawasan[$kawasanSlug] : null,
                'icon' => $icon,
                'description' => $description,
                'photo_alt' => 'Foto '.$name,
                'is_featured' => $featured,
                'sort_order' => $index + 1,
                'is_published' => true,
            ]);
        }
    }

    private function seedFutureDevelopments(): void
    {
        $items = [
            ['2026', 'Arunika Walk Tahap 1', 'Area komersial 2 ha dengan 60 tenant F&B dan ritel.', DevelopmentStatus::Beroperasi],
            ['2027', 'Stasiun LRT [NAMA]', 'Stasiun terintegrasi dengan skybridge ke kawasan.', DevelopmentStatus::Konstruksi],
            ['2028', 'Rumah Sakit [NAMA]', 'Rumah sakit tipe B kerja sama dengan [OPERATOR].', DevelopmentStatus::Perencanaan],
            ['2029', 'Arunika CBD', 'Pusat bisnis dengan perkantoran, hotel, dan apartemen.', DevelopmentStatus::Perencanaan],
        ];

        foreach ($items as $index => [$target, $title, $description, $status]) {
            FutureDevelopment::query()->updateOrCreate(['title' => $title], [
                'target' => $target,
                'description' => $description,
                'status' => $status,
                'image_alt' => 'Render '.$title,
                'sort_order' => $index + 1,
                'is_published' => true,
            ]);
        }
    }
}
