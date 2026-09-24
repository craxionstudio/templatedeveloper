<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\PrivacyPageSettings;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManagePrivacyPage extends PageSettingsPage
{
    protected static string $settings = PrivacyPageSettings::class;

    protected static ?string $title = 'Kebijakan Privasi';

    protected static ?string $slug = 'pengaturan/kebijakan-privasi';

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $publicPath = '/kebijakan-privasi';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Kebijakan Privasi')->tabs([
                Tab::make('Konten')->schema([
                    Fields::text('content.title', 'Judul (H1)'),
                    Grid::make(2)->schema([
                        DatePicker::make('content.effective_date')->label('Tanggal berlaku')->native(false),
                        Fields::text('content.effective_label', 'Label tanggal berlaku'),
                    ]),
                    RichEditor::make('content.body')->label('Isi')
                        ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                ]),
                Tab::make('SEO')->schema(Fields::settingsSeo('seo', '/kebijakan-privasi')),
            ]),
        ]);
    }
}
