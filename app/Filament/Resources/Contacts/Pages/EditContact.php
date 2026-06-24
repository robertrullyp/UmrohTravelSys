<?php

namespace App\Filament\Resources\Contacts\Pages;

use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
