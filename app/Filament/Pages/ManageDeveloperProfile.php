<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Fields;
use App\Models\DeveloperProfile;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Profil developer (satu baris). Dipakai di Beranda (Tentang Developer) dan Tentang Kami.
 */
class ManageDeveloperProfile extends SingletonRecordPage
{
    protected static ?string $title = 'Profil Developer';

    protected static ?string $navigationLabel = 'Profil Developer';

    protected static ?string $slug = 'profil-developer';

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static function resolveRecord(): Model
    {
        return DeveloperProfile::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profil')->schema([
                TextInput::make('headline')->label('Headline')->required()->maxLength(255),
                Textarea::make('description')->label('Deskripsi singkat')->rows(4),
                RichEditor::make('history')
                    ->label('Sejarah')
                    ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                TextInput::make('vision_quote')->label('Kutipan visi / filosofi')->maxLength(255),
            ]),
            Section::make('Statistik')->schema([
                Repeater::make('stats')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('value')->label('Nilai')->placeholder('[XX] ha')->required()->maxLength(20),
                        TextInput::make('label')->label('Label')->required()->maxLength(60),
                    ])
                    ->columns(2)
                    ->reorderableWithDragAndDrop()
                    ->maxItems(6)
                    ->addActionLabel('Tambah statistik'),
            ]),
            Section::make('Foto')->columns(2)->schema([
                ...Fields::image('photo', 'photo_alt', 'Foto utama (kantor / kawasan)'),
                ...Fields::image('secondary_photo', 'secondary_photo_alt', 'Foto kedua (tim)'),
            ]),
        ]);
    }
}
