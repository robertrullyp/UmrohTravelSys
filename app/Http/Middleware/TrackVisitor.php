<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        $now = now();
        $userAgent = (string) $request->userAgent();

        VisitorLog::query()->create([
            'visited_on' => $now->toDateString(),
            'visited_at' => $now,
            'path' => $request->path() === '/' ? '/' : '/' . trim($request->path(), '/'),
            'route_name' => $request->route()?->getName(),
            'ip_hash' => $this->hashValue((string) $request->ip()),
            'user_agent_hash' => $userAgent !== '' ? $this->hashValue($userAgent) : null,
        ]);

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->expectsJson()) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->is('admin*', 'build*', 'livewire*', 'storage*')) {
            return false;
        }

        $routeName = (string) $request->route()?->getName();

        return ! str($routeName)->startsWith('filament.');
    }

    private function hashValue(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }
}
