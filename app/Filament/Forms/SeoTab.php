<?php

namespace App\Filament\Forms;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

/**
 * Tab SEO untuk resource konten (relasi polymorphic seo_meta).
 */
class SeoTab
{
    /**
     * @param  Closure(Get): string  $path  URL publik record (untuk preview), mis. fn (Get $get) => '/properti/'.$get('../slug')
     */
    public static function make(Closure $path, string $titleField = 'name', string $descriptionField = 'summary'): Tab
    {
        return Tab::make('SEO')
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->schema([
                Group::make()
                    ->relationship('seo')
                    ->schema([
                        Fields::metaTitle(),
                        Fields::metaDescription(),
                        FileUpload::make('og_image')
                            ->label('Gambar Open Graph (1200×630)')
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->image()
                            ->maxSize(4096),
                        TextInput::make('canonical_url')
                            ->label('Canonical override (opsional)')
                            ->url()
                            ->maxLength(255),
                        Toggle::make('noindex')->label('Noindex (sembunyikan dari mesin pencari)'),
                        Fields::snippetPreview(
                            fn (Get $get) => $get('meta_title') ?: $get("../{$titleField}"),
                            fn (Get $get) => $get('meta_description') ?: strip_tags((string) $get("../{$descriptionField}")),
                            $path,
                        ),
                    ]),
            ]);
    }
}
