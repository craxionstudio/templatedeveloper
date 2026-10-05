<?php

namespace App\Filament\Pages\Settings;

use App\Settings\PageSettings;
use Illuminate\Support\Facades\DB;

/**
 * Satu menu admin untuk beberapa settings halaman sekaligus. State form dikelompokkan per key
 * (mis. "listing.header.title"), lalu tiap settings disimpan sendiri dengan aturan mergeSettings.
 */
abstract class GroupedSettingsPage extends PageSettingsPage
{
    /**
     * @return array<string, class-string<PageSettings>>
     */
    abstract protected static function settingsGroups(): array;

    public static function getSettings(): string
    {
        return array_values(static::settingsGroups())[0];
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill(collect(static::settingsGroups())->map(fn (string $class): array => $this->mutateFormDataBeforeFill(app($class)->toArray()))->all());

        $this->callHook('afterFill');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            foreach (static::settingsGroups() as $key => $class) {
                $settings = app($class);
                $settings->fill(self::mergeSettings($settings->toArray(), $data[$key] ?? []));
                $settings->save();
            }
        });

        $this->rememberData();
        $this->getSavedNotification()?->send();
    }
}
