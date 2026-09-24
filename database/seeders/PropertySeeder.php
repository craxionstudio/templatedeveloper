<?php

namespace Database\Seeders;

use App\Enums\ClusterBadge;
use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Models\Cluster;
use App\Models\Kawasan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 3 kawasan, 9 cluster (2 mandiri), 20 tipe rumah — mengikuti docs/design/desktop/02a-02c & 03.
 * Nama, kavling, rentang LT/KT/harga, dan cicilan mulai persis desain; LB, kamar mandi,
 * carport, dan sisa unit per tipe adalah angka dummy yang konsisten dengan rentang tersebut.
 */
class PropertySeeder extends Seeder
{
    private const M = 1_000_000_000;

    private const JT = 1_000_000;

    public function run(): void
    {
        $kawasans = $this->seedKawasans();

        $clusters = self::clusterData();

        foreach ($clusters as $index => [$name, $kawasanSlug, $buildingType, $propertyType, $badge, $status, $featured, $types]) {
            $cluster = Cluster::query()->updateOrCreate(['slug' => Str::slug($name)], [
                'kawasan_id' => $kawasanSlug ? $kawasans[$kawasanSlug]->id : null,
                'name' => $name,
                'building_type' => $buildingType,
                'property_type' => $propertyType,
                'badge' => $badge,
                'status' => $status,
                'summary' => $this->summary($name, $types),
                'description' => $name === 'Vega Garden' ? $this->vegaDescription() : "<p>[DESKRIPSI CLUSTER {$name}: konsep desain, tata ruang, dan keunggulan dibanding cluster lain.]</p>",
                'address' => '[ALAMAT CLUSTER], Serpong, Tangerang Selatan',
                'booking_fee' => 10 * self::JT,
                'specifications' => $this->specifications(),
                'legality' => 'SHGB dipecah per unit · PBG sudah terbit',
                'is_featured' => $featured,
                'sort_order' => $index + 1,
                'is_published' => true,
                // Urutan "Terbaru" = urutan kartu di desain.
                'published_at' => now()->subDays($index + 1)->startOfDay(),
            ]);

            foreach ($types as $order => [$typeName, $lot, $land, $building, $bed, $extraBed, $bath, $floors, $carports, $price, $installment, $units]) {
                $cluster->houseTypes()->updateOrCreate(['slug' => Str::slug($typeName)], [
                    'name' => $typeName,
                    'lot_size' => $lot,
                    'land_area' => $land,
                    'building_area' => $building,
                    'bedrooms' => $bed,
                    'extra_bedrooms' => $extraBed,
                    'bathrooms' => $bath,
                    'floors' => $floors,
                    'carports' => $carports,
                    'price_from' => (int) round($price),
                    'installment_from' => (int) round($installment),
                    'units_available' => $units,
                    'floorplan_alt' => "Denah tipe {$typeName}",
                    'sort_order' => $order + 1,
                    'is_published' => true,
                ]);
            }

            $cluster->refreshAggregates();
        }
    }

