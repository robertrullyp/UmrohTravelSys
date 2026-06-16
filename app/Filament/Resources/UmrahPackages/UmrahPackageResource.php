<?php

namespace App\Filament\Resources\UmrahPackages;

use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\UmrahPackages\Pages\CreateUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\EditUmrahPackage;
use App\Filament\Resources\UmrahPackages\Pages\ListUmrahPackages;
use App\Filament\Resources\UmrahPackages\Schemas\UmrahPackageForm;
use App\Filament\Resources\UmrahPackages\Tables\UmrahPackagesTable;
use App\Models\UmrahPackage;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UmrahPackageResource extends PermissionResource
{
    protected static ?string $model = UmrahPackage::class;

    protected static ?string $recordRouteKeyName = 'slug';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Paket Umrah';
    protected static ?string $modelLabel = 'Paket Umrah';
    protected static ?string $pluralModelLabel = 'Paket Umrah';
    protected static ?int $navigationSort = 2;

    protected static function permissionPrefix(): string
    {
        return 'packages';
    }

    public static function form(Schema $schema): Schema
    {
        return UmrahPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UmrahPackagesTable::configure($table);
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
            'index' => ListUmrahPackages::route('/'),
            'create' => CreateUmrahPackage::route('/create'),
            'edit' => EditUmrahPackage::route('/{record}/edit'),
        ];
    }
}
