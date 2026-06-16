<?php

namespace App\Filament\Resources\CompanyProfiles;

use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\CompanyProfiles\Pages\EditCompanyProfile;
use App\Filament\Resources\CompanyProfiles\Pages\ListCompanyProfiles;
use App\Filament\Resources\CompanyProfiles\Schemas\CompanyProfileForm;
use App\Models\CompanyProfile;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanyProfileResource extends PermissionResource
{
    protected static ?string $model = CompanyProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'Profil';
    protected static ?string $modelLabel = 'Profil';
    protected static ?string $pluralModelLabel = 'Profil';
    protected static ?int $navigationSort = 5;

    protected static function permissionPrefix(): string
    {
        return 'profiles';
    }

    public static function form(Schema $schema): Schema
    {
        return CompanyProfileForm::configure($schema);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyProfiles::route('/'),
            'edit' => EditCompanyProfile::route('/{record}/edit'),
        ];
    }
}
