<?php

use App\Support\SiteLayout;

it('menormalisasi nomor WhatsApp ke format 62', function (string $input, string $expected) {
    expect(SiteLayout::whatsappUrl($input))->toBe($expected);
})->with([
    ['0812-3456-7890', 'https://wa.me/6281234567890'],
    ['+62 812 3456 7890', 'https://wa.me/6281234567890'],
    ['6281234567890', 'https://wa.me/6281234567890'],
]);

it('menambahkan template pesan WhatsApp', function () {
    expect(SiteLayout::whatsappUrl('0812345678', 'Halo kak'))
        ->toBe('https://wa.me/62812345678?text=Halo%20kak');
});

it('tidak membuat link WhatsApp bila nomor kosong', function () {
    expect(SiteLayout::whatsappUrl(''))->toBeNull()
        ->and(SiteLayout::whatsappUrl(null))->toBeNull();
});

it('tidak membuat link tel: dari placeholder hotline', function () {
    expect(SiteLayout::telUrl('[NO. HOTLINE]'))->toBeNull()
        ->and(SiteLayout::telUrl('(021) 555-1234'))->toBe('tel:0215551234');
});
