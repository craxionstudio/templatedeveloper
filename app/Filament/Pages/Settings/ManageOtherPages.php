<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\AboutPageSettings;
use App\Settings\ArticleIndexPageSettings;
use App\Settings\FacilityPageSettings;
use App\Settings\PrivacyPageSettings;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Halaman Lain: Tentang Kami, Artikel, Fasilitas, Kebijakan Privasi.
 * (Halaman Kontak dihapus Okt 2026: info kontak di footer diambil dari Pengaturan Umum.)
 */
class ManageOtherPages extends GroupedSettingsPage
{
    protected static ?string $title = 'Halaman Lain';

    protected static ?string $navigationLabel = 'Halaman Lain';

    protected static ?string $slug = 'pengaturan/halaman-lain';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $publicPath = null;

    protected static function settingsGroups(): array
    {
        return [
            'about' => AboutPageSettings::class,
            'articles' => ArticleIndexPageSettings::class,
            'facility' => FacilityPageSettings::class,
            'privacy' => PrivacyPageSettings::class,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Halaman')->persistTabInQueryString()->tabs([
                Tab::make('Tentang Kami')->schema([
                    Fields::text('about.hero.title', 'Judul (H1)')->placeholder('Kosong = headline Profil Developer'),
                    Fields::textarea('about.hero.description', 'Teks di bawah judul', 2)->placeholder('Kosong = deskripsi Profil Developer'),
                    Fields::settingsImageOnly('about.hero.image', 'Foto header (kosong = foto Profil Developer)'),
                    Fields::textarea('about.vision.vision', 'Visi', 2)->placeholder('Membangun kota mandiri yang nyaman untuk ditinggali, bekerja, dan berkembang.'),
                    Repeater::make('about.vision.missions')->label('Misi')
                        ->simple(TextInput::make('text')->required()->maxLength(255)->placeholder('Menyediakan hunian dengan fasilitas lengkap dalam satu kawasan.'))
                        ->reorderableWithDragAndDrop()->defaultItems(0)->addActionLabel('Tambah misi'),
                    Repeater::make('about.timeline.items')->label('Perjalanan (timeline)')
                        ->schema([
                            TextInput::make('year')->label('Tahun')->required()->maxLength(10)->placeholder('1989'),
                            TextInput::make('title')->label('Judul')->required()->maxLength(120)->placeholder('BSD City mulai dibangun'),
                            TextInput::make('description')->label('Keterangan')->maxLength(255)->columnSpanFull(),
                        ])
                        ->columns(2)->reorderableWithDragAndDrop()->defaultItems(0)->collapsible()->addActionLabel('Tambah tonggak'),
                    Fields::advanced(Fields::settingsMeta('about.seo')),
                ]),
                Tab::make('Artikel')->schema([
                    Fields::text('articles.header.title', 'Judul (H1)')->placeholder('Info & Tips Properti BSD City'),
                    Fields::textarea('articles.header.description', 'Teks di bawah judul', 2)->placeholder('Kabar terbaru BSD City, tips KPR, dan panduan memilih cluster.'),
                    Fields::advanced(Fields::settingsMeta('articles.seo')),
                ]),
                Tab::make('Fasilitas')->schema([
                    Fields::text('facility.header.title', 'Judul (H1)')->placeholder('Fasilitas di BSD City'),
                    Fields::textarea('facility.header.description', 'Teks di bawah judul', 2)->placeholder('Sekolah, rumah sakit, pusat belanja, dan ruang hijau di sekitar cluster.'),
                    Fields::advanced(Fields::settingsMeta('facility.seo')),
                ]),
                Tab::make('Kebijakan Privasi')->schema([
                    DatePicker::make('privacy.content.effective_date')->label('Tanggal berlaku')->native(false),
                    RichEditor::make('privacy.content.body')->label('Isi')
                        ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                ]),
            ]),
        ]);
    }
}
