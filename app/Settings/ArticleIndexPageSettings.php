<?php

namespace App\Settings;

/**
 * Halaman Artikel (index & kategori).
 */
class ArticleIndexPageSettings extends PageSettings
{
    public array $header;

    public array $list;

    public array $seo;

    public static function group(): string
    {
        return 'page_article_index';
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
            'seo.meta_title',
            'seo.meta_description',
        ];
    }
}
