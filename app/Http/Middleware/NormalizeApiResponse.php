<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safety net that guarantees EVERY API JSON response shares the standard
 * envelope produced by {@see ApiResponse}:
 *
 *   { "success": bool, "message": string|null, "data": mixed, "errors": mixed }
 *
 * Controllers SHOULD return through ApiResponse explicitly (see its README).
 * Some legacy controllers still return bare resources / collections
 * (`Resource::collection(...)`), which — with JsonResource::withoutWrapping()
 * active — serialize to a top-level array with no envelope. This middleware
 * wraps any such not-yet-enveloped JSON response so the contract holds
 * everywhere, including for endpoints added in the future.
 *
 * Already-enveloped responses (ApiResponse success builders and the
 * ExceptionsBootstrapper error renderers) carry a `success` key and are left
 * untouched — no double wrapping.
 *
 * Two wrapping shapes, so that the payload always lives under `data`:
 *   - Plain resource / list / object  → nested:  { success, message, data: <payload>, errors }
 *   - A paginated payload (resource collection { data, links, meta } OR a raw
 *     paginator { current_page, data, … }) → normalised to the *lean, canonical*
 *     pagination envelope, dropping Laravel's verbose per-page `links` array and
 *     absolute `path`, so EVERY paginated endpoint returns the same shape:
 *     { success, message, data: [...], meta: { current_page, per_page, total,
 *       last_page, from, to }, errors }
 *     This is the single point where pagination is centralised; controllers can
 *     keep returning `Resource::collection($paginator)` and still land here, or
 *     call {@see ApiResponse::paginate()} for the identical result.
 */
final class NormalizeApiResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only normalize JSON responses; leave files, streams, redirects,
        // and SPA/HTML responses alone.
        if (! $response instanceof JsonResponse) {
            return $response;
        }

        // 204 No Content carries no body to wrap.
        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
            return $response;
        }

        /** @var mixed $payload */
        $payload = $response->getData(true);

        // Already in the standard envelope (ApiResponse / exception renderers).
        if (is_array($payload) && array_key_exists('success', $payload)) {
            return $response;
        }

        $success = $response->getStatusCode() < Response::HTTP_BAD_REQUEST;

        // Paginated payload — normalise to the lean, canonical envelope. Two
        // serialized shapes reach us:
        //   - resource collection: { data, links, meta: { current_page, … } }
        //   - raw paginator:        { current_page, data, …flat keys… }
        if (is_array($payload) && array_key_exists('data', $payload) && ! is_int(array_key_first($payload))) {
            $metaSource = isset($payload['meta']) && is_array($payload['meta'])
                ? $payload['meta']
                : $payload;

            if (array_key_exists('current_page', $metaSource)) {
                // Lean canonical pagination keys, PLUS any domain-specific meta
                // an endpoint attached via ->additional(['meta' => …]) (e.g.
                // nearby restaurants ship delivery_zones / search_type / cached).
                // Only Laravel's verbose paginator extras are dropped.
                $extra = array_diff_key($metaSource, array_flip([
                    'current_page', 'per_page', 'total', 'last_page', 'from', 'to',
                    'first_page_url', 'last_page_url', 'next_page_url', 'prev_page_url',
                    'links', 'path',
                ]));

                $response->setData([
                    'success' => $success,
                    'message' => null,
                    'data' => $payload['data'],
                    'meta' => array_merge(
                        ApiResponse::paginationMetaFromArray($metaSource),
                        $extra
                    ),
                    'errors' => null,
                ]);

                return $response;
            }

            // Non-paginated structured payload that already carries `data` —
            // merge its keys rather than nesting `data` inside `data`
            $response->setData(array_merge(
                ['success' => $success, 'message' => null],
                $payload,
                ['errors' => null],
            ));

            return $response;
        }

        $response->setData([
            'success' => $success,
            'message' => null,
            'data' => $payload,
            'errors' => null,
        ]);

        return $response;
    }
}
