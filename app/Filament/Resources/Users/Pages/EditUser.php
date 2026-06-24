<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        if (! $record instanceof User || ! $record->hasRole('super-admin')) {
            return;
        }

        $selectedRoles = collect($this->data['roles'] ?? []);
        $superAdminRoleId = Role::findByName('super-admin')->getKey();

        if (! $selectedRoles->contains(fn ($roleId): bool => (int) $roleId === (int) $superAdminRoleId)
            && User::role('super-admin')->count() <= 1) {
            Notification::make()
                ->danger()
                ->title('Super-admin terakhir tidak dapat diturunkan.')
                ->send();

            throw ValidationException::withMessages([
                'roles' => 'Minimal satu user harus tetap memiliki role super-admin.',
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Pengguna')
                ->visible(fn (): bool => UserResource::canDelete($this->getRecord())),
        ];
    }
}
