<?php

namespace App\Settings;

/**
 * Template Detail Artikel.
 */
class ArticleDetailPageSettings extends PageSettings
{
    public array $display;

    public array $related;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_article_detail';
    }
}
