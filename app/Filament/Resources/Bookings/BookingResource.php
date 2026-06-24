<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Pages\ViewBooking;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Schemas\BookingInfolist;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Filament\Resources\PermissionResource;
use App\Models\Booking;
use App\Services\BookingStatusService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BookingResource extends PermissionResource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Booking';

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $pluralModelLabel = 'Booking';

    protected static ?int $navigationSort = 2;

    protected static function permissionPrefix(): string
    {
        return 'bookings';
    }

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record)
            && $record instanceof Booking
            && $record->status === Booking::STATUS_PENDING;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Booking::query()->where('status', Booking::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Setujui')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Booking $record): bool => $record->status === Booking::STATUS_PENDING
                && (auth()->user()?->can('bookings.approve') ?? false))
            ->requiresConfirmation()
            ->modalHeading('Setujui booking?')
            ->modalDescription('Kuota jadwal akan langsung dikurangi sesuai jumlah jamaah.')
            ->schema([
                Textarea::make('admin_notes')
                    ->label('Catatan Admin')
                    ->rows(3),
            ])
            ->action(function (Booking $record, array $data): void {
                app(BookingStatusService::class)->approve(
                    $record,
                    auth()->user(),
                    $data['admin_notes'] ?? null,
                );

                Notification::make()
                    ->success()
                    ->title('Booking disetujui dan kuota diperbarui.')
                    ->send();
            });
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Tolak')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Booking $record): bool => $record->status === Booking::STATUS_PENDING
                && (auth()->user()?->can('bookings.reject') ?? false))
            ->requiresConfirmation()
            ->modalHeading('Tolak booking?')
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->required()
                    ->rows(3),
                Textarea::make('admin_notes')
                    ->label('Catatan Internal')
                    ->rows(3),
            ])
            ->action(function (Booking $record, array $data): void {
                app(BookingStatusService::class)->reject(
                    $record,
                    auth()->user(),
                    $data['rejection_reason'],
                    $data['admin_notes'] ?? null,
                );

                Notification::make()->success()->title('Booking ditolak.')->send();
            });
    }

    public static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Batalkan')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('warning')
            ->visible(fn (Booking $record): bool => in_array(
                $record->status,
                [Booking::STATUS_PENDING, Booking::STATUS_APPROVED],
                true,
            ) && (auth()->user()?->can('bookings.cancel') ?? false))
            ->requiresConfirmation()
            ->modalHeading('Batalkan booking?')
            ->modalDescription('Jika booking sudah disetujui, kuota akan dikembalikan ke jadwal.')
            ->schema([
                Textarea::make('admin_notes')
                    ->label('Catatan Pembatalan')
                    ->rows(3),
            ])
            ->action(function (Booking $record, array $data): void {
                app(BookingStatusService::class)->cancel(
                    $record,
                    auth()->user(),
                    $data['admin_notes'] ?? null,
                );

                Notification::make()->success()->title('Booking dibatalkan.')->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'view' => ViewBooking::route('/{record}'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }
}
