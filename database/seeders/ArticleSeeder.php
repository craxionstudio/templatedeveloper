<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Author;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * 9 artikel di 5 kategori (docs/design/desktop/05-artikel & 06-detail-artikel).
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['Berita', 'Kabar terbaru pembangunan dan kegiatan di kota Arunika.'],
            ['Tips Properti', 'Panduan praktis membeli, membiayai, dan merawat rumah.'],
            ['Gaya Hidup', 'Cerita keseharian dan agenda warga kota Arunika.'],
            ['Promo', 'Info promo dan program pembelian yang sedang berjalan.'],
            ['Investasi', 'Pertimbangan nilai properti dan potensi sewa.'],
        ])->mapWithKeys(fn (array $category, int $index) => [
            $category[0] => ArticleCategory::query()->updateOrCreate(
                ['slug' => Str::slug($category[0])],
                ['name' => $category[0], 'description' => $category[1], 'sort_order' => $index + 1],
            ),
        ]);

        $author = Author::query()->updateOrCreate(['slug' => 'tim-redaksi-arunika'], [
            'name' => 'Tim Redaksi Arunika',
            'job_title' => 'Redaksi',
            'bio' => '[BIO SINGKAT PENULIS]',
            'photo_alt' => 'Foto Tim Redaksi Arunika',
        ]);

        $tags = collect(['KPR', 'Rumah Pertama', 'Keuangan', 'Legalitas', 'Kawasan'])
            ->mapWithKeys(fn (string $name) => [
                $name => Tag::query()->updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]),
            ]);

        // [judul, kategori, excerpt, tanggal, highlight, tag]
        $articles = [
            ['5 hal yang perlu dicek sebelum mengajukan KPR rumah pertama', 'Tips Properti', 'Dari rasio cicilan sampai biaya di luar harga rumah, ini daftar cek singkat supaya pengajuan KPR lebih mulus.', '2026-09-18', true, ['KPR', 'Rumah Pertama', 'Keuangan']],
            ['Beda SHM dan SHGB, mana yang lebih aman untuk rumah tinggal?', 'Tips Properti', 'Penjelasan singkat status sertifikat, masa berlaku, dan cara meningkatkan SHGB jadi SHM.', '2026-09-15', false, ['Legalitas', 'Rumah Pertama']],
            ['Progres pembangunan Stasiun LRT capai [XX] persen', 'Berita', 'Pekerjaan skybridge ke kawasan Arunika mulai berjalan bulan ini.', '2026-09-12', false, ['Kawasan']],
            ['Agenda akhir pekan di Central Park bulan ini', 'Gaya Hidup', 'Pasar tani, lari pagi bersama, dan bioskop terbuka untuk penghuni dan pengunjung.', '2026-09-05', false, ['Kawasan']],
            ['Syarat dan cara ikut promo DP 0% September', 'Promo', 'Daftar cluster yang ikut promo, bank rekanan, dan dokumen yang perlu disiapkan.', '2026-09-01', false, ['KPR']],
            ['Kenapa rumah dekat stasiun cenderung lebih cepat disewa', 'Investasi', 'Melihat permintaan sewa di sekitar simpul transportasi dan apa yang dicari penyewa.', '2026-08-28', false, ['Keuangan']],
            ['Checklist serah terima kunci: apa saja yang dicek?', 'Tips Properti', 'Dari retak dinding sampai tekanan air, daftar yang perlu diperiksa sebelum tanda tangan BAST.', '2026-08-22', false, ['Rumah Pertama']],
            ['Arunika Walk tahap 1 resmi dibuka untuk umum', 'Berita', 'Enam puluh tenant kuliner dan ritel kini beroperasi setiap hari mulai pukul 10.00.', '2026-08-15', false, ['Kawasan']],
            ['Ide tata taman belakang untuk lahan 3 meter', 'Gaya Hidup', 'Tanaman yang tahan panas, pilihan paving, dan cara membuat taman terasa lebih luas.', '2026-08-08', false, []],
        ];

        foreach ($articles as [$title, $category, $excerpt, $date, $highlight, $articleTags]) {
            $publishedAt = Carbon::parse($date.' 09:00', config('app.timezone'));

            $article = Article::query()->updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title,
                'article_category_id' => $categories[$category]->id,
                'author_id' => $author->id,
                'excerpt' => $excerpt,
                'body' => $highlight ? $this->kprBody() : $this->placeholderBody($excerpt),
                'cover_alt' => 'Ilustrasi: '.$title,
                'is_highlight' => $highlight,
                'is_published' => true,
                'published_at' => $publishedAt,
            ]);

            $article->tags()->sync(collect($articleTags)->map(fn (string $tag) => $tags[$tag]->id));

            // Tanggal dibuat/diubah mengikuti tanggal terbit supaya "Diperbarui" tidak muncul.
            $article->timestamps = false;
            $article->forceFill(['created_at' => $publishedAt, 'updated_at' => $publishedAt])->saveQuietly();
        }
    }

    private function placeholderBody(string $excerpt): string
    {
        return "<p>{$excerpt}</p><h2>[SUBJUDUL]</h2><p>[ISI ARTIKEL — ganti dengan tulisan asli.]</p>";
    }

    private function kprBody(): string
    {
        return '<p>Membeli rumah pertama lewat KPR adalah keputusan finansial jangka panjang. Sebelum menyerahkan berkas ke bank, ada beberapa hal yang sebaiknya kamu cek supaya prosesnya lancar dan cicilan tetap nyaman dibayar.</p>'
            .'<h2>1. Hitung rasio cicilan terhadap penghasilan</h2><p>Banyak bank menggunakan batas cicilan sekitar 30–40% dari penghasilan bersih bulanan. Jumlahkan juga cicilan lain yang sedang berjalan, seperti kendaraan atau kartu kredit, karena semuanya ikut diperhitungkan.</p>'
            .'<h2>2. Siapkan dana di luar harga rumah</h2><p>Selain uang muka, siapkan biaya lain seperti BPHTB, biaya notaris dan akta, provisi dan administrasi bank, serta asuransi. Tanyakan ke marketing apakah ada promo yang menanggung sebagian biaya ini.</p>'
            .'<blockquote><p>“Cek riwayat kredit sebelum mengajukan. Tunggakan kecil yang terlupa bisa membuat pengajuan tertunda.”</p><cite>[NAMA NARASUMBER], [JABATAN]</cite></blockquote>'
            .'<h2>3. Cek riwayat kredit di SLIK OJK</h2><p>Bank akan melihat riwayat pembayaran pinjamanmu. Kamu bisa mengecek sendiri lewat layanan SLIK OJK dan membereskan tunggakan terlebih dahulu.</p>'
            .'<h2>4. Pahami skema bunga</h2><p>Umumnya bunga KPR fixed di beberapa tahun awal, lalu berubah mengikuti suku bunga pasar (floating). Minta simulasi cicilan untuk periode setelah masa fixed berakhir agar tidak kaget.</p>'
            .'<h2>5. Lengkapi dokumen sejak awal</h2><ul><li>KTP, KK, NPWP, dan buku nikah (jika sudah menikah)</li><li>Slip gaji 3 bulan terakhir dan surat keterangan kerja</li><li>Rekening koran 3–6 bulan terakhir</li><li>Untuk wiraswasta: izin usaha dan laporan keuangan</li></ul>'
            .'<p>Butuh bantuan menghitung simulasi cicilan untuk cluster tertentu? Tim marketing kami bisa bantu hitungkan sesuai penghasilan dan tenor yang kamu inginkan.</p>';
    }
}
