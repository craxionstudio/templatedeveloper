<?php

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Author;
use App\Models\Cluster;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FutureDevelopment;
use App\Models\Kawasan;
use App\Models\Promo;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

function admin(): User
{
    return User::where('email', 'admin@example.com')->firstOrFail();
}

it('merender semua halaman admin untuk admin', function (string $url) {
    $ids = [
        '{cluster}' => Cluster::first()->id, '{kawasan}' => Kawasan::first()->id, '{promo}' => Promo::first()->id,
        '{facility}' => Facility::first()->id, '{fcat}' => FacilityCategory::first()->id, '{dev}' => FutureDevelopment::first()->id,
        '{article}' => Article::first()->id, '{acat}' => ArticleCategory::first()->id, '{tag}' => Tag::first()->id,
        '{author}' => Author::first()->id, '{user}' => User::first()->id,
    ];

    $this->actingAs(admin())->get(strtr($url, $ids))->assertOk();
})->with([
    '/admin',
    '/admin/kawasans', '/admin/kawasans/create', '/admin/kawasans/{kawasan}/edit',
    '/admin/clusters', '/admin/clusters/create', '/admin/clusters/{cluster}/edit',
    '/admin/profil-lokasi', '/admin/profil-developer',
    '/admin/promos', '/admin/promos/create', '/admin/promos/{promo}/edit',
    '/admin/facilities', '/admin/facilities/create', '/admin/facilities/{facility}/edit',
    '/admin/facility-categories', '/admin/facility-categories/{fcat}/edit',
    '/admin/future-developments', '/admin/future-developments/{dev}/edit',
    '/admin/articles', '/admin/articles/create', '/admin/articles/{article}/edit',
    '/admin/article-categories', '/admin/article-categories/{acat}/edit',
    '/admin/tags', '/admin/tags/{tag}/edit', '/admin/authors', '/admin/authors/{author}/edit',
    '/admin/redirects', '/admin/redirects/create', '/admin/users', '/admin/users/create', '/admin/users/{user}/edit',
    '/admin/pengaturan/umum', '/admin/pengaturan/menu', '/admin/pengaturan/beranda', '/admin/pengaturan/properti',
    '/admin/pengaturan/halaman-lain',
]);

it('memindahkan semua user lama ke role Admin dengan akses ke semua menu', function () {
    foreach (['super_admin' => 'lama-super@example.com', 'admin_konten' => 'lama-konten@example.com', 'marketing' => 'lama-marketing@example.com'] as $role => $email) {
        DB::table('users')->insert(['name' => $role, 'email' => $email, 'role' => $role, 'password' => bcrypt('password'), 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    (require base_path('database/migrations/2026_10_05_100100_merge_user_roles_into_admin.php'))->up();

    expect(DB::table('users')->distinct()->pluck('role')->all())->toBe(['admin'])
        ->and(User::factory()->make()->role)->toBe(UserRole::Admin);

    $marketing = User::where('email', 'lama-marketing@example.com')->firstOrFail();
    foreach (['/admin/clusters', '/admin/pengaturan/umum', '/admin/users'] as $url) {
        $this->actingAs($marketing)->get($url)->assertOk();
    }

    expect(User::whereIn('email', ['lama-super@example.com', 'lama-konten@example.com'])->get()->every->isAdmin())->toBeTrue();
});

it('tidak lagi punya menu newsletter', function () {
    expect(Schema::hasTable('newsletter_subscribers'))->toBeFalse();
    $this->actingAs(admin())->get('/admin/newsletter-subscribers')->assertNotFound();
    expect($this->post('/newsletter', ['email' => 'pembaca@example.com'])->status())->toBeIn([404, 405]);
});
