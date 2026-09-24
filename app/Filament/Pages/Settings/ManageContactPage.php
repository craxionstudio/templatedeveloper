<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ContactPageSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageContactPage extends PageSettingsPage
{
    protected static string $settings = ContactPageSettings::class;

    protected static ?string $title = 'Kontak';

    protected static ?string $slug = 'pengaturan/kontak';

    protected static ?int $navigationSort = 9;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $publicPath = '/kontak';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Kontak')->persistTabInQueryString()->tabs([
                Tab::make('Header')->schema([
                    Fields::text('header.eyebrow', 'Eyebrow'),
                    Fields::text('header.title', 'Judul (H1)'),
                    Fields::textarea('header.description', 'Deskripsi'),
                ]),
                Tab::make('Info kontak')->schema([
                    Fields::enabled('info'),
                    Fields::text('info.title', 'Judul', 'Alamat, telepon, email, dan jam buka diambil dari Pengaturan Global → Kontak.'),
                    Grid::make(4)->schema([
                        Fields::text('info.hours_label', 'Label jam buka'),
                        Fields::text('info.phone_label', 'Label telepon'),
                        Fields::text('info.email_label', 'Label email'),
                        Fields::text('info.whatsapp_label', 'Label WhatsApp'),
                    ]),
                ]),
                Tab::make('Peta')->schema([
                    Fields::enabled('map'),
                    Textarea::make('map.embed_url')->label('URL embed Google Maps')->rows(2),
                    ...Fields::settingsImage('map.image', 'map.image_alt', 'Gambar peta statis (facade)'),
                    Fields::text('map.button_label', 'Label tombol "Buka peta"'),
                ]),
                Tab::make('Form')->schema([
                    Fields::enabled('form'),
                    Fields::text('form.title', 'Judul form'),
                    Grid::make(2)->schema([
                        Fields::text('form.name_label', 'Label nama'),
                        Fields::text('form.whatsapp_label', 'Label WhatsApp'),
                        Fields::text('form.email_label', 'Label email'),
                        Fields::text('form.interest_label', 'Label minat cluster'),
                        Fields::text('form.interest_placeholder', 'Opsi kosong minat cluster'),
                        Fields::text('form.payment_label', 'Label rencana pembayaran'),
                        Fields::text('form.message_label', 'Label pesan'),
                        Fields::text('form.submit_label', 'Tombol kirim'),
                    ]),
                    Repeater::make('form.payment_options')->label('Opsi rencana pembayaran')
                        ->simple(TextInput::make('label')->required())->reorderableWithDragAndDrop(),
                    Fields::textarea('form.consent_label', 'Teks persetujuan (UU PDP)', 2),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO')->schema(Fields::settingsSeo('seo', '/kontak')),
            ]),
        ]);
    }
}
