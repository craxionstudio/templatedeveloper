<?php

use App\Support\SiteLayout;

it('tidak membuat link tel: dari placeholder hotline', function () {
    expect(SiteLayout::telUrl('[NO. HOTLINE]'))->toBeNull()
        ->and(SiteLayout::telUrl('(021) 555-1234'))->toBe('tel:0215551234');
});
