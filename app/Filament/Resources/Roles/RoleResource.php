<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleResource extends PermissionResource
{
    protected static ?string $model = Role::class;

    protected static ?string $cluster = Settings::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $modelLabel = 'Role';
    protected static ?string $pluralModelLabel = 'Roles';
    protected static ?int $navigationSort = 2;

    protected static function permissionPrefix(): string
    {
        return 'roles';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Role & Hak Akses')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Role')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('permissions')
                        ->label('Permissions')
                        ->relationship('permissions', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Role')->searchable()->sortable(),
                TextColumn::make('permissions_count')->label('Jumlah Permission')->counts('permissions')->sortable(),
                TextColumn::make('users_count')->label('Jumlah User')->counts('users')->sortable(),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->visible(fn (Role $record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (Role $record): bool => static::canDelete($record)),
            ])
            ->defaultSort('name');
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && $record instanceof Role && $record->name !== 'super-admin';
    }

    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record) && $record instanceof Role && $record->name !== 'super-admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
