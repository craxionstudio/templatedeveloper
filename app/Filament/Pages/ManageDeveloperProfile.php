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
                TextInput::make('headline')->label('Headline')->required()->maxLength(255)->placeholder('Membangun kota mandiri sejak 1989'),
                Textarea::make('description')->label('Deskripsi singkat')->rows(4)->placeholder('BSD City adalah kota terencana yang dikembangkan Sinar Mas Land.'),
                RichEditor::make('history')
                    ->label('Sejarah')
                    ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                TextInput::make('vision_quote')->label('Kutipan visi / filosofi')->maxLength(255)->placeholder('Kota yang tumbuh bersama penghuninya.'),
            ]),
            Section::make('Statistik')->schema([
                Repeater::make('stats')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('value')->label('Nilai')->placeholder('6.000 ha')->required()->maxLength(20),
                        TextInput::make('label')->label('Label')->required()->maxLength(60)->placeholder('Luas kawasan'),
                    ])
                    ->columns(2)
                    ->reorderableWithDragAndDrop()
                    ->maxItems(6)
                    ->addActionLabel('Tambah statistik'),
            ]),
            Section::make('Foto')->columns(2)->schema([
                Fields::imageOnly('photo', 'Foto utama (kantor / kawasan)'),
                Fields::imageOnly('secondary_photo', 'Foto kedua (tim)'),
            ]),
        ]);
    }
}
