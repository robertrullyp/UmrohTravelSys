@php
    $status = $lastCheck['status'] ?? null;
    $statusLabel = match ($status) {
        'up_to_date' => 'Sudah terbaru',
        'update_available' => 'Update tersedia',
        'remote_empty_or_unreachable' => 'Tidak dapat dicek',
        default => 'Belum dicek',
    };
    $statusClass = match ($status) {
        'up_to_date' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'update_available' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'remote_empty_or_unreachable' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
    $releaseVersion = $releaseNotes['version'] ?? ($info['version'] ?? '-');
    $releaseDate = $releaseNotes['date'] ?? null;
    $localCommit = $info['commit'] ?? '-';
    $remoteHash = $lastCheck['remote_hash'] ?? null;
    $remoteShort = $remoteHash ? substr((string) $remoteHash, 0, 7) : '-';
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Sistem
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Versi aplikasi</div>
                <div class="mt-1 text-base font-extrabold text-gray-950 dark:text-white">{{ $info['version'] ?? '-' }}</div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rilis terakhir</div>
                <div class="mt-1 text-sm font-extrabold text-gray-950 dark:text-white">{{ $releaseVersion }}</div>
                @if ($releaseDate)
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $releaseDate }}</div>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Commit lokal</div>
                <div class="mt-1 font-mono text-sm font-bold text-gray-950 dark:text-white">{{ ($info['branch'] ?? '-') . '@' . $localCommit }}</div>
                @if ($remoteShort !== '-')
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Remote: <span class="font-mono">{{ $remoteShort }}</span></div>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Source update</div>
                <div class="mt-1 text-sm font-bold text-gray-950 dark:text-white">{{ $info['source_branch'] ?? 'main' }}</div>
                <div class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $info['source_repository'] ?? '-' }}</div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Token FAT</div>
                <div class="mt-1">
                    @if ($info['github_token']['configured'] ?? false)
                        <span class="rounded-md bg-success-50 px-2 py-1 text-xs font-bold text-success-700 dark:bg-success-500/10 dark:text-success-300">
                            Tersimpan
                        </span>
                    @else
                        <span class="rounded-md bg-warning-50 px-2 py-1 text-xs font-bold text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                            Belum ada
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $info['github_token']['updated_at'] ?? 'Diperlukan untuk repo private.' }}</div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status update</div>
                <div class="mt-1">
                    <span class="rounded-md px-2 py-1 text-xs font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $lastCheck['checked_at'] ?? 'Belum pernah dicek.' }}
                </div>
            </div>
        </div>

        <div class="mt-3 truncate text-xs text-gray-500 dark:text-gray-400">
            Remote aktif: <span class="font-mono">{{ $info['remote_url'] ?? '-' }}</span>
        </div>
    </x-filament::section>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Catatan Rilis
            </x-slot>

            <div class="space-y-3 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-bold text-gray-950 dark:text-white">{{ $releaseVersion }}</span>
                    @if ($releaseDate)
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $releaseDate }}</span>
                    @endif
                </div>

                @if (! empty($releaseNotes['notes']))
                    <ul class="list-disc space-y-1 ps-5 text-gray-700 dark:text-gray-300">
                        @foreach ($releaseNotes['notes'] as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600 dark:text-gray-300">Belum ada catatan rilis di CHANGELOG.md.</p>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Detail Update
            </x-slot>

            <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                <div class="grid gap-2 sm:grid-cols-2">
                    <div>
                        <span class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Local</span>
                        <span class="font-mono text-gray-950 dark:text-white">{{ $localCommit }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Remote</span>
                        <span class="font-mono text-gray-950 dark:text-white">{{ $remoteShort }}</span>
                    </div>
                </div>

                <p>
                    Gunakan <strong>Check Update</strong> untuk membandingkan commit lokal dengan source update resmi.
                    Untuk repo private, simpan Token FAT terlebih dahulu.
                </p>

                @if ($lastCheck)
                    <details class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                        <summary class="cursor-pointer text-sm font-bold text-gray-950 dark:text-white">Output pengecekan remote</summary>
                        <pre class="mt-3 max-h-44 overflow-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">{{ $lastCheck['step']['output'] ?? '-' }}</pre>
                    </details>
                @endif
            </div>
        </x-filament::section>
    </div>

    @if ($lastUpdate)
        <x-filament::section>
            <x-slot name="heading">
                Output Update Terakhir
            </x-slot>

            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="font-semibold text-gray-500 dark:text-gray-400">Waktu:</span>
                    <span class="font-mono text-gray-950 dark:text-white">{{ $lastUpdate['updated_at'] ?? '-' }}</span>
                    <span class="rounded-md px-2 py-1 font-bold {{ ($lastUpdate['successful'] ?? false) ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300' : 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300' }}">
                        {{ ($lastUpdate['successful'] ?? false) ? 'Sukses' : 'Gagal' }}
                    </span>
                </div>

                <details class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                    <summary class="cursor-pointer text-sm font-bold text-gray-950 dark:text-white">Lihat detail command update</summary>

                    <div class="mt-3 grid gap-3">
                        @foreach (($lastUpdate['steps'] ?? []) as $step)
                            <details class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-950">
                                <summary class="cursor-pointer text-sm font-bold text-gray-950 dark:text-white">
                                    {{ $step['successful'] ? 'OK' : 'ERROR' }}: {{ $step['command'] }}
                                </summary>
                                <pre class="mt-3 max-h-64 overflow-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">{{ $step['output'] ?: '-' }}</pre>
                            </details>
                        @endforeach
                    </div>
                </details>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
