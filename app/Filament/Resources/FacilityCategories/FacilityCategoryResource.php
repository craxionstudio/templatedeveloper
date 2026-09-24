<?php

namespace App\Filament\Resources\FacilityCategories;

use App\Filament\Resources\FacilityCategories\Pages\CreateFacilityCategory;
use App\Filament\Resources\FacilityCategories\Pages\EditFacilityCategory;
use App\Filament\Resources\FacilityCategories\Pages\ListFacilityCategories;
use App\Filament\Resources\FacilityCategories\Schemas\FacilityCategoryForm;
use App\Filament\Resources\FacilityCategories\Tables\FacilityCategoriesTable;
use App\Models\FacilityCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FacilityCategoryResource extends Resource
{
    protected static ?string $model = FacilityCategory::class;

    protected static ?string $modelLabel = 'Kategori fasilitas';

    protected static ?string $pluralModelLabel = 'Kategori fasilitas';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return FacilityCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacilityCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilityCategories::route('/'),
            'create' => CreateFacilityCategory::route('/create'),
            'edit' => EditFacilityCategory::route('/{record}/edit'),
        ];
    }
}
