<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Forms\Fields;
use App\Settings\AboutPageSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageAboutPage extends PageSettingsPage
{
    protected static string $settings = AboutPageSettings::class;

    protected static ?string $title = 'Tentang Kami';

    protected static ?string $slug = 'pengaturan/tentang-kami';

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static ?string $publicPath = '/tentang-kami';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Tentang Kami')->persistTabInQueryString()->tabs([
                Tab::make('Hero')->schema([
                    Fields::enabled('hero'),
                    Fields::text('hero.eyebrow', 'Eyebrow'),
                    Fields::text('hero.title', 'Judul (kosong = headline Profil Developer)'),
                    Fields::textarea('hero.description', 'Deskripsi (kosong = deskripsi Profil Developer)'),
                    ...Fields::settingsImage('hero.image', 'hero.image_alt', 'Gambar'),
                ]),
                Tab::make('Sejarah')->schema([
                    Fields::enabled('history'),
                    Fields::text('history.title', 'Judul'),
                    RichEditor::make('history.body')->label('Isi (kosong = sejarah di Profil Developer)')
                        ->toolbarButtons([['bold', 'italic', 'link'], ['h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                ]),
                Tab::make('Visi & Misi')->schema([
                    Fields::enabled('vision'),
                    Fields::text('vision.title', 'Judul'),
                    Fields::text('vision.vision_label', 'Label visi'),
                    Fields::textarea('vision.vision', 'Visi', 2),
                    Fields::text('vision.mission_label', 'Label misi'),
                    Repeater::make('vision.missions')->label('Misi')->simple(TextInput::make('text')->required())
                        ->reorderableWithDragAndDrop(),
                ]),
                Tab::make('Statistik')->schema([
                    Fields::enabled('stats'),
                    Fields::text('stats.title', 'Judul'),
                    Toggle::make('stats.use_profile')->label('Statistik dari Profil Developer'),
                ]),
                Tab::make('Timeline')->schema([
                    Fields::enabled('timeline'),
                    Fields::text('timeline.title', 'Judul'),
                    Repeater::make('timeline.items')->label('Tonggak')->schema([
                        TextInput::make('year')->label('Tahun')->required()->maxLength(20),
                        TextInput::make('title')->label('Judul')->required(),
                        Textarea::make('description')->label('Keterangan')->rows(2)->columnSpanFull(),
                    ])->columns(2)->reorderableWithDragAndDrop()->collapsible(),
                ]),
                Tab::make('Tim (opsional)')->schema([
                    Fields::enabled('team'),
                    Fields::text('team.title', 'Judul'),
                    Repeater::make('team.items')->label('Anggota')->schema([
                        TextInput::make('name')->label('Nama')->required(),
                        TextInput::make('role')->label('Jabatan'),
                        ...Fields::settingsImage('photo', 'photo_alt', 'Foto'),
                    ])->columns(2)->reorderableWithDragAndDrop()->collapsible(),
                ]),
                Tab::make('Penghargaan (opsional)')->schema([
                    Fields::enabled('awards'),
                    Fields::text('awards.title', 'Judul'),
                    Repeater::make('awards.items')->label('Penghargaan')->schema([
                        TextInput::make('year')->label('Tahun'),
                        TextInput::make('title')->label('Nama penghargaan')->required(),
                        TextInput::make('issuer')->label('Pemberi'),
                    ])->columns(3)->reorderableWithDragAndDrop(),
                ]),
                Tab::make('CTA')->schema([Fields::cta()]),
                Tab::make('SEO')->schema(Fields::settingsSeo('seo', '/tentang-kami')),
            ]),
        ]);
    }
}
