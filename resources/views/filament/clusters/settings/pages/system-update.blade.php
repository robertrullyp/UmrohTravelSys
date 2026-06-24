@php
    $status = $lastCheck['status'] ?? null;
    $tokenConfigured = $info['github_token']['configured'] ?? false;
    $statusLabel = match ($status) {
        'up_to_date' => 'Aplikasi sudah menggunakan versi terbaru',
        'update_available' => 'Pembaruan baru tersedia',
        'remote_empty_or_unreachable' => $tokenConfigured ? 'Pembaruan belum dapat diperiksa' : 'Akses pembaruan belum diatur',
        default => 'Pembaruan belum diperiksa',
    };
    $statusDescription = match ($status) {
        'up_to_date' => 'Tidak ada tindakan yang perlu dilakukan saat ini.',
        'update_available' => 'Klik Perbarui Sekarang untuk memasang versi terbaru.',
        'remote_empty_or_unreachable' => $tokenConfigured
            ? 'Sistem belum dapat terhubung ke server pembaruan. Coba cek kembali beberapa saat lagi.'
            : 'Klik Atur Akses Pembaruan, simpan kode akses, lalu cek pembaruan kembali.',
        default => $tokenConfigured
            ? 'Klik Cek Pembaruan untuk memastikan aplikasi menggunakan versi terbaru.'
            : 'Atur akses pembaruan terlebih dahulu agar sistem dapat mencari versi terbaru.',
    };
    $statusPanelClass = match ($status) {
        'up_to_date' => 'border-success-200 bg-success-50 dark:border-success-500/30 dark:bg-success-500/10',
        'update_available' => 'border-warning-200 bg-warning-50 dark:border-warning-500/30 dark:bg-warning-500/10',
        'remote_empty_or_unreachable' => 'border-danger-200 bg-danger-50 dark:border-danger-500/30 dark:bg-danger-500/10',
        default => 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/60',
    };
    $statusTextClass = match ($status) {
        'up_to_date' => 'text-success-700 dark:text-success-300',
        'update_available' => 'text-warning-700 dark:text-warning-300',
        'remote_empty_or_unreachable' => 'text-danger-700 dark:text-danger-300',
        default => 'text-gray-700 dark:text-gray-200',
    };
    $formatDate = static function (?string $value, bool $withTime = false): ?string {
        if (blank($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)
                ->locale('id')
                ->translatedFormat($withTime ? 'd M Y, H.i' : 'd F Y');
        } catch (\Throwable) {
            return $value;
        }
    };
    $checkedAt = $formatDate($lastCheck['checked_at'] ?? null, true);
    $releaseVersion = $releaseNotes['version'] ?? ($info['version'] ?? '-');
    $releaseDate = $formatDate($releaseNotes['date'] ?? null);
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Status Saat Ini
        </x-slot>

        <div class="rounded-lg border p-4 {{ $statusPanelClass }}">
            <div class="flex items-start gap-3">
                @switch($status)
                    @case('up_to_date')
                        <x-heroicon-o-check-circle class="system-update-status-icon text-success-600 dark:text-success-400" />
                        @break
                    @case('update_available')
                        <x-heroicon-o-arrow-down-circle class="system-update-status-icon text-warning-600 dark:text-warning-400" />
                        @break
                    @case('remote_empty_or_unreachable')
                        <x-heroicon-o-exclamation-triangle class="system-update-status-icon text-danger-600 dark:text-danger-400" />
                        @break
                    @default
                        <x-heroicon-o-information-circle class="system-update-status-icon text-gray-500 dark:text-gray-400" />
                @endswitch

                <div class="min-w-0">
                    <p class="font-bold {{ $statusTextClass }}">{{ $statusLabel }}</p>
                    <p class="mt-1 text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $statusDescription }}</p>
                    @if ($checkedAt)
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Diperiksa {{ $checkedAt }}</p>
                    @endif
                </div>
            </div>
        </div>

        <dl class="mt-4 grid gap-4 sm:grid-cols-2 sm:divide-x sm:divide-gray-200 dark:sm:divide-gray-700">
            <div class="sm:pe-4">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Versi terpasang</dt>
                <dd class="mt-1 text-base font-bold text-gray-950 dark:text-white">{{ $info['version'] ?? '-' }}</dd>
            </div>

            <div class="sm:ps-4">
                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Koneksi pembaruan</dt>
                <dd class="mt-1 flex items-center gap-2 text-sm font-bold {{ $tokenConfigured ? 'text-success-700 dark:text-success-300' : 'text-warning-700 dark:text-warning-300' }}">
                    <span class="system-update-status-dot {{ $tokenConfigured ? 'bg-success-500' : 'bg-warning-500' }}"></span>
                    {{ $tokenConfigured ? 'Akses tersimpan' : 'Belum terhubung' }}
                </dd>
            </div>
        </dl>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Pembaruan Terakhir
        </x-slot>

        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
            <span class="font-bold text-gray-950 dark:text-white">{{ $releaseVersion }}</span>
            @if ($releaseDate)
                <span class="text-xs text-gray-500 dark:text-gray-400">Dirilis {{ $releaseDate }}</span>
            @endif
        </div>

        @if (! empty($releaseNotes['notes']))
            <ul class="mt-4 space-y-3">
                @foreach (array_slice($releaseNotes['notes'], 0, 3) as $note)
                    <li class="flex items-start gap-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                        <x-heroicon-o-check class="system-update-list-icon text-success-600 dark:text-success-400" />
                        <span>{{ $note }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Belum ada informasi pembaruan.</p>
        @endif
    </x-filament::section>

    @if ($lastUpdate)
        <x-filament::section>
            <x-slot name="heading">
                Hasil Pembaruan
            </x-slot>

            <div class="flex items-start gap-3">
                @if ($lastUpdate['successful'] ?? false)
                    <x-heroicon-o-check-circle class="system-update-status-icon text-success-600 dark:text-success-400" />
                @else
                    <x-heroicon-o-x-circle class="system-update-status-icon text-danger-600 dark:text-danger-400" />
                @endif

                <div>
                    <p class="font-bold text-gray-950 dark:text-white">
                        {{ ($lastUpdate['successful'] ?? false) ? 'Pembaruan berhasil dipasang' : 'Pembaruan belum berhasil' }}
                    </p>
                    @if ($lastUpdate['updated_at'] ?? null)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $formatDate($lastUpdate['updated_at'], true) }}</p>
                    @endif
                    @if (! ($lastUpdate['successful'] ?? false))
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Coba cek pembaruan kembali. Jika masih gagal, hubungi pengelola sistem.</p>
                    @endif
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
