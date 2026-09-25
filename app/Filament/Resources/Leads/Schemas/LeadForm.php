<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Models\Lead;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lead masuk dari form website; admin hanya membaca detail dan mengubah status follow-up.
 */
class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Follow-up')->columns(2)->schema([
                    Select::make('status')->label('Status')->options(LeadStatus::class)->required(),
                    Select::make('assigned_to')
                        ->label('Ditangani oleh')
                        ->relationship('assignee', 'name', fn (Builder $query) => $query->whereIn('role', [UserRole::Marketing->value, UserRole::SuperAdmin->value]))
                        ->preload(),
                    Textarea::make('notes')->label('Catatan')->rows(3)->columnSpanFull(),
                ]),
                Section::make('Data lead')->schema([
                    Grid::make(2)->schema([
                        self::info('Nama', fn (Lead $r) => $r->name),
                        self::info('WhatsApp', fn (Lead $r) => $r->whatsapp),
                        self::info('Email', fn (Lead $r) => $r->email),
                        self::info('Cluster / tipe', fn (Lead $r) => trim(($r->cluster?->name ?? '').' '.($r->houseType?->name ?? ''))),
                        self::info('Rencana bayar', fn (Lead $r) => $r->payment_plan),
                        self::info('Masuk', fn (Lead $r) => $r->created_at?->format('d M Y H:i')),
                        self::info('Pesan', fn (Lead $r) => $r->message),
                        self::info('Persetujuan privasi', fn (Lead $r) => $r->consent ? 'Ya' : 'Tidak'),
                    ]),
                ]),
                Section::make('Sumber')->collapsed()->schema([
                    Grid::make(2)->schema([
                        self::info('Halaman form', fn (Lead $r) => $r->source_page),
                        self::info('Posisi form', fn (Lead $r) => $r->source_position),
                        self::info('UTM source / medium', fn (Lead $r) => trim($r->utm_source.' / '.$r->utm_medium, ' /')),
                        self::info('UTM campaign', fn (Lead $r) => $r->utm_campaign),
                        self::info('UTM content / term', fn (Lead $r) => trim($r->utm_content.' / '.$r->utm_term, ' /')),
                        self::info('fbclid / gclid', fn (Lead $r) => trim($r->fbclid.' '.$r->gclid)),
                        self::info('Landing page pertama', fn (Lead $r) => $r->landing_page),
                        self::info('Referrer', fn (Lead $r) => $r->referrer),
                        self::info('Event ID (Pixel/CAPI)', fn (Lead $r) => $r->event_id),
                    ]),
                ]),
            ]);
    }

    private static function info(string $label, Closure $value): Text
    {
        return Text::make(fn (?Lead $record): string => $label.': '.(($record ? $value($record) : null) ?: '–'));
    }
}
