<?php

namespace App\Settings;

/**
 * Template Detail Rumah (per cluster).
 */
class ClusterDetailPageSettings extends PageSettings
{
    public array $sections;

    public array $pricing;

    public array $spec_labels;

    public array $form;

    public array $others;

    public array $cta;

    public array $seo;

    public static function group(): string
    {
        return 'page_cluster_detail';
    }
}
