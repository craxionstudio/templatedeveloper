<?php

namespace App\Filament\Resources\OtherKawasans;

use App\Filament\Resources\OtherKawasans\Pages\CreateOtherKawasan;
use App\Filament\Resources\OtherKawasans\Pages\EditOtherKawasan;
use App\Filament\Resources\OtherKawasans\Pages\ListOtherKawasans;
use App\Models\Kawasan;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Kawasan Lainnya: kawasan tanpa halaman sendiri (punya_halaman = false), hanya judul grup di
 * /properti/cluster-lainnya. Tabel yang sama dengan Kawasan; hanya resource admin yang dipisah.
 */
class OtherKawasanResource extends Resource
{
    protected static ?string $model = Kawasan::class;

    protected static ?string $slug = 'kawasan-lainnya';

    protected static ?string $modelLabel = 'Kawasan lainnya';

    protected static ?string $pluralModelLabel = 'Kawasan Lainnya';

    protected static ?string $navigationLabel = 'Kawasan Lainnya';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Properti';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('punya_halaman', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('name')->label('Nama kawasan')->required()->maxLength(255)->placeholder('Giri Loka')
                ->helperText('Tampil sebagai judul grup di halaman Cluster Lainnya. Slug dibuat otomatis dari nama.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('clusters'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('clusters_count')->label('Cluster')->badge()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                ActivateKawasanAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOtherKawasans::route('/'),
            'create' => CreateOtherKawasan::route('/create'),
            'edit' => EditOtherKawasan::route('/{record}/edit'),
        ];
    }
}
