<?php

namespace App\Filament\Resources\VisitorLogs;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\VisitorLogs\Pages\ListVisitorLogs;
use App\Models\VisitorLog;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VisitorLogResource extends PermissionResource
{
    protected static ?string $model = VisitorLog::class;

    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'logs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Log';

    protected static ?string $modelLabel = 'Log';

    protected static ?string $pluralModelLabel = 'Log';

    protected static ?int $navigationSort = 4;

    protected static function permissionPrefix(): string
    {
        return 'logs';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('logs.delete') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('logs.delete') ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('visited_at')
                    ->label('Waktu Kunjungan')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('path')
                    ->label('Halaman Dibuka')
                    ->searchable()
                    ->wrap()
                    ->limit(64),
                TextColumn::make('route_name')
                    ->label('Jenis Halaman')
                    ->formatStateUsing(fn (?string $state): string => static::routeLabel($state))
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('ip_hash')
                    ->label('Pengunjung Anonim')
                    ->formatStateUsing(fn (?string $state): string => static::anonymousVisitorLabel($state))
                    ->placeholder('-'),
                TextColumn::make('user_agent_hash')
                    ->label('Hash Browser')
                    ->limit(12)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('period')
                    ->label('Periode')
                    ->options([
                        'today' => 'Hari ini',
                        '7' => '7 hari',
                        '30' => '30 hari',
                        '90' => '90 hari',
                        'all' => 'Semua',
                    ])
                    ->default('90')
                    ->query(fn (Builder $query, array $data): Builder => static::applyPeriodFilter($query, $data['value'] ?? '90')),
            ])
            ->defaultSort('visited_at', 'desc')
            ->paginated([10, 25, 50])
            ->stackedOnMobile();
    }

    public static function pruneOlderThan(int $days): int
    {
        return VisitorLog::query()
            ->where('visited_at', '<', now()->subDays($days))
            ->delete();
    }

    public static function applyPeriodFilter(Builder $query, mixed $period): Builder
    {
        return match ((string) $period) {
            'today' => $query->whereDate('visited_at', today()),
            '7', '30', '90' => $query->where('visited_at', '>=', now()->subDays((int) $period)),
            default => $query,
        };
    }

    private static function routeLabel(?string $routeName): string
    {
        return match ($routeName) {
            'home' => 'Beranda',
            'profile' => 'Profil',
            'packages' => 'Daftar Paket',
            'package.show' => 'Detail Paket',
            'schedules' => 'Jadwal',
            'galleries' => 'Galeri',
            'contact' => 'Kontak',
            'bookings.create' => 'Booking',
            'bookings.store' => 'Kirim Booking',
            'bookings.package' => 'Booking Paket',
            'bookings.status' => 'Cek Status Booking',
            default => filled($routeName) ? str($routeName)->headline()->toString() : '-',
        };
    }

    private static function anonymousVisitorLabel(?string $hash): string
    {
        if (blank($hash)) {
            return '-';
        }

        return 'Pengunjung '.str($hash)->substr(0, 8)->upper();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitorLogs::route('/'),
        ];
    }
}
