<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = GalleryResource::class;

    public function getTitle(): string
    {
        return 'Edit Album';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
