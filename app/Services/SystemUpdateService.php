<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class SystemUpdateService
{
    /**
     * @return array<string, mixed>
     */
    public function info(): array
    {
        return [
            'version' => config('admin.version', 'v2026.06.16'),
            'branch' => $this->gitValue(['git', 'rev-parse', '--abbrev-ref', 'HEAD']),
            'commit' => $this->gitValue(['git', 'rev-parse', '--short', 'HEAD']),
            'full_commit' => $this->gitValue(['git', 'rev-parse', 'HEAD']),
            'remote_url' => $this->gitValue(['git', 'config', '--get', 'remote.origin.url']),
            'source_repository' => $this->repository(),
            'source_branch' => $this->branch(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function check(): array
    {
        $info = $this->info();
        $remote = $this->run(['git', 'ls-remote', '--heads', $this->repository(), $this->branch()], timeout: 60);
        $remoteHash = $this->extractRemoteHash($remote['output']);
        $status = 'remote_empty_or_unreachable';

        if ($remoteHash) {
            $status = $remoteHash === ($info['full_commit'] ?? null) ? 'up_to_date' : 'update_available';
        }

        $result = [
            'checked_at' => now()->toDateTimeString(),
            'status' => $status,
            'remote_hash' => $remoteHash,
            'info' => $info,
            'step' => $remote,
        ];

        Log::info('System update check finished.', [
            'status' => $status,
            'remote_hash' => $remoteHash,
        ]);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function update(): array
    {
        $branch = $this->branch();
        $php = $this->phpBinary();
        $commands = [
            ['git', 'fetch', 'origin', $branch],
            ['git', 'reset', '--hard', "origin/{$branch}"],
            ['composer', 'install', '--no-dev', '--optimize-autoloader'],
            ['npm', 'ci'],
            ['npm', 'run', 'build'],
            [$php, 'artisan', 'migrate', '--force'],
            [$php, 'artisan', 'optimize'],
            [$php, 'artisan', 'filament:optimize'],
        ];

        $steps = [];
        $successful = true;

        foreach ($commands as $command) {
            $step = $this->run($command, timeout: 900);
            $steps[] = $step;

            if (! $step['successful']) {
                $successful = false;

                break;
            }
        }

        $result = [
            'updated_at' => now()->toDateTimeString(),
            'successful' => $successful,
            'steps' => $steps,
        ];

        Log::{$successful ? 'info' : 'error'}('System update run finished.', [
            'successful' => $successful,
            'steps' => collect($steps)->map(fn (array $step): array => [
                'command' => $step['command'],
                'exit_code' => $step['exit_code'],
                'successful' => $step['successful'],
            ])->all(),
        ]);

        return $result;
    }

    /**
     * @param  array<int, string>  $command
     * @return array<string, mixed>
     */
    private function run(array $command, int $timeout = 120): array
    {
        $process = new Process($command, base_path(), null, null, $timeout);
        $output = '';

        try {
            $process->run();
            $output = trim($process->getOutput() . PHP_EOL . $process->getErrorOutput());
        } catch (ProcessTimedOutException $exception) {
            $output = $exception->getMessage();
        }

        return [
            'command' => $this->formatCommand($command),
            'exit_code' => $process->getExitCode(),
            'successful' => $process->isSuccessful(),
            'output' => $this->truncate($output),
        ];
    }

    /**
     * @param  array<int, string>  $command
     */
    private function gitValue(array $command): string
    {
        $result = $this->run($command, timeout: 30);

        if (! $result['successful']) {
            return '-';
        }

        return trim((string) $result['output']) ?: '-';
    }

    private function extractRemoteHash(string $output): ?string
    {
        if (! preg_match('/^([a-f0-9]{40})\s+/i', trim($output), $matches)) {
            return null;
        }

        return $matches[1];
    }

    private function repository(): string
    {
        return (string) config('admin.update_repository', 'https://github.com/robertrullyp/UmrohTravelSys.git');
    }

    private function branch(): string
    {
        $branch = (string) config('admin.update_branch', 'main');

        return preg_match('/^[A-Za-z0-9._\/-]+$/', $branch) ? $branch : 'main';
    }

    private function phpBinary(): string
    {
        foreach (['/usr/bin/php8.3', '/usr/bin/php', PHP_BINARY, 'php'] as $binary) {
            if ($binary === 'php' || is_executable($binary)) {
                return $binary;
            }
        }

        return 'php';
    }

    /**
     * @param  array<int, string>  $command
     */
    private function formatCommand(array $command): string
    {
        return implode(' ', array_map(
            fn (string $part): string => str_contains($part, ' ') ? escapeshellarg($part) : $part,
            $command,
        ));
    }

    private function truncate(string $output): string
    {
        return mb_strlen($output) > 8000 ? mb_substr($output, 0, 8000) . PHP_EOL . '[output dipotong]' : $output;
    }
}
