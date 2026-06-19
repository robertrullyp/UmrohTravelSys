<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalRedirect
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            (app()->environment(['local', 'testing']) && ! config('seo.force_canonical_redirect', false))
            || ! config('seo.canonical_redirect', true)
            || ! $request->isMethodSafe()
            || $request->is('.well-known/*')
        ) {
            return $next($request);
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $base = parse_url($baseUrl);

        if (! is_array($base) || empty($base['scheme']) || empty($base['host'])) {
            return $next($request);
        }

        $rawPath = parse_url($request->getRequestUri(), PHP_URL_PATH) ?: '/';
        $path = $rawPath === '/' ? '/' : rtrim($rawPath, '/');
        $path = $path !== '' ? $path : '/';
        $port = isset($base['port']) ? ':'.$base['port'] : '';
        $canonicalOrigin = $base['scheme'].'://'.$base['host'].$port;
        $currentOrigin = $request->getSchemeAndHttpHost();

        if ($currentOrigin === $canonicalOrigin && $path === $rawPath) {
            return $next($request);
        }

        $target = $canonicalOrigin.$path;

        if ($query = $request->getQueryString()) {
            $target .= '?'.$query;
        }

        return redirect()->away($target, 301);
    }
}
