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
use Filament\Schemas\Components\Grid;
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

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    protected static function permissionPrefix(): string
    {
        return 'users';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make([
                'default' => 1,
                'lg' => 12,
            ])
                ->schema([
                    Section::make('Foto Profil')
                        ->description('Tampil di menu akun admin. Opsional.')
                        ->schema([
                            FileUpload::make('avatar_path')
                                ->label('Foto Profil')
                                ->disk('public')
                                ->directory('avatars')
                                ->image()
                                ->avatar()
                                ->imageEditor()
                                ->imageCropAspectRatio('1:1')
                                ->maxSize(2048)
                                ->previewable()
                                ->openable()
                                ->downloadable()
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'default' => 'full',
                            'lg' => 3,
                        ]),
                    Section::make('Data Pengguna Admin')
                        ->description('Akun yang bisa masuk panel admin. Role menentukan menu dan aksi yang boleh digunakan.')
                        ->columns([
                            'default' => 1,
                            'md' => 2,
                        ])
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama')
                                ->helperText('Nama yang tampil di panel admin.')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label('Email')
                                ->helperText('Dipakai untuk login. Pastikan email aktif dan unik.')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label('Nomor Telepon')
                                ->helperText('Opsional. Dipakai sebagai kontak internal admin.')
                                ->tel()
                                ->maxLength(32)
                                ->rule('regex:/^[0-9+()\s-]{8,32}$/'),
                            TextInput::make('password')
                                ->label('Kata Sandi')
                                ->helperText('Wajib saat tambah pengguna. Saat edit, kosongkan jika tidak ingin mengganti kata sandi.')
                                ->password()
                                ->autocomplete('new-password')
                                ->revealable()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->minLength(8),
                            TextInput::make('password_confirmation')
                                ->label('Konfirmasi Kata Sandi')
                                ->helperText('Isi ulang kata sandi baru agar tidak salah ketik.')
                                ->password()
                                ->autocomplete('new-password')
                                ->revealable()
                                ->same('password')
                                ->requiredWith('password')
                                ->dehydrated(false),
                            Select::make('roles')
                                ->label('Role / Hak Akses')
                                ->helperText('Pilih sesuai tugas pengguna. Super-admin memiliki akses penuh.')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columnSpan([
                            'default' => 'full',
                            'lg' => 9,
                        ]),
                ])
                ->columnSpanFull(),
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
                    ->defaultImageUrl(asset('images/site/logo.png'))
                    ->visibleFrom('md'),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('phone')->label('Nomor Telepon')->searchable()->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Role / Hak Akses')
                    ->badge()
                    ->separator(','),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->visibleFrom('lg')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (User $record): bool => static::canDelete($record)),
            ])
            ->stackedOnMobile()
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
