<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Akun Saya';

    public static function getLabel(): string
    {
        return 'Akun Saya';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Akun Saya';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Admin')
                    ->description('Data ini hanya untuk akun Anda di panel admin.')
                    ->columns(2)
                    ->schema([
                        $this->getAvatarFormComponent(),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPhoneFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ]),
            ]);
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_path')
            ->label('Foto Profil')
            ->helperText('Tampil di menu akun admin. Opsional.')
            ->disk('public')
            ->directory('avatars')
            ->image()
            ->avatar()
            ->imageEditor()
            ->imageCropAspectRatio('1:1')
            ->maxSize(2048)
            ->previewable()
            ->downloadable()
            ->openable()
            ->removeUploadedFileButtonPosition('right')
            ->uploadButtonPosition('right')
            ->columnSpanFull();
    }

    protected function getNameFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getNameFormComponent();

        return $component
            ->label('Nama')
            ->helperText('Nama yang tampil di menu akun admin.');
    }

    protected function getEmailFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getEmailFormComponent();

        return $component
            ->label('Email')
            ->helperText('Dipakai untuk login ke panel admin.');
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Nomor Telepon')
            ->helperText('Opsional. Dipakai sebagai kontak internal admin.')
            ->tel()
            ->maxLength(32)
            ->rule('regex:/^[0-9+()\s-]{8,32}$/');
    }

    protected function getPasswordFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordFormComponent();

        return $component
            ->label('Kata Sandi Baru')
            ->helperText('Kosongkan jika tidak ingin mengganti kata sandi.');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordConfirmationFormComponent();

        return $component
            ->label('Konfirmasi Kata Sandi Baru')
            ->helperText('Isi ulang kata sandi baru agar tidak salah ketik.')
            ->required(fn (Get $get): bool => filled($get('password')))
            ->visible();
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getCurrentPasswordFormComponent();

        return $component
            ->label('Kata Sandi Saat Ini')
            ->helperText('Diperlukan untuk menyimpan perubahan akun.');
    }
}
