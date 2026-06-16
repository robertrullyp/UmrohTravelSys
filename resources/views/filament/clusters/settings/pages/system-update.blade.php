<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Sistem
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Versi</div>
                <div class="mt-1 text-base font-extrabold text-gray-950 dark:text-white">{{ $info['version'] ?? '-' }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Commit Lokal</div>
                <div class="mt-1 font-mono text-sm font-bold text-gray-950 dark:text-white">{{ ($info['branch'] ?? '-') . '@' . ($info['commit'] ?? '-') }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Source</div>
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
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $info['github_token']['updated_at'] ?? 'Input token untuk repo private.' }}</div>
            </div>
        </div>

        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
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
                    <span class="font-bold text-gray-950 dark:text-white">{{ $releaseNotes['version'] ?? ($info['version'] ?? '-') }}</span>
                    @if ($releaseNotes['date'] ?? null)
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $releaseNotes['date'] }}</span>
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
                Status Remote
            </x-slot>

            @if ($lastCheck)
                <div class="space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <span class="font-semibold text-gray-500 dark:text-gray-400">Status</span>
                        <span class="rounded-md bg-primary-50 px-2 py-1 font-bold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                            {{ str($lastCheck['status'] ?? '-')->replace('_', ' ')->title() }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <span class="font-semibold text-gray-500 dark:text-gray-400">Remote hash</span>
                        <span class="text-right font-mono text-gray-950 dark:text-white">{{ $lastCheck['remote_hash'] ?? '-' }}</span>
                    </div>
                    <div class="grid gap-1">
                        <span class="font-semibold text-gray-500 dark:text-gray-400">Output</span>
                        <pre class="max-h-44 overflow-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">{{ $lastCheck['step']['output'] ?? '-' }}</pre>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Tekan <strong>Check Update</strong> untuk membandingkan commit lokal dengan branch update resmi. Untuk repo private, input token FAT terlebih dahulu.
                </p>
            @endif
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

                <div class="grid gap-3">
                    @foreach (($lastUpdate['steps'] ?? []) as $step)
                        <details class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
                            <summary class="cursor-pointer text-sm font-bold text-gray-950 dark:text-white">
                                {{ $step['successful'] ? 'OK' : 'ERROR' }}: {{ $step['command'] }}
                            </summary>
                            <pre class="mt-3 max-h-64 overflow-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">{{ $step['output'] ?: '-' }}</pre>
                        </details>
                    @endforeach
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
