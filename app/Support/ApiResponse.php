<?php

declare(strict_types=1);

namespace App\Support;

use App\Http\Middleware\NormalizeApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Centralised JSON response builder.
 *
 * Audit V3-2 found ~140 hand-rolled `response()->json([...])` calls
 * scattered across controllers, each with a slightly different shape:
 *
 *   { message, data }
 *   { message, order, status }
 *   { success, message, data }
 *   { error, code, payload }
 *   …
 *
 * Mobile clients parsing these have to special-case half a dozen
 * different envelopes. Worse, when a new field is added (request_id
 * for tracing, locale for i18n, …) it needs to be added in 140 places.
 *
 * Standard envelope produced by this helper:
 *
 *   {
 *     "success": true | false,
 *     "message": "Human-readable summary" | null,
 *     "data":    <Resource | array | null>,
 *     "errors":  null | array { field => [messages] }
 *   }
 *
 * All controller methods should return JsonResponse via these helpers,
 * not via response()->json([...]) directly. The shape becomes a
 * one-line change if we ever need to add request_id / version / etc.
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function badRequest(string $message, ?array $errors = null): JsonResponse
    {
        return self::error($message, Response::HTTP_BAD_REQUEST, $errors);
    }

    public static function conflict(string $message): JsonResponse
    {
        return self::error($message, Response::HTTP_CONFLICT);
    }

    /**
     * @param  JsonResource|ResourceCollection|array<int|string, mixed>|null  $data
     */
    public static function created(
        JsonResource|ResourceCollection|array|null $data = null,
        ?string $message = null,
    ): JsonResponse {
        return self::build(true, $data, $message, null, Response::HTTP_CREATED);
    }

    /**
     * @param  array<string, mixed>|null  $errors  Validation-style errors.
     */
    public static function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
    ): JsonResponse {
        return self::build(false, null, $message, $errors, $status);
    }

    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return self::error($message, Response::HTTP_FORBIDDEN);
    }

    public static function noContent(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * @param  JsonResource|ResourceCollection|array<int|string, mixed>|null  $data
     */
    public static function ok(
        JsonResource|ResourceCollection|array|null $data = null,
        ?string $message = null,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return self::build(true, $data, $message, null, $status);
    }

    /**
     * Centralised paginated response.
     *
     * Produces the standard envelope with the page items hoisted to `data`
     * and a *lean, canonical* `meta` block — instead of Laravel's verbose
     * paginated payload ({ data, links:{…}, meta:{ …, links:[ …per-page urls… ], path } }),
     * which ships a per-page URL array and an absolute path on every request.
     *
     *   {
     *     "success": true,
     *     "message": null,
     *     "data":    [ …items… ],
     *     "meta":    { current_page, per_page, total, last_page, from, to },
     *     "errors":  null
     *   }
     *
     * Both an explicit call here and the bare `Resource::collection($paginator)`
     * path (normalised by {@see NormalizeApiResponse})
     * converge on this exact shape, so pagination stays centralised.
     *
     * @param  LengthAwarePaginator<int, Model>  $paginator
     * @param  class-string<JsonResource>|null  $resource  Resource to map each item through.
     * @param  array<string, mixed>  $extraMeta  Domain-specific meta merged into the canonical pagination meta (e.g. nearby's delivery_zones / search_type).
     */
    public static function paginate(
        LengthAwarePaginator $paginator,
        ?string $resource = null,
        ?string $message = null,
        int $status = Response::HTTP_OK,
        array $extraMeta = [],
    ): JsonResponse {
        $items = $resource
            ? $resource::collection($paginator->items())
            : $paginator->items();

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => array_merge(self::paginationMeta($paginator), $extraMeta),
            'errors' => null,
        ], $status);
    }

    /**
     * Lean, canonical pagination meta extracted from a paginator instance.
     *
     * @param  LengthAwarePaginator<int, Model>  $paginator
     * @return array<string, int|null>
     */
    public static function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    /**
     * Lean, canonical pagination meta extracted from an already-serialized
     * paginated payload (used by {@see NormalizeApiResponse}
     * which only sees the response array, not the paginator object).
     *
     * Accepts both shapes:
     *   - resource collection: keys live under `meta` ({ current_page, … })
     *   - raw paginator:        keys live at the top level
     *
     * @param  array<string, mixed>  $source
     * @return array<string, int|null>
     */
    public static function paginationMetaFromArray(array $source): array
    {
        return [
            'current_page' => isset($source['current_page']) ? (int) $source['current_page'] : null,
            'per_page' => isset($source['per_page']) ? (int) $source['per_page'] : null,
            'total' => isset($source['total']) ? (int) $source['total'] : null,
            'last_page' => isset($source['last_page']) ? (int) $source['last_page'] : null,
            'from' => isset($source['from']) ? (int) $source['from'] : null,
            'to' => isset($source['to']) ? (int) $source['to'] : null,
        ];
    }

    public static function serverError(string $message = 'Server error.'): JsonResponse
    {
        return self::error($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function tooManyRequests(string $message = 'Too many requests.'): JsonResponse
    {
        return self::error($message, Response::HTTP_TOO_MANY_REQUESTS);
    }

    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return self::error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function unprocessable(string $message, ?array $errors = null): JsonResponse
    {
        return self::error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * @param  JsonResource|ResourceCollection|array<int|string, mixed>|null  $data
     * @param  array<string, mixed>|null  $errors
     */
    private static function build(
        bool $success,
        JsonResource|ResourceCollection|array|null $data,
        ?string $message,
        ?array $errors,
        int $status,
    ): JsonResponse {
        $payload = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ];

        return new JsonResponse($payload, $status);
    }
}
