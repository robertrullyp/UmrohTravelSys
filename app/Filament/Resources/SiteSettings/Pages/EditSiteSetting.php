<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = SiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Pengaturan Custom')
                ->visible(fn (): bool => ! SiteSetting::isSystemKey($this->getRecord()->key)),
        ];
    }
}
