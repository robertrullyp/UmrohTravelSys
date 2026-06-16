<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\PermissionResource as BasePermissionResource;
use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\EditPermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends BasePermissionResource
{
    protected static ?string $model = Permission::class;

    protected static ?string $cluster = Settings::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;
    protected static ?string $navigationLabel = 'Permissions';
    protected static ?string $modelLabel = 'Permission';
    protected static ?string $pluralModelLabel = 'Permissions';
    protected static ?int $navigationSort = 3;

    protected static function permissionPrefix(): string
    {
        return 'permissions';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Permission')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Permission')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Permission')->searchable()->sortable(),
                TextColumn::make('roles_count')->label('Dipakai Role')->counts('roles')->sortable(),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
