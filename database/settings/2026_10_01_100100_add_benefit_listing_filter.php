<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Bank Benefit di /properti: filter ?benefit= ikut tampil, status disembunyikan dari filter
 * (opsi "Sold out" tidak boleh tampil), dan urutan "Promo" tersedia.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('page_listing.cluster_view', function (array|object $view): array {
            $view = json_decode(json_encode($view), true);
            $filters = array_values(array_diff($view['filters'] ?? [], ['status']));

            if (! in_array('benefit', $filters, true)) {
                $filters[] = 'benefit';
            }

            return [
                ...$view,
                'filters' => $filters,
                'filter_labels' => [...($view['filter_labels'] ?? []), 'benefit' => $view['filter_labels']['benefit'] ?? 'Promo & benefit'],
                'sort_options' => [...($view['sort_options'] ?? []), 'promo' => $view['sort_options']['promo'] ?? 'Promo'],
            ];
        });
    }
};
