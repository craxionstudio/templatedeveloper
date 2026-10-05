<?php

namespace App\Filament\Pages\Settings;

use App\Support\Content;
use Filament\Actions\Action;
use Filament\Pages\SettingsPage;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Basis settings page: grup "Pengaturan", tombol "Lihat halaman",
 * dan akses untuk super admin & admin konten.
 */
abstract class PageSettingsPage extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    /**
     * Path publik halaman terkait (null = tidak ada tombol "Lihat halaman").
     */
    protected static ?string $publicPath = '/';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * Teks contoh "[...]" dan link "#" tidak dimuat ke form (dianggap kosong), supaya admin langsung
     * melihat placeholder contoh dan validasi tidak gagal karena data contoh.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return self::withoutPlaceholders($data);
    }

    /**
     * @param  array<int|string, mixed>  $data
     * @return array<int|string, mixed>
     */
    public static function withoutPlaceholders(array $data): array
    {
        $isList = array_is_list($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::withoutPlaceholders($value);
            } elseif (is_string($value) && (Content::blank($value) || $value === '#')) {
                $value = null;
            }

            if ($isList && (Content::blank($value) || (is_array($value) && array_key_exists('url', $value) && blank($value['url'])))) {
                unset($data[$key]);

                continue;
            }

            $data[$key] = $value;
        }

        return $isList ? array_values($data) : $data;
    }

    /**
     * Gabungkan data form ke nilai tersimpan: key yang tidak ada di form tidak hilang,
     * sedangkan list (repeater) diganti utuh supaya item yang dihapus benar-benar terhapus.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return self::mergeSettings(app(static::getSettings())->toArray(), $data);
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    public static function mergeSettings(array $current, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            $existing = $current[$key] ?? null;

            $current[$key] = is_array($value) && is_array($existing) && ! array_is_list($value) && ! array_is_list($existing)
                ? self::mergeSettings($existing, $value)
                : $value;
        }

        return $current;
    }

    protected function getHeaderActions(): array
    {
        if (static::$publicPath === null) {
            return [];
        }

        return [
            Action::make('view')
                ->label('Lihat halaman')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(url(static::$publicPath), shouldOpenInNewTab: true),
        ];
    }
}
