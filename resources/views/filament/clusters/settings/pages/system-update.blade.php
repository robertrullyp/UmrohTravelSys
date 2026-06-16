<x-filament-panels::page>
    <div class="grid gap-4 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                Informasi Versi
            </x-slot>

            <dl class="grid gap-3 text-sm">
                <div class="flex items-start justify-between gap-4">
                    <dt class="font-semibold text-gray-500 dark:text-gray-400">Versi aplikasi</dt>
                    <dd class="text-right font-bold text-gray-950 dark:text-white">{{ $info['version'] ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="font-semibold text-gray-500 dark:text-gray-400">Branch lokal</dt>
                    <dd class="text-right font-mono text-gray-950 dark:text-white">{{ $info['branch'] ?? '-' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <dt class="font-semibold text-gray-500 dark:text-gray-400">Commit lokal</dt>
                    <dd class="text-right font-mono text-gray-950 dark:text-white">{{ $info['commit'] ?? '-' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-semibold text-gray-500 dark:text-gray-400">Remote aktif</dt>
                    <dd class="break-all font-mono text-gray-950 dark:text-white">{{ $info['remote_url'] ?? '-' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="font-semibold text-gray-500 dark:text-gray-400">Sumber update</dt>
                    <dd class="break-all font-mono text-gray-950 dark:text-white">
                        {{ $info['source_repository'] ?? '-' }} <span class="text-gray-500">({{ $info['source_branch'] ?? 'main' }})</span>
                    </dd>
                </div>
            </dl>
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
                    Tekan <strong>Check Update</strong> untuk membandingkan commit lokal dengan branch update resmi.
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
