<?php

namespace App\Console\Commands;

use App\Enums\ClusterBadge;
use App\Enums\ClusterStatus;
use App\Enums\PropertyType;
use App\Models\Area;
use App\Models\Cluster;
use App\Models\GalleryItem;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Models\SeoMeta;
use App\Settings\AboutPageSettings;
use App\Settings\ArticleIndexPageSettings;
use App\Settings\ClusterDetailPageSettings;
use App\Settings\ContactPageSettings;
use App\Settings\FacilityPageSettings;
use App\Settings\HomePageSettings;
use App\Settings\KawasanDetailPageSettings;
use App\Settings\ListingPageSettings;
use App\Support\DummyContent;
use App\Support\IconOptions;
use App\Support\PageCache;
use App\Support\Sitemaps;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;

/**
 * Import data asli properti (docs/data/bsd-city-data.json) ke database.
 *
 * - Upsert per slug: aman dijalankan berkali-kali, tidak membuat duplikat.
 * - Nilai null di JSON tidak menimpa isi yang sudah ada (data yang dilengkapi di admin aman);
 *   untuk record baru, nilai null dibiarkan kosong.
 * - Rentang ("1000-1788", "5-6") dan "3+1" disimpan angka terkecil/utamanya; nilai asli
 *   ditulis di catatan internal tipe rumah.
 */
class ImportBsdData extends Command
{
    protected $signature = 'import:bsd-data
        {path=docs/data/bsd-city-data.json : File JSON (relatif ke root project atau path absolut)}
        {--fresh : Hapus dulu SEMUA kawasan, cluster, tipe rumah, dan promo contoh; nonaktifkan konten contoh lain}
        {--force : Jalankan --fresh di production tanpa konfirmasi}';

    protected $description = 'Import profil lokasi, kawasan, cluster, tipe rumah, dan SEO dari file JSON data asli';

    /** URL halaman di seo_halaman → [settings, section]. Pola {slug} = title/description pattern. */
    private const PAGE_SEO = [
        '/' => [HomePageSettings::class, 'seo'],
        '/properti' => [ListingPageSettings::class, 'seo_cluster'],
        '/properti/kawasan' => [ListingPageSettings::class, 'seo_kawasan'],
        '/fasilitas' => [FacilityPageSettings::class, 'seo'],
        '/artikel' => [ArticleIndexPageSettings::class, 'seo'],
        '/tentang-kami' => [AboutPageSettings::class, 'seo'],
        '/kontak' => [ContactPageSettings::class, 'seo'],
        '/properti/kawasan/{slug}' => [KawasanDetailPageSettings::class, 'seo'],
        '/properti/{slug}' => [ClusterDetailPageSettings::class, 'seo'],
    ];

    /** Kata kunci → ikon (App\Support\IconOptions) untuk fasilitas & keunggulan wilayah. */
    private const ICON_KEYWORDS = [
        'train-front' => ['stasiun', 'commuter', 'krl', 'kereta', 'mrt', 'lrt'],
        'bus' => ['bus', 'shuttle', 'feeder', 'bsd link'],
        'route' => ['tol', 'jorr', 'akses', 'jalan', 'boulevard', 'bandara'],
        'graduation-cap' => ['sekolah', 'pendidikan', 'kampus', 'universit', 'school'],
        'stethoscope' => ['rumah sakit', 'kesehatan', 'klinik', 'hospital'],
        'shopping-cart' => ['belanja', 'mall', 'aeon', 'supermarket'],
        'store' => ['commercial', 'komersial', 'ruko', 'retail', 'kuliner', 'cafe'],
        'dumbbell' => ['gym', 'olahraga', 'jogging', 'kolam renang', 'pool', 'sport', 'tenis', 'basket', 'lapangan', 'track'],
        'waves' => ['danau', 'river', 'sungai', 'lake', 'kolam', 'water'],
        'house' => ['clubhouse', 'club house', 'rumah', 'balcony', 'smart home'],
        'shield' => ['keamanan', 'security', 'cctv', 'gate', 'one gate', 'satpam'],
        'church' => ['ibadah', 'masjid', 'gereja'],
        'building-2' => ['kantor', 'bisnis', 'business', 'cbd', 'gedung'],
        'sprout' => ['taman', 'hijau', 'park', 'garden', 'playground', 'green'],
    ];

