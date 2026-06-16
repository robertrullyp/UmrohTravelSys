<?php

namespace App\Filament\Resources\CompanyProfiles\Pages;

use App\Filament\Resources\CompanyProfiles\CompanyProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditCompanyProfile extends EditRecord
{
    protected static string $resource = CompanyProfileResource::class;

    public function mount(int|string $record): void
    {
        $this->redirect(CompanyProfileResource::getUrl('index'), navigate: false);
    }
}
