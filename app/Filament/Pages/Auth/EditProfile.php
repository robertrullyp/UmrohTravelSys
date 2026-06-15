<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'My Account';

    public static function getLabel(): string
    {
        return 'My Account';
    }

    public function getTitle(): string|Htmlable
    {
        return 'My Account';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_path')
            ->label('Foto Avatar')
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

    protected function getPasswordFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordFormComponent();

        return $component->label('Kata sandi baru');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        /** @var TextInput $component */
        $component = parent::getPasswordConfirmationFormComponent();

        return $component
            ->label('Konfirmasi Kata sandi baru')
            ->required(fn (Get $get): bool => filled($get('password')))
            ->visible();
    }
}
