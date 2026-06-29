<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class Localize
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve to a SUPPORTED locale via content negotiation. Passing the
        // raw Accept-Language header (e.g. "en_US,en;q=0.5") straight to
        // setLocale() throws under Symfony Translator 7 / Carbon 3, whose
        // assertValidLocale() rejects the "," / ";" / "=" characters
        // (tolerated on Laravel 11). getPreferredLanguage() parses the
        // header and always returns one of the listed locales.
        // French first: it is the primary market (Togo). When the client sends
        // no (or an unmatched) Accept-Language, getPreferredLanguage returns the
        // first listed locale, so responses default to French instead of English.
        $locale = $request->getPreferredLanguage(['fr', 'en']);

        app()->setLocale($locale);

        return $next($request);
    }
}
