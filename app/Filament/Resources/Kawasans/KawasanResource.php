<?php

namespace App\Filament\Resources\Kawasans;

use App\Filament\Resources\Kawasans\Pages\CreateKawasan;
use App\Filament\Resources\Kawasans\Pages\EditKawasan;
use App\Filament\Resources\Kawasans\Pages\ListKawasans;
use App\Filament\Resources\Kawasans\RelationManagers\ClustersRelationManager;
use App\Filament\Resources\Kawasans\Schemas\KawasanForm;
use App\Filament\Resources\Kawasans\Tables\KawasansTable;
use App\Models\Kawasan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KawasanResource extends Resource
{
    protected static ?string $model = Kawasan::class;

    protected static ?string $modelLabel = 'Kawasan';

    protected static ?string $pluralModelLabel = 'Kawasan';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Properti';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    public static function form(Schema $schema): Schema
    {
        return KawasanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KawasansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ClustersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKawasans::route('/'),
            'create' => CreateKawasan::route('/create'),
            'edit' => EditKawasan::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
