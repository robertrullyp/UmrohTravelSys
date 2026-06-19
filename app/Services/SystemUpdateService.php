<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class SystemUpdateService
{
    private const GITHUB_TOKEN_KEY = 'update_github_fat';

    /**
     * @return array<string, mixed>
     */
    public function info(): array
    {
        return [
            'version' => $this->version(),
            'branch' => $this->gitValue(['git', 'rev-parse', '--abbrev-ref', 'HEAD']),
            'commit' => $this->gitValue(['git', 'rev-parse', '--short', 'HEAD']),
            'full_commit' => $this->gitValue(['git', 'rev-parse', 'HEAD']),
            'remote_url' => $this->gitValue(['git', 'config', '--get', 'remote.origin.url']),
            'source_repository' => $this->repository(),
            'source_branch' => $this->branch(),
            'github_token' => $this->githubTokenInfo(),
        ];
    }

    /**
     * @return array{version: string, date: string|null, notes: array<int, string>}
     */
    public function latestReleaseNotes(): array
    {
        $fallback = [
            'version' => $this->version(),
            'date' => null,
            'notes' => [],
        ];
        $path = base_path('CHANGELOG.md');

        if (! is_file($path)) {
            return $fallback;
        }

        $contents = (string) file_get_contents($path);

        if (! preg_match('/^##\s+\[(?<version>[^\]]+)\](?:\s+-\s+(?<date>[^\r\n]+))?\R(?<body>.*?)(?=^##\s+\[|\z)/ms', $contents, $matches)) {
            return $fallback;
        }

        $notes = collect(preg_split('/\R/', trim((string) $matches['body'])) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => str_starts_with($line, '- '))
            ->map(fn (string $line): string => trim(substr($line, 2)))
            ->take(4)
            ->values()
            ->all();

        return [
            'version' => trim((string) $matches['version']),
            'date' => isset($matches['date']) ? trim((string) $matches['date']) : null,
            'notes' => $notes,
        ];
    }

    /**
     * @return array{configured: bool, updated_at: string|null}
     */
    public function githubTokenInfo(): array
    {
        $setting = SiteSetting::query()
            ->where('key', self::GITHUB_TOKEN_KEY)
            ->first();

        return [
            'configured' => filled($setting?->value),
            'updated_at' => $setting?->updated_at?->toDateTimeString(),
        ];
    }

    public function storeGitHubToken(string $token): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => self::GITHUB_TOKEN_KEY],
            ['value' => Crypt::encryptString(trim($token))],
        );
    }

    public function forgetGitHubToken(): void
    {
        SiteSetting::query()
            ->where('key', self::GITHUB_TOKEN_KEY)
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function check(): array
    {
        $info = $this->info();
        $remote = $this->run(
            ['git', 'ls-remote', '--heads', $this->repository(), $this->branch()],
            timeout: 60,
            withGitCredentials: true,
        );
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
        $composer = $this->composerCommand($php);
        $commands = [
            ['git', 'fetch', 'origin', $branch],
            ['git', 'reset', '--hard', "origin/{$branch}"],
            [...$composer, 'install', '--no-dev', '--optimize-autoloader'],
            ['npm', 'ci'],
            ['npm', 'run', 'build'],
            [$php, 'artisan', 'migrate', '--force'],
            [$php, 'artisan', 'optimize'],
            [$php, 'artisan', 'filament:optimize'],
        ];

        $steps = [];
        $successful = true;

        foreach ($commands as $command) {
            $step = $this->run(
                $command,
                timeout: 900,
                withGitCredentials: $command[0] === 'git' && $command[1] === 'fetch',
            );
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
    private function run(array $command, int $timeout = 120, bool $withGitCredentials = false): array
    {
        $askPassPath = null;
        $environment = null;

        if ($withGitCredentials) {
            $environment = ['GIT_TERMINAL_PROMPT' => '0'];
            $token = $this->githubToken();

            if ($token) {
                $askPassPath = $this->createAskPassScript();
                $environment['GIT_ASKPASS'] = $askPassPath;
                $environment['GITHUB_FINE_GRAINED_TOKEN'] = $token;
            }
        }

        $process = new Process($command, base_path(), $environment, null, $timeout);
        $output = '';

        try {
            $process->run();
            $output = trim($this->redactSecrets($process->getOutput().PHP_EOL.$process->getErrorOutput()));
        } catch (ProcessTimedOutException $exception) {
            $output = $exception->getMessage();
        } finally {
            if ($askPassPath) {
                @unlink($askPassPath);
            }
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

    private function githubToken(): ?string
    {
        $encrypted = SiteSetting::query()
            ->where('key', self::GITHUB_TOKEN_KEY)
            ->value('value');

        if (! $encrypted) {
            return null;
        }

        try {
            $token = Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }

        return filled($token) ? $token : null;
    }

    private function createAskPassScript(): string
    {
        $directory = storage_path('framework/cache');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/git-askpass-'.bin2hex(random_bytes(8)).'.sh';
        $script = <<<'SH'
#!/bin/sh
case "$1" in
    *Username*) echo "x-access-token" ;;
    *Password*) printf '%s' "$GITHUB_FINE_GRAINED_TOKEN" ;;
    *) echo "" ;;
esac
SH;

        file_put_contents($path, $script);
        chmod($path, 0700);

        return $path;
    }

    private function redactSecrets(string $output): string
    {
        $token = $this->githubToken();

        return $token ? str_replace($token, '[github-token-redacted]', $output) : $output;
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
     * @return array<int, string>
     */
    private function composerCommand(string $php): array
    {
        foreach (['/usr/local/bin/composer', '/usr/bin/composer'] as $composer) {
            if (is_file($composer)) {
                return [$php, $composer];
            }
        }

        return ['composer'];
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
        return mb_strlen($output) > 8000 ? mb_substr($output, 0, 8000).PHP_EOL.'[output dipotong]' : $output;
    }

    private function version(): string
    {
        $version = (string) config('admin.version', '');

        if ($version !== '') {
            return $version;
        }

        $path = base_path('VERSION');

        return is_file($path) ? trim((string) file_get_contents($path)) : 'v2026.06.19';
    }
}