    /** @var array<string, int> */
    private array $counts = [];

    public function handle(): int
    {
        $path = $this->resolvePath((string) $this->argument('path'));

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        try {
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error('JSON tidak valid: '.$e->getMessage());

            return self::FAILURE;
        }

        // Satu-satunya konfirmasi: --fresh di production tanpa --force. Dengan --force (deploy otomatis)
        // tidak ada pertanyaan sama sekali; dengan --no-interaction tanpa --force, perintah berhenti.
        if ($this->option('fresh') && app()->isProduction() && ! $this->option('force')
            && ! $this->confirm('--fresh menghapus SEMUA kawasan, cluster, dan tipe rumah di database production. Lanjutkan?')) {
            $this->error('Dibatalkan. Jalankan dengan --force untuk melewati konfirmasi (mis. di script deploy).');

            return self::FAILURE;
        }

        DB::transaction(function () use ($data): void {
            if ($this->option('fresh')) {
                $this->deleteDummyProperties();
            }

            $this->importArea($data['profil_lokasi'] ?? null);
            $kawasanIds = $this->importKawasans($data['kawasan'] ?? []);
            $this->importClusters($data['clusters'] ?? [], $kawasanIds);
            $this->importPageSeo($data['seo_halaman'] ?? []);
        });

        Sitemaps::flush();
        PageCache::flush();

        $this->table(['Data', 'Jumlah'], collect($this->counts)->map(fn (int $n, string $label) => [$label, $n])->values()->all());
        $this->info('Import selesai.');

        return self::SUCCESS;
    }

    private function resolvePath(string $path): string
    {
        return str_starts_with($path, '/') ? $path : base_path($path);
    }

    /**
     * --fresh: hapus permanen semua data properti (dummy) beserta media, galeri, dan SEO-nya.
     * Konten contoh lain dinonaktifkan (tidak dihapus). User, lead (cluster_id jadi null),
     * dan settings tidak disentuh.
     */
    private function deleteDummyProperties(): void
    {
        $morphs = [(new Cluster)->getMorphClass(), (new Kawasan)->getMorphClass()];

        $this->count('Dihapus: tipe rumah', HouseType::query()->count());
        HouseType::query()->each(fn (HouseType $type) => $type->delete()); // media denah ikut terhapus

        GalleryItem::query()->whereIn('galleryable_type', $morphs)->each(fn (GalleryItem $item) => $item->delete());
        SeoMeta::query()->whereIn('seoable_type', $morphs)->delete();

        $this->count('Dihapus: cluster', Cluster::withTrashed()->count());
        Cluster::withTrashed()->each(fn (Cluster $cluster) => $cluster->forceDelete());

        $this->count('Dihapus: kawasan', Kawasan::withTrashed()->count());
        Kawasan::withTrashed()->each(fn (Kawasan $kawasan) => $kawasan->forceDelete());

        // Konten contoh lain (fasilitas, pengembangan mendatang, artikel, promo) tidak dihapus, hanya dinonaktifkan.
        foreach (DummyContent::unpublish() as $type => $n) {
            $this->count("Dinonaktifkan: {$type} contoh", $n);
        }
    }

