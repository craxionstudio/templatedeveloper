<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ArticleDetailPageSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageArticleDetailPage extends PageSettingsPage
{
    protected static string $settings = ArticleDetailPageSettings::class;

    protected static ?string $title = 'Detail Artikel (template)';

    protected static ?string $navigationLabel = 'Detail Artikel';

    protected static ?string $slug = 'pengaturan/detail-artikel';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $publicPath = '/artikel';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Detail Artikel')->persistTabInQueryString()->tabs([
                Tab::make('Tampilan')->schema([
                    Grid::make(4)->schema([
                        Toggle::make('display.show_author')->label('Penulis'),
                        Toggle::make('display.show_reading_time')->label('Waktu baca'),
                        Toggle::make('display.show_share')->label('Tombol bagikan'),
                        Toggle::make('display.show_tags')->label('Tag'),
                    ]),
                    Fields::text('display.updated_label', 'Label "Diperbarui"'),
                ]),
                Tab::make('Artikel terkait')->schema([
                    Fields::enabled('related'),
                    Fields::text('related.title', 'Judul section'),
                    TextInput::make('related.limit')->label('Jumlah')->numeric()->minValue(1)->maxValue(6),
                    Fields::button('related.link', 'link'),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO default')->schema([
                    Fields::text('seo.title_pattern', 'Pola title', '{title} = judul artikel. Tab SEO di resource Artikel menimpa pola ini.'),
                    Fields::text('seo.description_pattern', 'Pola description', '{excerpt} = ringkasan artikel.'),
                ]),
            ]),
        ]);
    }
}
