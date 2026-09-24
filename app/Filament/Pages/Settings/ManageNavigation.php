<?php

namespace App\Filament\Pages\Settings;

use App\Settings\NavigationSettings;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageNavigation extends PageSettingsPage
{
    protected static string $settings = NavigationSettings::class;

    protected static ?string $title = 'Menu Navigasi';

    protected static ?string $navigationLabel = 'Menu Navigasi';

    protected static ?string $slug = 'pengaturan/menu';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Header & drawer mobile')
                ->description('Urutan drag = urutan menu. Tetap satu item "Properti"; kawasan dijangkau dari halaman Properti dan footer.')
                ->schema([
                    ManageGlobalSettings::links('header_items', 'Item menu')->minItems(1)->maxItems(8),
                ]),
        ]);
    }
}
