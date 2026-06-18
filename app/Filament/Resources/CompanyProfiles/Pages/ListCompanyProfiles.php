<?php

namespace App\Filament\Resources\CompanyProfiles\Pages;

use App\Filament\Resources\CompanyProfiles\CompanyProfileResource;
use App\Models\CompanyProfile;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class ListCompanyProfiles extends EditRecord
{
    protected static string $resource = CompanyProfileResource::class;

    protected static ?string $title = 'Profil Perusahaan';

    public function mount(int|string $record = 1): void
    {
        $this->record = $this->resolveSingletonRecord();

        $this->authorizeAccess();
        $this->fillForm();

        $this->previousUrl = CompanyProfileResource::getUrl('index');
    }

    public function getBreadcrumb(): string
    {
        return 'Profil';
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function getPageClasses(): array
    {
        return [
            ...parent::getPageClasses(),
            'fi-company-profile-singleton',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Profil'),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil perusahaan disimpan.';
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    private function resolveSingletonRecord(): Model
    {
        $record = CompanyProfile::query()->find(1);

        if ($record) {
            return $record;
        }

        $record = new CompanyProfile([
            'company_name' => 'PT Amara Al Medina Travel',
            'about' => 'Silakan lengkapi profil perusahaan.',
            'vision' => 'Silakan lengkapi visi perusahaan.',
            'missions' => [],
            'is_active' => true,
        ]);
        $record->forceFill(['id' => 1]);
        $record->save();

        return $record;
    }
}
