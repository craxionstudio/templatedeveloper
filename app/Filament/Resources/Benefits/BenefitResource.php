<?php

namespace App\Filament\Resources\Benefits;

use App\Filament\Resources\Benefits\Pages\CreateBenefit;
use App\Filament\Resources\Benefits\Pages\EditBenefit;
use App\Filament\Resources\Benefits\Pages\ListBenefits;
use App\Filament\Resources\Benefits\Schemas\BenefitForm;
use App\Filament\Resources\Benefits\Tables\BenefitsTable;
use App\Models\Benefit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Bank Benefit: daftar benefit tetap yang dipilih per cluster (tab "Promo & Benefit" di Cluster).
 */
class BenefitResource extends Resource
{
    protected static ?string $model = Benefit::class;

    protected static ?string $modelLabel = 'benefit';

    protected static ?string $pluralModelLabel = 'Bank Benefit';

    protected static ?string $navigationLabel = 'Bank Benefit';

    protected static ?string $slug = 'bank-benefit';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Properti';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    public static function form(Schema $schema): Schema
    {
        return BenefitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BenefitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBenefits::route('/'),
            'create' => CreateBenefit::route('/create'),
            'edit' => EditBenefit::route('/{record}/edit'),
        ];
    }
}