    /**
     * Data cluster & tipe. Nama, kavling, LT, KT, harga, dan cicilan mengikuti desain;
     * LB, kamar mandi, carport, dan sisa unit adalah DATA DUMMY (docs/DATA-DUMMY.md).
     *
     * @return list<array<int, mixed>>
     */
    public static function clusterData(): array
    {
        // [nama, kawasan, jenis bangunan, tipe properti, badge, status, unggulan, tipe-tipe]
        // tipe: [nama, kavling, LT, LB, KT, KT+, KM, lantai, carport, harga, cicilan, sisa unit]
        return [
            ['Vega Garden', 'arunika-garden', 'Rumah 2 lantai', PropertyType::Rumah, ClusterBadge::Terlaris, ClusterStatus::ReadyStock, true, [
                ['Altair', '6×12', 72, 90, 3, 0, 2, 2, 1, 1.1 * self::M, 6.9 * self::JT, 8],
                ['Deneb', '7×15', 105, 120, 3, 1, 3, 2, 2, 1.6 * self::M, 9.8 * self::JT, 5],
                ['Rigel', '8×15', 120, 150, 4, 1, 4, 2, 2, 2.2 * self::M, 13.5 * self::JT, 3],
            ]],
            ['Lyra Residence', 'arunika-garden', 'Rumah 2 lantai', PropertyType::Rumah, null, ClusterStatus::ReadyStock, true, [
                ['Aster', '6×12', 72, 90, 3, 0, 2, 2, 1, 1.0 * self::M, 6.2 * self::JT, 10],
                ['Iris', '7×12', 84, 100, 3, 0, 3, 2, 1, 1.3 * self::M, 8.0 * self::JT, 6],
            ]],
            ['Orion Park', 'arunika-garden', 'Rumah 2 lantai', PropertyType::Rumah, null, ClusterStatus::ReadyStock, false, [
                ['Nova', '8×15', 120, 150, 4, 0, 3, 2, 2, 2.2 * self::M, 13.5 * self::JT, 6],
                ['Stella', '8×16', 128, 160, 4, 0, 3, 2, 2, 2.4 * self::M, 14.7 * self::JT, 4],
                ['Luna', '9×18', 162, 190, 5, 0, 4, 2, 2, 3.1 * self::M, 19.1 * self::JT, 3],
                ['Sol', '10×18', 180, 220, 5, 0, 4, 2, 2, 3.6 * self::M, 22.2 * self::JT, 2],
            ]],
            ['Kirana Hills', 'arunika-hills', 'Rumah 2 lantai', PropertyType::Rumah, ClusterBadge::Baru, ClusterStatus::ReadyStock, true, [
                ['Kirana', '8×15', 120, 155, 4, 1, 4, 2, 2, 2.2 * self::M, 13.5 * self::JT, 7],
                ['Kirana Plus', '10×15', 150, 180, 4, 1, 4, 2, 2, 2.9 * self::M, 17.9 * self::JT, 4],
            ]],
            ['Nara Village', 'arunika-hills', 'Rumah 2 lantai', PropertyType::Rumah, null, ClusterStatus::ReadyStock, false, [
                ['Nara', '6×15', 90, 110, 3, 0, 3, 2, 1, 1.4 * self::M, 8.6 * self::JT, 9],
                ['Nara Corner', '8×15', 120, 135, 4, 0, 3, 2, 2, 1.8 * self::M, 11.1 * self::JT, 3],
            ]],
            ['Sora Terrace', 'arunika-lakeside', 'Rumah 1–2 lantai', PropertyType::Rumah, ClusterBadge::Promo, ClusterStatus::ReadyStock, true, [
                ['Sora', '6×10', 60, 45, 2, 0, 1, 1, 1, 580 * self::JT, 3.6 * self::JT, 12],
                ['Sora 2L', '6×12', 72, 70, 3, 0, 2, 2, 1, 820 * self::JT, 5.1 * self::JT, 8],
            ]],
            ['Kalea Townhouse', null, 'Townhouse 3 lantai', PropertyType::Townhouse, null, ClusterStatus::ReadyStock, false, [
                ['Kalea', '5×12', 60, 140, 3, 0, 3, 3, 1, 1.9 * self::M, 11.6 * self::JT, 6],
            ]],
            ['Sora Terrace II', 'arunika-lakeside', 'Rumah 2 lantai', PropertyType::Rumah, ClusterBadge::Segera, ClusterStatus::Inden, false, [
                ['Sora Plus', '6×12', 72, 80, 3, 0, 2, 2, 1, 850 * self::JT, 5.3 * self::JT, 20],
                ['Sora Corner', '8×12', 96, 100, 3, 0, 2, 2, 1, 1.1 * self::M, 6.8 * self::JT, 6],
            ]],
            ['Hana Residence', null, 'Rumah 2 lantai', PropertyType::Rumah, null, ClusterStatus::ReadyStock, false, [
                ['Hana', '7×14', 98, 110, 3, 0, 2, 2, 1, 1.5 * self::M, 9.2 * self::JT, 7],
                ['Hana Suite', '8×15', 120, 150, 4, 0, 3, 2, 2, 2.0 * self::M, 12.3 * self::JT, 4],
            ]],
        ];
    }

