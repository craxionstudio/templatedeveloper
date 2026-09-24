<?php

namespace App\Filament\Resources\Clusters\RelationManagers;

use App\Filament\Forms\Fields;
use App\Models\HouseType;
use App\Support\DummyData;
use App\Support\Rupiah;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

/**
 * Tipe rumah di cluster (1 sampai n). Urutan drag = urutan tab di Detail Rumah.
 */
class HouseTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'houseTypes';

    protected static ?string $title = 'Tipe rumah';

    protected static ?string $modelLabel = 'tipe rumah';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(3)->schema([
                    ...Fields::titleAndSlug(
                        'name',
                        'Nama tipe',
                        modifyUniqueRule: fn (Unique $rule) => $rule->where('cluster_id', $this->getOwnerRecord()->getKey()),
                    ),
                    TextInput::make('lot_size')->label('Kavling')->placeholder('7×15')->maxLength(20),
                ]),
                Section::make('Ukuran & ruang')->columns(4)->schema([
                    TextInput::make('land_area')->label('LT (m²)')->numeric()->minValue(0)->required(),
                    TextInput::make('building_area')->label('LB (m²)')->numeric()->minValue(0)->dummyHint(fn (?HouseType $record, $state): bool => DummyData::isHouseTypeValue($record, 'building_area', $state)),
                    TextInput::make('bedrooms')->label('Kamar tidur')->numeric()->minValue(0)->required(),
                    TextInput::make('extra_bedrooms')->label('KT tambahan (+1)')->numeric()->minValue(0)->default(0),
                    TextInput::make('bathrooms')->label('Kamar mandi')->numeric()->minValue(0)->dummyHint(fn (?HouseType $record, $state): bool => DummyData::isHouseTypeValue($record, 'bathrooms', $state)),
                    TextInput::make('floors')->label('Lantai')->numeric()->minValue(1)->default(1),
                    TextInput::make('carports')->label('Carport (mobil)')->numeric()->minValue(0)->dummyHint(fn (?HouseType $record, $state): bool => DummyData::isHouseTypeValue($record, 'carports', $state)),
                    TextInput::make('units_available')->label('Sisa unit')->numeric()->minValue(0)->dummyHint(fn (?HouseType $record, $state): bool => DummyData::isHouseTypeValue($record, 'units_available', $state)),
                ]),
                Section::make('Harga')->columns(2)->schema([
                    TextInput::make('price_from')->label('Harga mulai')->numeric()->minValue(0)->prefix('Rp')->required(),
                    TextInput::make('installment_from')->label('Cicilan mulai / bulan')->numeric()->minValue(0)->prefix('Rp'),
                ]),
                Section::make('Denah')->schema(Fields::image('floorplan', 'floorplan_alt', 'Denah')),
                Toggle::make('is_published')->label('Dipublikasikan')->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('Tipe')->searchable(),
                TextColumn::make('lot_size')->label('Kavling'),
                TextColumn::make('land_area')->label('LT / LB')
                    ->formatStateUsing(fn ($state, HouseType $record): string => "{$record->land_area} / {$record->building_area} m²"),
                TextColumn::make('bedrooms')->label('KT / KM')
                    ->formatStateUsing(fn ($state, HouseType $record): string => $record->bedroomsLabel().' / '.$record->bathrooms),
                TextColumn::make('price_from')->label('Harga mulai')
                    ->formatStateUsing(fn ($state): ?string => Rupiah::short($state)),
                TextColumn::make('units_available')->label('Sisa unit'),
                ToggleColumn::make('is_published')->label('Publik'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
