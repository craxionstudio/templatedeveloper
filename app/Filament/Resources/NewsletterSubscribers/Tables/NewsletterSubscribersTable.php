<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Models\NewsletterSubscriber;
use App\Support\TableExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'aktif' ? 'success' : 'gray'),
                TextColumn::make('source')->label('Sumber')->placeholder('–'),
                TextColumn::make('created_at')->label('Daftar')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['aktif' => 'Aktif', 'berhenti' => 'Berhenti']),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Ekspor')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->schema([
                        ToggleButtons::make('format')->options(['xlsx' => 'Excel (XLSX)', 'csv' => 'CSV'])->default('xlsx')->inline()->required(),
                    ])
                    ->action(fn (array $data, $livewire) => TableExport::download(
                        $livewire->getFilteredTableQuery(),
                        [
                            'Email' => fn (NewsletterSubscriber $s) => $s->email,
                            'Status' => fn (NewsletterSubscriber $s) => $s->status,
                            'Sumber' => fn (NewsletterSubscriber $s) => $s->source,
                            'Tanggal daftar' => fn (NewsletterSubscriber $s) => $s->created_at,
                        ],
                        $data['format'],
                        'newsletter',
                    )),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
