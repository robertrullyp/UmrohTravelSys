<?php

namespace App\Filament\Resources\Users;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends PermissionResource
{
    protected static ?string $model = User::class;

    protected static ?string $cluster = Settings::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?int $navigationSort = 1;

    protected static function permissionPrefix(): string
    {
        return 'users';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Akun')
                ->columns(2)
                ->schema([
                    FileUpload::make('avatar_path')
                        ->label('Foto Avatar')
                        ->disk('public')
                        ->directory('avatars')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->columnSpanFull(),
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('password')
                        ->label('Kata Sandi')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->minLength(8),
                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Kata Sandi')
                        ->password()
                        ->revealable()
                        ->same('password')
                        ->requiredWith('password')
                        ->dehydrated(false),
                    Select::make('roles')
                        ->label('Role')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(asset('images/site/logo.png')),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (User $record): bool => static::canDelete($record)),
            ])
            ->defaultSort('name');
    }

    public static function canDelete(Model $record): bool
    {
        if (! parent::canDelete($record) || ! $record instanceof User) {
            return false;
        }

        if ($record->is(auth()->user())) {
            return false;
        }

        return ! ($record->hasRole('super-admin') && User::role('super-admin')->count() <= 1);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
