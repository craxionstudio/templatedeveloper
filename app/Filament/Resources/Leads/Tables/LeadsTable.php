<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Support\TableExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cluster', 'houseType']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Masuk')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('whatsapp')->label('WhatsApp')->searchable()->copyable()
                    ->url(fn (Lead $record): string => 'https://wa.me/'.$record->whatsapp, shouldOpenInNewTab: true),
                TextColumn::make('cluster.name')->label('Cluster')->placeholder('–'),
                TextColumn::make('utm_source')->label('Sumber')->placeholder('langsung')->badge()->color('gray'),
                TextColumn::make('source_position')->label('Form')->toggleable(isToggledHiddenByDefault: true),
                SelectColumn::make('status')->label('Status')->options(LeadStatus::class)->selectablePlaceholder(false),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
                SelectFilter::make('status')->options(LeadStatus::class)->multiple(),
                SelectFilter::make('cluster_id')->label('Cluster')->relationship('cluster', 'name')->searchable()->preload(),
                SelectFilter::make('utm_source')
                    ->label('UTM source')
                    ->options(fn (): array => Lead::query()->whereNotNull('utm_source')->distinct()->orderBy('utm_source')->pluck('utm_source', 'utm_source')->all()),
                SelectFilter::make('utm_campaign')
                    ->label('UTM campaign')
                    ->options(fn (): array => Lead::query()->whereNotNull('utm_campaign')->distinct()->orderBy('utm_campaign')->pluck('utm_campaign', 'utm_campaign')->all()),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Ekspor')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->schema([
                        ToggleButtons::make('format')->options(['xlsx' => 'Excel (XLSX)', 'csv' => 'CSV'])->default('xlsx')->inline()->required(),
                    ])
                    ->modalDescription('Mengekspor lead sesuai filter yang sedang aktif.')
                    ->action(fn (array $data, $livewire) => TableExport::download(
                        $livewire->getFilteredTableQuery()->with(['cluster', 'houseType', 'assignee']),
                        self::exportColumns(),
                        $data['format'],
                        'lead',
                    )),
            ])
            ->recordActions([EditAction::make()->label('Detail')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /**
     * @return array<string, callable(Lead): mixed>
     */
    public static function exportColumns(): array
    {
        return [
            'Tanggal' => fn (Lead $l) => $l->created_at,
            'Nama' => fn (Lead $l) => $l->name,
            'WhatsApp' => fn (Lead $l) => $l->whatsapp,
            'Email' => fn (Lead $l) => $l->email,
            'Cluster' => fn (Lead $l) => $l->cluster?->name,
            'Tipe' => fn (Lead $l) => $l->houseType?->name,
            'Rencana bayar' => fn (Lead $l) => $l->payment_plan,
            'Pesan' => fn (Lead $l) => $l->message,
            'Status' => fn (Lead $l) => $l->status?->getLabel(),
            'Ditangani' => fn (Lead $l) => $l->assignee?->name,
            'Catatan' => fn (Lead $l) => $l->notes,
            'Halaman form' => fn (Lead $l) => $l->source_page,
            'Posisi form' => fn (Lead $l) => $l->source_position,
            'utm_source' => fn (Lead $l) => $l->utm_source,
            'utm_medium' => fn (Lead $l) => $l->utm_medium,
            'utm_campaign' => fn (Lead $l) => $l->utm_campaign,
            'utm_content' => fn (Lead $l) => $l->utm_content,
            'utm_term' => fn (Lead $l) => $l->utm_term,
            'fbclid' => fn (Lead $l) => $l->fbclid,
            'gclid' => fn (Lead $l) => $l->gclid,
            'Landing page' => fn (Lead $l) => $l->landing_page,
            'Referrer' => fn (Lead $l) => $l->referrer,
        ];
    }
}
