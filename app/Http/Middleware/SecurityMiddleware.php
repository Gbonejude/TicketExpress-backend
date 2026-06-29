<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Attach security headers to every outgoing response.
 *
 * SECURITY V4-1 — The previous incarnation of this class tried to do
 * everything in one middleware: SQL-injection regex matching, XSS regex
 * matching, request-size enforcement, in-process rate limiting, input
 * sanitisation, *and* security headers. None of it worked correctly:
 *
 *   * addSecurityHeaders() referenced a `$next($request)` that wasn't
 *     in scope — PHP raised an undefined-variable warning and the
 *     headers were never set (audit P1-13). The middleware was also
 *     never registered in MiddlewareBootstrapper, so even the bug above
 *     never had a chance to misfire.
 *   * The SQL-injection detector matched on any French sentence
 *     containing the word "select" / "update" — false-positive rate
 *     was off the charts, and real injection attempts go through
 *     Eloquent's prepared statements anyway.
 *   * The XSS detector matched 80+ regexes on json_encode(request->all),
 *     which broke every legitimate restaurant comment containing words
 *     like "onerror" or "javascript".
 *   * The home-grown rate limiter used cache()->put($k, $current+1)
 *     non-atomically — the V4-3 work uses Laravel's RateLimiter for that.
 *
 * The rewritten version does exactly one thing — add security response
 * headers — and is registered in MiddlewareBootstrapper to actually run.
 *
 * Header choices:
 *   * Strict-Transport-Security with preload eligible value: HTTPS is
 *     enforced at the Apache vhost; this tells browsers to remember.
 *   * Content-Security-Policy is set in API-friendly mode (no script
 *     sources, no inline content allowed). For the dashboard SPA served
 *     out of /build the Vite assets are same-origin so 'self' suffices.
 *   * X-Content-Type-Options: nosniff — stops MIME confusion.
 *   * X-Frame-Options: DENY — no embedding the API or dashboard pages.
 *   * Referrer-Policy: strict-origin-when-cross-origin — cookies and
 *     paths stay on our domain.
 *   * Permissions-Policy: lock down browser APIs we never use.
 *   * X-XSS-Protection is deliberately left OUT — the header is
 *     deprecated and Chrome dropped it; CSP is the modern replacement.
 *
 * APP_DEBUG=true keeps Laravel's debug pages working in dev (Ignition
 * needs inline scripts), so we relax the CSP in non-production envs.
 */
final class SecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // ResponseHeaderBag is a public PROPERTY (not a method) on every
        // Symfony Response subclass. A previous version of this check used
        // method_exists($response, 'headers') and skipped every response —
        // headers were never sent.
        if (! isset($response->headers)) {
            return $response;
        }

        $isApi = $request->is('api/*') || $request->is('webhooks/*');

        // Internal admin dashboards (Pulse/Telescope) render via Livewire +
        // Alpine, which require inline + eval scripts — incompatible with the
        // strict script-src 'self' applied to the public SPA. They are
        // admin-only (gated), so a relaxed CSP scoped to them is acceptable
        $isDashboard = $request->is('pulse') || $request->is('pulse/*')
            || $request->is('telescope') || $request->is('telescope/*');

        $isProd = config('app.env') === 'production';

        // Headers safe for every response.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(self), payment=()',
        );

        // HSTS only over HTTPS — never set on plain HTTP to avoid bricking
        // a fallback dev environment.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=63072000; includeSubDomains; preload',
            );
        }

        // CSP. API responses are JSON; default-src 'none' is the strictest
        // safe option. The dashboard/landing pages need to load same-origin
        // scripts and styles produced by Vite.
        if ($isApi) {
            $csp = "default-src 'none'; frame-ancestors 'none'; base-uri 'none'";
        } elseif ($isDashboard) {
            // Pulse/Telescope: Livewire/Alpine need inline + eval scripts, and
            // the dashboards pull their font from the Bunny CDN.
            $csp = "default-src 'self'; img-src 'self' data: https:; ".
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; ".
                "font-src 'self' https://fonts.bunny.net data:; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
                "frame-ancestors 'none'; base-uri 'self'";
        } else {
            // First-party admin SPA (Vue 3 + Vite + Pusher + Google Fonts).
            // `script-src 'self'` alone breaks Vite (its inline module-preload
            // polyfill is blocked, cascading into a load loop), and the missing
            // connect-src blocks the API + Pusher WebSocket. Relax accordingly —
            // this is our own trusted bundle; auth, not CSP, is the control here.
            $csp = "default-src 'self'; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
                "font-src 'self' https://fonts.gstatic.com data:; ".
                "img-src 'self' data: blob: https:; ".
                "connect-src 'self' https: wss:; ".
                "frame-ancestors 'none'; base-uri 'self'";
        }

        if (! $isProd) {
            // Dev: keep Ignition / Tinker / Telescope inline scripts working.
            $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: *;";
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
