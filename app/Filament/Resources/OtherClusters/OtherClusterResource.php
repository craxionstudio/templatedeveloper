<?php

namespace App\Filament\Resources\OtherClusters;

use App\Enums\ClusterDisplay;
use App\Filament\Resources\OtherClusters\Pages\CreateOtherCluster;
use App\Filament\Resources\OtherClusters\Pages\EditOtherCluster;
use App\Filament\Resources\OtherClusters\Pages\ListOtherClusters;
use App\Models\Cluster;
use App\Models\Kawasan;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Cluster Lainnya: cluster tanpa halaman sendiri (tampil_sebagai = daftar), hanya nama di
 * /properti/cluster-lainnya. Tabel yang sama dengan Cluster; hanya resource admin yang dipisah.
 */
class OtherClusterResource extends Resource
{
    protected static ?string $model = Cluster::class;

    protected static ?string $slug = 'cluster-lainnya';

    protected static ?string $modelLabel = 'Cluster lainnya';

    protected static ?string $pluralModelLabel = 'Cluster Lainnya';

    protected static ?string $navigationLabel = 'Cluster Lainnya';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Properti';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tampil_sebagai', ClusterDisplay::Daftar->value);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('name')->label('Nama cluster')->required()->maxLength(255)->placeholder('Giri Loka 1'),
            Select::make('kawasan_id')
                ->label('Kawasan')
                ->options(fn (): array => Kawasan::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->placeholder('Tanpa kawasan (grup "Lainnya")')
                ->helperText('Kosong = tampil di grup "Lainnya" di halaman Cluster Lainnya. Slug dibuat otomatis dari nama.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('kawasan'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('kawasan.name')->label('Kawasan')->placeholder('Lainnya')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('kawasan')
                    ->label('Kawasan')
                    ->options(fn (): array => ['lainnya' => 'Lainnya (tanpa kawasan)'] + Kawasan::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        null, '' => $query,
                        'lainnya' => $query->whereNull('kawasan_id'),
                        default => $query->where('kawasan_id', $data['value']),
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                ActivateClusterAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOtherClusters::route('/'),
            'create' => CreateOtherCluster::route('/create'),
            'edit' => EditOtherCluster::route('/{record}/edit'),
        ];
    }
}
