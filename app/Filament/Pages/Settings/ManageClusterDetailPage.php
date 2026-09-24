<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\ClusterDetailPageSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Template semua halaman Detail Rumah (per cluster). Konten per cluster diatur di resource Cluster.
 */
class ManageClusterDetailPage extends PageSettingsPage
{
    protected static string $settings = ClusterDetailPageSettings::class;

    protected static ?string $title = 'Detail Rumah (template)';

    protected static ?string $navigationLabel = 'Detail Rumah';

    protected static ?string $slug = 'pengaturan/detail-rumah';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $publicPath = '/properti';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Detail Rumah')->persistTabInQueryString()->tabs([
                Tab::make('Label & section')->schema([
                    Section::make('Promo')->columns(2)->schema([
                        Fields::enabled('sections.promo'),
                        Fields::text('sections.promo.title', 'Judul'),
                        Fields::text('sections.promo.period_prefix', 'Awalan periode'),
                    ]),
                    Section::make('Spesifikasi')->schema([
                        Fields::enabled('sections.specs'),
                        Fields::text('sections.specs.title', 'Judul'),
                    ]),
                    Section::make('Tipe rumah')->schema([
                        Fields::enabled('sections.types'),
                        Fields::text('sections.types.title', 'Judul', '{cluster} diganti nama cluster.'),
                    ]),
                    Section::make('Deskripsi')->schema([
                        Fields::enabled('sections.description'),
                        Fields::text('sections.description.title', 'Judul'),
                    ]),
                    Section::make('Listing lainnya')->schema([
                        Fields::enabled('sections.others'),
                        Fields::text('sections.others.eyebrow', 'Eyebrow'),
                        Fields::text('sections.others.title', 'Judul'),
                        Fields::button('sections.others.link', 'link'),
                        ...Fields::dataSource('others', fn (): array => Options::clusters(), 'Otomatis (utamakan cluster di kawasan yang sama)'),
                    ]),
                    Section::make('Label spesifikasi')->columns(4)->schema(collect(ClusterDetailPageSettings::defaults()['spec_labels'])
                        ->keys()
                        ->map(fn (string $key) => TextInput::make("spec_labels.{$key}")->label(str($key)->replace('_', ' ')->ucfirst()->toString()))
                        ->all()),
                ]),
                Tab::make('Harga')->schema([
                    Grid::make(2)->schema([
                        Fields::text('pricing.price_label', 'Label harga'),
                        Fields::text('pricing.price_note', 'Catatan harga default'),
                        Fields::text('pricing.installment_label', 'Label cicilan'),
                        Fields::text('pricing.installment_note', 'Catatan cicilan default'),
                        Fields::text('pricing.booking_fee_label', 'Label booking fee'),
                        Fields::text('pricing.booking_fee_note', 'Teks booking fee default'),
                    ]),
                    Fields::button('pricing.kpr_link', 'link simulasi KPR'),
                ]),
                Tab::make('Form & marketing')->schema([
                    Grid::make(2)->schema([
                        Fields::text('form.name_label', 'Label nama'),
                        Fields::text('form.name_placeholder', 'Placeholder nama'),
                        Fields::text('form.whatsapp_label', 'Label WhatsApp'),
                        Fields::text('form.whatsapp_placeholder', 'Placeholder WhatsApp'),
                        Fields::text('form.submit_label', 'Tombol kirim'),
                        Fields::text('form.whatsapp_button_label', 'Tombol WhatsApp'),
                        Fields::text('form.survey_button_label', 'Tombol survey'),
                    ]),
                    Fields::textarea('form.whatsapp_message', 'Template pesan WhatsApp', 2, '{cluster} dan {type} diganti otomatis.'),
                    Section::make('Marketing default')
                        ->description('Dipakai kalau cluster tidak punya marketing sendiri.')
                        ->columns(3)
                        ->schema([
                            Fields::text('form.marketing_name', 'Nama'),
                            Fields::text('form.marketing_title', 'Jabatan'),
                            TextInput::make('form.marketing_whatsapp')->label('WhatsApp (62…)')->tel(),
                            Fields::settingsImage('form.marketing_photo', 'form.marketing_name', 'Foto')[0],
                        ]),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO default')->schema([
                    Fields::text('seo.title_pattern', 'Pola title', '{name}, {building_type}, {kawasan}. Tab SEO di resource Cluster menimpa pola ini.'),
                    Fields::text('seo.description_pattern', 'Pola description', '{summary} = ringkasan cluster.'),
                ]),
            ]),
        ]);
    }
}