    /**
     * @param  array<string, mixed>|null  $profile
     */
    private function importArea(?array $profile): void
    {
        if (! $profile) {
            return;
        }

        $area = Area::current();
        $this->fillFilled($area, [
            'name' => $profile['nama'] ?? null,
            'location' => $profile['lokasi'] ?? null,
            'area_ha' => $profile['luas_ha'] ?? null,
            'description' => $profile['deskripsi'] ?? null,
            'advantages' => filled($profile['poin_keunggulan'] ?? null)
                ? collect($profile['poin_keunggulan'])->map(fn (array $point) => [
                    'icon' => self::icon($point['judul'].' '.($point['deskripsi'] ?? '')),
                    'title' => $point['judul'],
                    'description' => $point['deskripsi'] ?? null,
                    'distance' => null,
                ])->values()->all()
                : null,
        ]);
        $area->save();
        $this->count('Profil lokasi', 1);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, int> nama kawasan → id
     */
    private function importKawasans(array $rows): array
    {
        $ids = [];

        foreach (array_values($rows) as $index => $row) {
            $kawasan = Kawasan::withTrashed()->firstOrNew(['slug' => $row['slug']]);
            $isNew = ! $kawasan->exists;

            $kawasan->name = $row['nama'];
            $this->fillFilled($kawasan, [
                'summary' => $row['ringkasan'] ?? null,
                'description' => self::paragraphs($row['deskripsi'] ?? null),
                'area_ha' => $row['luas_ha'] ?? null,
                'opened_year' => $row['tahun_dibuka'] ?? null,
                'facilities' => filled($row['fasilitas'] ?? null)
                    ? collect($row['fasilitas'])->map(fn (string $title) => ['icon' => self::icon($title), 'title' => $title, 'description' => null])->all()
                    : null,
                'access' => filled($row['lokasi_akses'] ?? null) ? array_values($row['lokasi_akses']) : null,
                'is_published' => $row['is_published'] ?? null,
            ]);

            if ($isNew) {
                $kawasan->sort_order = $index + 1;
                $kawasan->published_at = now();
            }

            $kawasan->save();

            if ($kawasan->trashed()) {
                $kawasan->restore();
            }
            $this->importSeo($kawasan, $row['seo'] ?? null);

            $ids[$row['nama']] = $kawasan->id;
            $this->count($isNew ? 'Kawasan baru' : 'Kawasan diperbarui', 1);
        }

        return $ids;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, int>  $kawasanIds
     */
    private function importClusters(array $rows, array $kawasanIds): void
    {
        foreach (array_values($rows) as $index => $row) {
            $cluster = Cluster::withTrashed()->firstOrNew(['slug' => $row['slug']]);
            $isNew = ! $cluster->exists;

            $cluster->name = $row['nama'];
            // Kawasan kosong = cluster mandiri.
            $cluster->kawasan_id = filled($row['kawasan'] ?? null) ? ($kawasanIds[$row['kawasan']] ?? $this->kawasanId($row['kawasan'])) : null;

            $this->fillFilled($cluster, [
                'building_type' => filled($row['jenis_bangunan'] ?? null) ? Str::ucfirst($row['jenis_bangunan']) : null,
                'property_type' => filled($row['tipe_properti'] ?? null) ? PropertyType::tryFrom(Str::lower($row['tipe_properti'])) : null,
                'badge' => filled($row['badge'] ?? null) ? ClusterBadge::tryFrom(Str::lower($row['badge'])) : null,
                'status' => filled($row['status'] ?? null) ? ClusterStatus::tryFrom(Str::slug($row['status'], '_')) : null,
                'launch_year' => $row['tahun_launching'] ?? null,
                'summary' => $row['ringkasan'] ?? null,
                'description' => self::paragraphs($row['deskripsi'] ?? null),
                'facilities' => filled($row['fasilitas'] ?? null) ? array_values($row['fasilitas']) : null,
                'address' => $row['alamat'] ?? null,
                'booking_fee' => $row['booking_fee'] ?? null,
                'prioritas' => $row['prioritas'] ?? null,
                'catatan_internal' => $row['catatan_internal'] ?? null,
                'is_published' => $row['is_published'] ?? null,
            ]);

            $cluster->perlu_dilengkapi = self::mergeChecklist($cluster->perlu_dilengkapi ?? [], $row['perlu_dilengkapi'] ?? []);

            // Urutan "Terbaru" memakai tanggal launching; kalau baru ada tahunnya: 1 Januari tahun itu.
            if (! $cluster->tanggal_launching && $cluster->launch_year) {
                $cluster->tanggal_launching = sprintf('%04d-01-01', $cluster->launch_year);
            }

            if ($isNew) {
                // Cluster prioritas di urutan teratas, sisanya mengikuti urutan file.
                $cluster->sort_order = $row['prioritas'] ?? 100 + $index;
                $cluster->published_at = now();
            }

            $cluster->save();

            if ($cluster->trashed()) {
                $cluster->restore();
            }
            $this->importSeo($cluster, $row['seo'] ?? null);
            $this->importHouseTypes($cluster, $row['tipe'] ?? []);
            $cluster->refreshAggregates();

            $this->count($isNew ? 'Cluster baru' : 'Cluster diperbarui', 1);
            $this->count($cluster->kawasan_id ? '  di kawasan' : '  mandiri', 1);
        }
    }

    private function kawasanId(string $name): ?int
    {
        return Kawasan::query()->where('name', $name)->value('id');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function importHouseTypes(Cluster $cluster, array $rows): void
    {
        $slugs = [];

        foreach (array_values($rows) as $index => $row) {
            $name = filled($row['nama'] ?? null) ? trim($row['nama']) : null;
            // Tipe tanpa nama: slug teknis dari urutannya (tidak tampil di website).
            $slug = $name ? Str::slug($name) : 'tipe-'.($index + 1);
            $slug = in_array($slug, $slugs, true) ? $slug.'-'.($index + 1) : $slug;
            $slugs[] = $slug;

            $notes = [];
            $landArea = self::number($row['luas_tanah'] ?? null, 'LT', 'm²', $notes);
            $bedrooms = self::number($row['kamar_tidur'] ?? null, 'Kamar tidur', null, $notes);
            $bathrooms = self::number($row['kamar_mandi'] ?? null, 'Kamar mandi', null, $notes);
            $floors = self::number($row['lantai'] ?? null, 'Lantai', null, $notes);

            $type = HouseType::query()->firstOrNew(['cluster_id' => $cluster->id, 'slug' => $slug]);
            $isNew = ! $type->exists;

            $this->fillFilled($type, [
                'name' => $name,
                'lot_size' => filled($row['kavling'] ?? null) ? preg_replace('/(\d)\s*x\s*(\d)/i', '$1×$2', $row['kavling']) : null,
                'land_area' => $landArea,
                'building_area' => self::number($row['luas_bangunan'] ?? null, 'LB', 'm²', $notes),
                'bedrooms' => $bedrooms,
                'extra_bedrooms' => $row['kamar_tidur_tambahan'] ?? null,
                'bathrooms' => $bathrooms,
                'floors' => $floors,
                'carports' => self::number($row['carport'] ?? null, 'Carport', null, $notes),
                'price_from' => $row['harga_mulai'] ?? null,
                'installment_from' => $row['cicilan_mulai'] ?? null,
                'catatan_internal' => self::notes($row['catatan_internal'] ?? null, $notes),
            ]);

            if ($isNew) {
                $type->is_published = true;
                // Kolom wajib di database: 0 = belum diisi (tidak tampil di website).
                $type->bedrooms ??= 0;
                $type->extra_bedrooms ??= 0;
                $type->bathrooms ??= 0;
                $type->carports ??= 0;
            }

            $type->sort_order = $index + 1;
            $type->saveQuietly(); // agregat cluster dihitung sekali setelah semua tipe
            $this->count($isNew ? 'Tipe rumah baru' : 'Tipe rumah diperbarui', 1);
        }
    }

    /**
     * @param  array<string, string>|null  $seo
     */
    private function importSeo(Model $model, ?array $seo): void
    {
        $values = array_filter([
            'meta_title' => $seo['meta_title'] ?? null,
            'meta_description' => $seo['meta_description'] ?? null,
        ], 'filled');

        if ($values !== []) {
            $model->seo()->updateOrCreate([], $values);
        }
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function importPageSeo(array $rows): void
    {
        foreach ($rows as $row) {
            [$class, $key] = self::PAGE_SEO[$row['url']] ?? [null, null];

            if (! $class) {
                $this->warn("SEO halaman dilewati (URL tidak dikenal): {$row['url']}");

                continue;
            }

            $settings = app($class);
            $section = $settings->{$key};

            if (str_contains($row['url'], '{slug}')) {
                // "{Nama Kawasan}" / "{Nama Cluster}" → placeholder {name} di pola judul.
                $pattern = fn (?string $text) => filled($text) ? preg_replace('/\{Nama [^}]+\}/u', '{name}', $text) : null;
                $section = [...$section, ...array_filter([
                    'title_pattern' => $pattern($row['meta_title'] ?? null),
                    'description_pattern' => $pattern($row['meta_description'] ?? null),
                ], 'filled')];
            } else {
                $section = [...$section, ...array_filter([
                    'meta_title' => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_description'] ?? null,
                ], 'filled')];
            }

            $settings->{$key} = $section;
            $settings->save();
            $this->count('SEO halaman', 1);
        }
    }

    /**
     * Isi atribut yang nilainya ada. Null / kosong tidak menimpa isi yang sudah ada.
     *
     * @param  array<string, mixed>  $values
     */
    private function fillFilled(Model $model, array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                $model->{$key} = $value;
            }
        }
    }

    /**
     * Angka dari nilai data: 120 → 120; "1000-1788" → 1000; "3+1" → 3; 2.5 → 2.
     * Kalau nilai asli bukan bilangan bulat biasa, nilai aslinya dicatat di $notes.
     *
     * @param  list<string>  $notes
     */
    public static function number(int|float|string|null $value, string $label, ?string $unit, array &$notes): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (floor($value) !== $value) {
                $notes[] = trim("{$label} ".str_replace('.', ',', (string) $value).($unit ? " {$unit}" : ''));
            }

            return (int) floor($value);
        }

        preg_match_all('/\d+/', $value, $matches);
        $numbers = array_map('intval', $matches[0]);

        if ($numbers === []) {
            $notes[] = "{$label} {$value}";

            return null;
        }

        // Rentang ("5-6") → angka terkecil; "3+1" / "5+1+1" → angka pertama.
        $stored = str_contains($value, '+') ? $numbers[0] : min($numbers);

        if ((string) $stored !== trim($value)) {
            $notes[] = trim("{$label} {$value}".($unit ? " {$unit}" : ''));
        }

        return $stored;
    }

