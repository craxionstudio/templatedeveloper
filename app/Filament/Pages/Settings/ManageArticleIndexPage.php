<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ArticleIndexPageSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageArticleIndexPage extends PageSettingsPage
{
    protected static string $settings = ArticleIndexPageSettings::class;

    protected static ?string $title = 'Artikel (index)';

    protected static ?string $navigationLabel = 'Artikel';

    protected static ?string $slug = 'pengaturan/artikel';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $publicPath = '/artikel';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Artikel')->persistTabInQueryString()->tabs([
                Tab::make('Header')->schema([
                    Fields::text('header.eyebrow', 'Eyebrow'),
                    Fields::text('header.title', 'Judul (H1)'),
                    Fields::textarea('header.description', 'Deskripsi'),
                    Select::make('header.highlight_article_id')->label('Artikel highlight')
                        ->options(fn (): array => Options::articles())->placeholder('Otomatis (highlight terbaru)')->searchable(),
                    Fields::text('header.highlight_badge', 'Label badge highlight'),
                ]),
                Tab::make('Daftar')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('list.per_page')->label('Jumlah per halaman')->numeric()->minValue(3)->maxValue(48),
                        Select::make('list.categories')->label('Kategori di filter')->options(fn (): array => Options::articleCategories())
                            ->multiple()->helperText('Kosong = semua kategori.'),
                    ]),
                    Toggle::make('list.show_search')->label('Tampilkan pencarian'),
                    Grid::make(2)->schema([
                        Fields::text('list.search_placeholder', 'Placeholder pencarian'),
                        Fields::text('list.all_label', 'Chip "Semua"'),
                        Fields::text('list.count_suffix', 'Akhiran jumlah ("artikel")'),
                        Fields::text('list.empty_text', 'Teks kalau kosong'),
                    ]),
                ]),
                Tab::make('Newsletter')->schema([
                    Fields::enabled('newsletter'),
                    Fields::text('newsletter.title', 'Judul'),
                    Fields::textarea('newsletter.description', 'Deskripsi', 2),
                    Grid::make(2)->schema([
                        Fields::text('newsletter.email_placeholder', 'Placeholder email'),
                        Fields::text('newsletter.button_label', 'Label tombol'),
                    ]),
                ]),
                Tab::make('SEO')->schema(Fields::settingsSeo('seo', '/artikel')),
            ]),
        ]);
    }
}
