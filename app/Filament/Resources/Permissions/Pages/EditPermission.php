<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus Permission')];
    }
}
