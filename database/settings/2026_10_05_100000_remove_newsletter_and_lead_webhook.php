<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Newsletter & webhook lead dihapus: section newsletter di halaman Artikel, label sukses newsletter,
 * dan URL webhook di notifikasi lead.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->deleteIfExists('page_article_index.newsletter');

        foreach (['global.notifications' => 'webhook_url', 'global.labels' => 'newsletter_success'] as $property => $key) {
            if ($this->migrator->exists($property)) {
                $this->migrator->update($property, function (array|object $value) use ($key): array {
                    $value = json_decode(json_encode($value), true);
                    unset($value[$key]);

                    return $value;
                });
            }
        }
    }
};
