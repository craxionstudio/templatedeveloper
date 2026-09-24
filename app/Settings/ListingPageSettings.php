<?php

namespace App\Settings;

/**
 * Produk Listing — dipakai tampilan Cluster (/properti) dan Kawasan (/properti/kawasan).
 */
class ListingPageSettings extends PageSettings
{
    public array $header;

    public array $toggle;

    public array $cluster_view;

    public array $kawasan_view;

    public array $empty_state;

    public array $cta;

    public array $seo_cluster;

    public array $seo_kawasan;

    public static function group(): string
    {
        return 'page_listing';
    }
}
