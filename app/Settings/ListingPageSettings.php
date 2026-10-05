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

    /**
     * Field yang bisa diubah admin; sisanya teks tetap di kode (lihat PageSettings::editable()).
     *
     * @return list<string>
     */
    public static function editable(): array
    {
        return [
            'header.title',
            'header.description',
            'header.image',
            'seo_cluster.meta_title',
            'seo_cluster.meta_description',
            'seo_kawasan.meta_title',
            'seo_kawasan.meta_description',
        ];
    }
}
