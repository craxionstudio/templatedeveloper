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
use App\Models\Lead;
use App\Models\Promo;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->seed();
    Lead::query()->create(['name' => 'Budi', 'whatsapp' => '6281234567890', 'cluster_id' => Cluster::first()->id, 'utm_source' => 'facebook']);
});

function adminAs(UserRole $role): User
{
    return User::where('role', $role->value)->firstOrFail();
}

it('merender semua halaman admin untuk super admin', function (string $url) {
    $ids = [
        '{cluster}' => Cluster::first()->id, '{kawasan}' => Kawasan::first()->id, '{promo}' => Promo::first()->id,
        '{facility}' => Facility::first()->id, '{fcat}' => FacilityCategory::first()->id, '{dev}' => FutureDevelopment::first()->id,
        '{article}' => Article::first()->id, '{acat}' => ArticleCategory::first()->id, '{tag}' => Tag::first()->id,
        '{author}' => Author::first()->id, '{lead}' => Lead::first()->id, '{user}' => User::first()->id,
    ];

    $this->actingAs(adminAs(UserRole::SuperAdmin))->get(strtr($url, $ids))->assertOk();
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
    '/admin/leads', '/admin/leads/{lead}/edit', '/admin/newsletter-subscribers', '/admin/newsletter-subscribers/create',
    '/admin/redirects', '/admin/redirects/create', '/admin/users', '/admin/users/create', '/admin/users/{user}/edit',
    '/admin/pengaturan/global', '/admin/pengaturan/menu', '/admin/pengaturan/beranda', '/admin/pengaturan/properti',
    '/admin/pengaturan/detail-kawasan', '/admin/pengaturan/detail-rumah', '/admin/pengaturan/fasilitas',
    '/admin/pengaturan/artikel', '/admin/pengaturan/detail-artikel', '/admin/pengaturan/tentang-kami',
    '/admin/pengaturan/kontak', '/admin/pengaturan/terima-kasih', '/admin/pengaturan/kebijakan-privasi',
]);

it('membatasi menu sesuai role', function (UserRole $role, string $url, int $status) {
    $url = str_replace('{lead}', (string) Lead::first()->id, $url);

    $this->actingAs(adminAs($role))->get($url)->assertStatus($status);
})->with([
    'admin konten → cluster' => [UserRole::AdminKonten, '/admin/clusters', 200],
    'admin konten → settings halaman' => [UserRole::AdminKonten, '/admin/pengaturan/beranda', 200],
    'admin konten → lead' => [UserRole::AdminKonten, '/admin/leads', 403],
    'admin konten → user' => [UserRole::AdminKonten, '/admin/users', 403],
    'marketing → lead' => [UserRole::Marketing, '/admin/leads', 200],
    'marketing → detail lead' => [UserRole::Marketing, '/admin/leads/{lead}/edit', 200],
    'marketing → newsletter' => [UserRole::Marketing, '/admin/newsletter-subscribers', 200],
    'marketing → cluster' => [UserRole::Marketing, '/admin/clusters', 403],
    'marketing → settings halaman' => [UserRole::Marketing, '/admin/pengaturan/beranda', 403],
    'marketing → pengaturan global' => [UserRole::Marketing, '/admin/pengaturan/global', 403],
    'marketing → profil lokasi' => [UserRole::Marketing, '/admin/profil-lokasi', 403],
]);

it('tidak mengizinkan marketing menghapus lead', function () {
    $lead = Lead::first();

    expect(adminAs(UserRole::Marketing)->can('delete', $lead))->toBeFalse()
        ->and(adminAs(UserRole::Marketing)->can('update', $lead))->toBeTrue()
        ->and(adminAs(UserRole::SuperAdmin)->can('delete', $lead))->toBeTrue()
        ->and(adminAs(UserRole::SuperAdmin)->can('create', Lead::class))->toBeFalse();
});
