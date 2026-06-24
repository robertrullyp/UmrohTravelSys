<?php

namespace App\Filament\Resources\VisitorLogs\Pages;

use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListVisitorLogs extends ListRecords
{
    protected static string $resource = VisitorLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearOldLogs')
                ->label('Bersihkan Log Lama')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->can('logs.delete') ?? false)
                ->form([
                    Select::make('days')
                        ->label('Hapus log >')
                        ->options([
                            1 => '1 hari',
                            3 => '3 hari',
                            7 => '7 hari',
                            14 => '14 hari',
                            30 => '30 hari',
                            60 => '60 hari',
                            90 => '90 hari',
                            180 => '180 hari',
                            365 => '365 hari',
                        ])
                        ->default(90)
                        ->native(false)
                        ->required()
                        ->helperText('Contoh: pilih 1 hari untuk menghapus log yang usianya lebih dari 1 hari. Log yang lebih baru tetap disimpan.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Bersihkan log lama?')
                ->modalDescription('Tindakan ini hanya menghapus log lama sesuai usia yang dipilih dan tidak menghapus data booking atau konten website.')
                ->modalSubmitActionLabel('Bersihkan Log')
                ->action(fn (array $data): null => $this->clearOldLogs((int) $data['days'])),
        ];
    }

    private function clearOldLogs(int $days): null
    {
        abort_unless(auth()->user()?->can('logs.delete'), 403);

        $deleted = VisitorLogResource::pruneOlderThan($days);

        $this->flushCachedTableRecords();

        Notification::make()
            ->success()
            ->title('Log lama dibersihkan.')
            ->body($deleted.' log lebih lama dari '.$days.' hari telah dihapus.')
            ->send();

        return null;
    }
}