    /**
     * Gabungkan catatan dari data dengan nilai asli hasil konversi (tanpa mengulang yang sudah tertulis).
     *
     * @param  list<string>  $converted
     */
    public static function notes(?string $note, array $converted): ?string
    {
        $haystack = Str::lower((string) $note);
        $extra = array_values(array_filter($converted, fn (string $line) => ! str_contains($haystack, Str::lower($line))));

        if ($extra !== []) {
            $note = trim(($note ? $note."\n" : '').'Nilai asli: '.implode('; ', $extra).' (disimpan angka terkecil/utama).');
        }

        return filled($note) ? $note : null;
    }

    /**
     * Checklist dari data digabung dengan yang sudah ada: status "selesai" yang dicentang di admin
     * dipertahankan, item tambahan dari admin tidak hilang.
     *
     * @param  list<array{item: string, selesai?: bool}>  $existing
     * @param  list<string>  $items
     * @return list<array{item: string, selesai: bool}>
     */
    public static function mergeChecklist(array $existing, array $items): array
    {
        $done = collect($existing)->mapWithKeys(fn (array $row) => [$row['item'] => (bool) ($row['selesai'] ?? false)]);

        $merged = collect($items)->filter(fn ($item) => filled($item))
            ->map(fn (string $item) => ['item' => $item, 'selesai' => $done[$item] ?? false]);

        $extra = collect($existing)->reject(fn (array $row) => in_array($row['item'], $items, true))
            ->map(fn (array $row) => ['item' => $row['item'], 'selesai' => (bool) ($row['selesai'] ?? false)]);

        return $merged->concat($extra)->values()->all();
    }

    /**
     * Teks biasa (paragraf dipisah baris kosong) → HTML rich text.
     */
    public static function paragraphs(?string $text): ?string
    {
        if (blank($text)) {
            return null;
        }

        return collect(preg_split('/\n\s*\n/', trim($text)))
            ->map(fn (string $paragraph) => '<p>'.nl2br(e(trim($paragraph)), false).'</p>')
            ->implode('');
    }

    /**
     * Ikon yang paling cocok dari teks fasilitas; null = ikon default (centang).
     */
    public static function icon(string $text): ?string
    {
        $text = Str::lower($text);

        foreach (self::ICON_KEYWORDS as $icon => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword) && array_key_exists($icon, IconOptions::all())) {
                    return $icon;
                }
            }
        }

        return null;
    }

    private function count(string $label, int $n): void
    {
        $this->counts[$label] = ($this->counts[$label] ?? 0) + $n;
    }
}
