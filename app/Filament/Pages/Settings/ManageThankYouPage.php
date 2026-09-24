<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ThankYouPageSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageThankYouPage extends PageSettingsPage
{
    protected static string $settings = ThankYouPageSettings::class;

    protected static ?string $title = 'Terima Kasih';

    protected static ?string $slug = 'pengaturan/terima-kasih';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?string $publicPath = '/terima-kasih';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Terima Kasih')->tabs([
                Tab::make('Konten')->schema([
                    Fields::text('content.title', 'Judul (H1)'),
                    Fields::textarea('content.message', 'Pesan'),
                    Fields::text('content.whatsapp_label', 'Tombol lanjut WhatsApp'),
                    Fields::textarea('content.whatsapp_message', 'Template pesan WhatsApp', 2, '{name} dan {cluster} diganti otomatis.'),
                    Repeater::make('content.links')->label('Tautan lanjutan')->schema([
                        TextInput::make('label')->required(),
                        TextInput::make('url')->required(),
                    ])->columns(2)->reorderableWithDragAndDrop()->maxItems(4),
                ]),
                Tab::make('SEO')->schema([
                    Fields::metaTitle('seo.meta_title'),
                    Text::make('Halaman ini selalu noindex (dikunci).'),
                ]),
            ]),
        ]);
    }
}
