<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Halaman edit untuk tabel yang hanya berisi satu baris (Profil Lokasi, Profil Developer).
 *
 * @property-read Schema $form
 */
abstract class SingletonRecordPage extends Page
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public ?Model $record = null;

    abstract protected static function resolveRecord(): Model;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public function mount(): void
    {
        $this->record = static::resolveRecord();
        $this->form->fill($this->record->attributesToArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->record)
            ->statePath('data')
            ->columns(1);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Simpan')->submit('save')->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
        $this->form->model($this->record)->saveRelationships();

        Notification::make()->success()->title('Tersimpan')->send();
    }
}
