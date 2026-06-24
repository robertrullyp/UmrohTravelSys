<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Login Admin';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Login Admin';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->placeholder('Masukkan email admin')
            ->autocomplete('username')
            ->autofocus()
            ->required();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->placeholder('Masukkan kata sandi')
            ->password()
            ->revealable()
            ->autocomplete('current-password')
            ->required();
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Login')
            ->submit('authenticate');
    }
}
