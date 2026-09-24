<?php

namespace App\Settings;

/**
 * Halaman Artikel (index & kategori).
 */
class ArticleIndexPageSettings extends PageSettings
{
    public array $header;

    public array $list;

    public array $newsletter;

    public array $seo;

    public static function group(): string
    {
        return 'page_article_index';
    }
}
