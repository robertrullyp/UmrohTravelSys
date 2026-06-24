<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus Role')];
    }
}