    /**
     * Data kawasan. Ringkasan & fasilitas Arunika Garden dari desain 02b/02c;
     * fasilitas Arunika Hills & Lakeside adalah DATA DUMMY (docs/DATA-DUMMY.md).
     *
     * @return list<array<string, mixed>>
     */
    public static function kawasanData(): array
    {
        return [
            [
                'name' => 'Arunika Garden',
                'summary' => 'Kawasan hijau pertama dengan Central Park 5 ha dan clubhouse, paling dekat ke gerbang tol.',
                'about_title' => 'Hidup di tengah taman, tetap dekat ke pusat kota',
                'description' => '<p>[DESKRIPSI KAWASAN: konsep, luas, jumlah unit, dan apa yang membedakan kawasan ini dari kawasan lain di kota Arunika.]</p><p>Setiap cluster di Arunika Garden punya gerbang sendiri, tapi penghuninya berbagi fasilitas kawasan yang sama.</p>',
                'facilities' => [
                    ['icon' => 'sprout', 'title' => 'Central Park 5 ha', 'description' => 'Danau, jogging track, area bermain'],
                    ['icon' => 'house', 'title' => 'Clubhouse', 'description' => 'Kolam renang dan gym'],
                    ['icon' => 'shield', 'title' => 'One gate per cluster', 'description' => 'Satpam 24 jam, CCTV'],
                    ['icon' => 'route', 'title' => 'Akses tol', 'description' => '± 5 menit ke gerbang tol'],
                    ['icon' => 'graduation-cap', 'title' => 'Sekolah', 'description' => 'TK–SMP di dalam kawasan'],
                    ['icon' => 'shopping-cart', 'title' => 'Arunika Walk', 'description' => 'Kuliner dan minimarket'],
                ],
                'is_featured' => true,
            ],
            [
                'name' => 'Arunika Hills',
                'summary' => 'Kawasan berkontur di sisi timur dengan pemandangan bukit dan jalur sepeda.',
                'about_title' => '[JUDUL TENTANG KAWASAN ARUNIKA HILLS]',
                'description' => '<p>[DESKRIPSI KAWASAN: konsep, luas, jumlah unit, dan apa yang membedakan kawasan ini dari kawasan lain di kota Arunika.]</p>',
                'facilities' => [
                    ['icon' => 'graduation-cap', 'title' => 'Sekolah [NAMA]', 'description' => 'TK hingga SMA'],
                    ['icon' => 'dumbbell', 'title' => 'Sport Center', 'description' => 'Futsal, basket, badminton, padel'],
                    ['icon' => 'shield', 'title' => 'One gate per cluster', 'description' => 'Satpam 24 jam, CCTV'],
                ],
                'is_featured' => false,
            ],
            [
                'name' => 'Arunika Lakeside',
                'summary' => 'Kawasan tepi danau untuk keluarga muda, dekat sekolah dan area komersial.',
                'about_title' => '[JUDUL TENTANG KAWASAN ARUNIKA LAKESIDE]',
                'description' => '<p>[DESKRIPSI KAWASAN: konsep, luas, jumlah unit, dan apa yang membedakan kawasan ini dari kawasan lain di kota Arunika.]</p>',
                'facilities' => [
                    ['icon' => 'waves', 'title' => 'Taman tepi danau', 'description' => '[KETERANGAN FASILITAS]'],
                    ['icon' => 'church', 'title' => 'Rumah Ibadah', 'description' => 'Masjid dan gereja di dalam kawasan'],
                    ['icon' => 'shield', 'title' => 'One gate per cluster', 'description' => 'Satpam 24 jam, CCTV'],
                ],
                'is_featured' => false,
            ],
        ];
    }

    /**
     * @return array<string, Kawasan>
     */
    private function seedKawasans(): array
    {
        $data = self::kawasanData();

        $kawasans = [];

        foreach ($data as $index => $kawasan) {
            $slug = Str::slug($kawasan['name']);

            $kawasans[$slug] = Kawasan::query()->updateOrCreate(['slug' => $slug], [
                ...$kawasan,
                'area_ha' => null, // [XX] ha — isi dengan data asli
                'hero_alt' => 'Foto kawasan '.$kawasan['name'],
                'sort_order' => $index + 1,
                'is_published' => true,
                'published_at' => now()->subMonth()->startOfDay(),
            ]);
        }

        return $kawasans;
    }

    /**
     * @param  list<array<int, mixed>>  $types
     */
    private function summary(string $name, array $types): string
    {
        $count = count($types);

        return "{$name} menawarkan {$count} tipe rumah. [RINGKASAN CLUSTER UNTUK KARTU & META DESCRIPTION]";
    }

    private function vegaDescription(): string
    {
        return '<p>Vega Garden dirancang untuk keluarga yang butuh ruang lebih tanpa kehilangan kehangatan rumah. Lantai dasar dibuat terbuka, menyatukan ruang keluarga, ruang makan, dan dapur dengan bukaan lebar ke taman belakang, sehingga cahaya alami dan sirkulasi udara mengalir sepanjang hari.</p>'
            .'<p>Lantai atas berisi tiga kamar tidur dengan kamar utama yang dilengkapi walk-in closet dan kamar mandi dalam. Ruang tambahan di lantai dasar bisa difungsikan sebagai kamar tamu, ruang kerja, atau kamar asisten rumah tangga.</p>'
            .'<p>Cluster ini berada 3 menit dari Central Park dan Arunika Walk, dengan sistem keamanan satu gerbang dan CCTV 24 jam.</p>';
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function specifications(): array
    {
        return [
            ['label' => 'Pondasi', 'value' => 'Tiang pancang & footplat'],
            ['label' => 'Struktur', 'value' => 'Beton bertulang'],
            ['label' => 'Dinding', 'value' => 'Bata ringan, plester aci, cat'],
            ['label' => 'Lantai', 'value' => 'Homogeneous tile 60×60'],
            ['label' => 'Plafon', 'value' => 'Gypsum rangka hollow'],
            ['label' => 'Atap', 'value' => 'Rangka baja ringan, genteng beton'],
            ['label' => 'Kusen', 'value' => 'Aluminium'],
            ['label' => 'Sanitair', 'value' => '[MEREK SANITAIR]'],
            ['label' => 'Listrik', 'value' => '2.200 VA'],
            ['label' => 'Air', 'value' => 'PAM kawasan'],
        ];
    }
}
