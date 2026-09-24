<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Forms\Fields;
use App\Filament\Forms\SeoTab;
use App\Models\Article;
use App\Models\Tag;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Artikel')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Konten')->schema([
                            Grid::make(2)->schema(Fields::titleAndSlug('title', 'Judul')),
                            Grid::make(2)->schema([
                                Select::make('article_category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->preload()
                                    ->required(),
                                Select::make('author_id')
                                    ->label('Penulis')
                                    ->relationship('author', 'name')
                                    ->preload(),
                            ]),
                            Textarea::make('excerpt')
                                ->label('Ringkasan')
                                ->rows(2)
                                ->maxLength(500)
                                ->helperText('Tampil di kartu artikel dan jadi meta description kalau tab SEO kosong.'),
                            RichEditor::make('body')
                                ->label('Isi artikel')
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('articles')
                                ->fileAttachmentsVisibility('public')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike', 'link'],
                                    ['h2', 'h3'],
                                    ['blockquote', 'bulletList', 'orderedList', 'horizontalRule'],
                                    ['attachFiles'],
                                    ['undo', 'redo'],
                                ])
                                ->helperText('HTML disanitasi saat disimpan (hanya tag yang diizinkan). Waktu baca dihitung otomatis.'),
                            Select::make('tags')
                                ->label('Tag')
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')->label('Nama tag')->required()->maxLength(60),
                                ])
                                ->createOptionUsing(fn (array $data): int => Tag::query()->firstOrCreate(
                                    ['slug' => Str::slug($data['name'])],
                                    ['name' => $data['name']],
                                )->getKey()),
                        ]),
                        Tab::make('Cover')->schema(Fields::image('cover', 'cover_alt', 'Gambar utama')),
                        Tab::make('Publikasi')->schema([
                            Toggle::make('is_published')->label('Dipublikasikan'),
                            DateTimePicker::make('published_at')->label('Tanggal terbit')->native(false)->default(now()),
                            Toggle::make('is_highlight')->label('Highlight')->helperText('Artikel highlight terbaru tampil paling atas di halaman Artikel dan Beranda.'),
                            Text::make(fn (?Article $record): string => $record
                                ? "Waktu baca: {$record->reading_minutes} menit (otomatis)"
                                : 'Waktu baca dihitung otomatis saat disimpan.'),
                        ]),
                        SeoTab::make(fn (Get $get): string => '/artikel/'.$get('../slug'), 'title', 'excerpt'),
                    ]),
            ]);
    }
}
