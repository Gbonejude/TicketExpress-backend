<?php

declare(strict_types=1);

namespace App\Services\Payment;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the PayGate Global mobile-money API (FLOOZ / TMONEY).
 *
 * @see https://www.paygateglobal.com/guide
 */
final class PayGateService
{
    /** Status returned by the "pay" endpoint. */
    public const PAY_SUCCESS = 0;

    public const PAY_INVALID_TOKEN = 2;

    public const PAY_INVALID_PARAMS = 4;

    public const PAY_DUPLICATE = 6;

    /** Status returned by the "status" endpoint. */
    public const PAYMENT_SUCCESS = 0;

    public const PAYMENT_PENDING = 2;

    public const PAYMENT_EXPIRED = 4;

    public const PAYMENT_CANCELLED = 6;

    private string $apiKey;

    private string $baseUrl;

    private int $timeout;

    private bool $verifySsl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.paygate.api_key');
        $this->baseUrl = rtrim((string) config('services.paygate.base_url'), '/');
        $this->timeout = (int) config('services.paygate.timeout', 30);
        $this->verifySsl = (bool) config('services.paygate.verify_ssl', true);
    }

    /**
     * A pre-configured HTTP client (timeout + SSL verification per config).
     */
    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::asJson()->acceptJson()->timeout($this->timeout);

        return $this->verifySsl ? $request : $request->withoutVerifying();
    }

    /**
     * Initiate a payment (Method 1). PayGate pushes a payment prompt to the
     * customer's phone.
     *
     * @param  array{phone_number: string, amount: int|float, identifier: string, network: string, description?: string}  $data
     * @return array{tx_reference?: string, status?: int, error?: string}
     */
    public function initiate(array $data): array
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/api/v1/pay", [
                    'auth_token' => $this->apiKey,
                    'phone_number' => $data['phone_number'],
                    'amount' => $data['amount'],
                    'description' => $data['description'] ?? null,
                    'identifier' => $data['identifier'],
                    'network' => $data['network'],
                ]);
        } catch (ConnectionException $e) {
            return ['error' => 'Service de paiement injoignable : '.$e->getMessage()];
        }

        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        return [
            'tx_reference' => $body['tx_reference'] ?? null,
            'status' => isset($body['status']) ? (int) $body['status'] : null,
        ];
    }

    /**
     * Build the hosted payment-page URL (Method 2) the customer is redirected
     * to.
     *
     * @param  array{amount: int|float, identifier: string, description?: string, url?: string, phone?: string, network?: string}  $data
     */
    public function paymentPageUrl(array $data): string
    {
        $query = array_filter([
            'token' => $this->apiKey,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'identifier' => $data['identifier'],
            'url' => $data['url'] ?? null,
            'phone' => $data['phone'] ?? null,
            'network' => $data['network'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');

        return "{$this->baseUrl}/v1/page?".http_build_query($query);
    }

    /**
     * Check a payment's status by PayGate's transaction reference.
     *
     * @return array<string, mixed>
     */
    public function status(string $txReference): array
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/api/v1/status", [
                    'auth_token' => $this->apiKey,
                    'tx_reference' => $txReference,
                ]);
        } catch (ConnectionException $e) {
            return ['error' => $e->getMessage()];
        }

        return $response->json() ?? [];
    }

    /**
     * Check a payment's status by the merchant's own identifier (e.g. order
     * number).
     *
     * @return array<string, mixed>
     */
    public function statusByIdentifier(string $identifier): array
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/api/v2/status", [
                    'auth_token' => $this->apiKey,
                    'identifier' => $identifier,
                ]);
        } catch (ConnectionException $e) {
            return ['error' => $e->getMessage()];
        }

        return $response->json() ?? [];
    }

    /**
     * Retrieve the merchant's FLOOZ / TMoney balance (IP must be whitelisted).
     *
     * @return array<string, mixed>
     */
    public function balance(): array
    {
        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/api/v1/check-balance", [
                    'auth_token' => $this->apiKey,
                ]);
        } catch (ConnectionException $e) {
            return ['error' => $e->getMessage()];
        }

        return $response->json() ?? [];
    }
}
