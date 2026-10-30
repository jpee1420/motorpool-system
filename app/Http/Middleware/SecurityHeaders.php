<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $contentSecurityPolicy = $this->buildContentSecurityPolicy();

        if ($request->isSecure() || app()->environment('production')) {
            $contentSecurityPolicy .= " upgrade-insecure-requests;";
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Content-Security-Policy', $contentSecurityPolicy);

        return $response;
    }

    private function buildContentSecurityPolicy(): string
    {
        $devSources = $this->allowsViteDevServer()
            ? ' http://127.0.0.1:5173 ws://127.0.0.1:5173'
            : '';

        return "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:{$devSources}; style-src 'self' 'unsafe-inline' https://fonts.bunny.net{$devSources}; img-src 'self' data: blob:{$devSources}; connect-src 'self'{$devSources}; font-src 'self' data: https://fonts.bunny.net; worker-src 'self' blob: data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self';";
    }

    private function allowsViteDevServer(): bool
    {
        $appUrl = config('app.url', '');

        return str_contains($appUrl, 'localhost')
            || str_contains($appUrl, '127.0.0.1')
            || str_contains($appUrl, '0.0.0.0')
            || str_contains($appUrl, '[::1]');
    }
}
