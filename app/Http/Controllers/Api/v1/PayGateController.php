<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PayGateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @group Payments
 *
 * PayGate Global (FLOOZ / TMONEY) payment integration.
 */
final class PayGateController extends Controller
{
    public function __construct(private readonly PayGateService $payGate) {}

    /**
     * Initiate a mobile-money payment
     *
     * Creates a pending payment for an order and asks PayGate to push a payment
     * prompt to the customer's phone.
     *
     * @bodyParam order_id string required The order to pay. Example: 01HXE...
     * @bodyParam phone_number string required Customer mobile number. Example: 92000000
     * @bodyParam network string required FLOOZ or TMONEY. Example: FLOOZ
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string', 'exists:orders,id'],
            'phone_number' => ['required', 'string', 'max:20'],
            'network' => ['required', 'string', 'in:FLOOZ,TMONEY'],
        ]);

        /** @var Order $order */
        $order = Order::findOrFail($validated['order_id']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => PaymentMethod::from(mb_strtolower($validated['network'])),
            // PayGate returns the reference only after the initiation call.
            'transaction_reference' => '',
            'status' => PaymentStatus::NON_PAYE,
        ]);

        $result = $this->payGate->initiate([
            'phone_number' => $validated['phone_number'],
            'amount' => (float) $order->total_amount,
            'identifier' => $payment->id,
            'network' => $validated['network'],
            'description' => 'Commande '.$order->order_number,
        ]);

        if (isset($result['error'])) {
            return $this->error($result['error'], 502);
        }

        if (($result['status'] ?? null) !== PayGateService::PAY_SUCCESS) {
            return $this->error(
                $this->payInitError((int) ($result['status'] ?? -1)),
                422,
            );
        }

        $payment->update(['transaction_reference' => $result['tx_reference'] ?? null]);

        return $this->success([
            'paymentId' => $payment->id,
            'txReference' => $result['tx_reference'] ?? null,
            'status' => 'pending',
        ], 'Paiement initié. Le client doit valider sur son téléphone.');
    }

    /**
     * Payment confirmation callback (PayGate webhook)
     *
     * Public endpoint PayGate posts to once the customer has paid. We re-verify
     * the status against PayGate before trusting the payload, then mark the
     * payment and order as paid.
     */
    public function callback(Request $request): JsonResponse
    {
        // Optional shared-secret gate (in addition to re-verifying with PayGate
        // below). Enabled only when PAYGATE_WEBHOOK_SECRET is set.
        $secret = (string) config('services.paygate.webhook_secret');
        if ($secret !== '') {
            $provided = (string) ($request->input('secret') ?? $request->header('X-Webhook-Secret', ''));
            if (! hash_equals($secret, $provided)) {
                return response()->json(['message' => 'Invalid secret.'], 403);
            }
        }

        $identifier = (string) $request->input('identifier');
        $txReference = (string) $request->input('tx_reference');

        Log::info('PayGate callback received', [
            'identifier' => $identifier,
            'tx_reference' => $txReference,
        ]);

        $payment = Payment::query()
            ->where('id', $identifier)
            ->orWhere('transaction_reference', $txReference)
            ->first();

        if ($payment === null) {
            return response()->json(['message' => 'Unknown payment.'], 200);
        }

        // Anti-spoofing: confirm with PayGate directly instead of trusting the
        // posted body.
        $status = $txReference !== ''
            ? $this->payGate->status($txReference)
            : $this->payGate->statusByIdentifier($payment->id);

        if ((int) ($status['status'] ?? -1) === PayGateService::PAYMENT_SUCCESS) {
            $this->markPaid($payment, $request->input('datetime'));
        }

        return response()->json(['message' => 'ok'], 200);
    }

    /**
     * Check / refresh a payment status
     *
     * @urlParam payment string required The payment ID (ULID).
     */
    public function status(Payment $payment): JsonResponse
    {
        $status = $payment->transaction_reference
            ? $this->payGate->status($payment->transaction_reference)
            : $this->payGate->statusByIdentifier($payment->id);

        if (isset($status['error'])) {
            return $this->error($status['error'], 502);
        }

        $code = (int) ($status['status'] ?? -1);

        if ($code === PayGateService::PAYMENT_SUCCESS) {
            $this->markPaid($payment, $status['datetime'] ?? null);
        }

        return $this->success([
            'payment' => new PaymentResource($payment->fresh()->load('order')),
            'paygateStatus' => $code,
            'paygateStatusLabel' => $this->paymentStatusLabel($code),
        ]);
    }

    /**
     * Merchant balance
     *
     * Returns the FLOOZ / TMoney balance (the server IP must be whitelisted by
     * PayGate).
     */
    public function balance(): JsonResponse
    {
        return $this->success($this->payGate->balance());
    }

    private function markPaid(Payment $payment, ?string $datetime): void
    {
        if ($payment->status === PaymentStatus::PAYE) {
            return;
        }

        $paidAt = $datetime !== null && $datetime !== '' ? $datetime : now();

        $payment->update([
            'status' => PaymentStatus::PAYE,
            'paid_at' => $paidAt,
        ]);

        $payment->order?->update([
            'status' => OrderStatus::PAID,
            'paid_at' => $paidAt,
        ]);

        // Silent real-time refresh for the back-office lists.
        \App\Events\ResourceChangedEvent::dispatch('payments', 'paid', $payment->id);
        \App\Events\ResourceChangedEvent::dispatch('orders', 'paid', $payment->order_id);
    }

    private function payInitError(int $code): string
    {
        return match ($code) {
            PayGateService::PAY_INVALID_TOKEN => "Jeton d'authentification PayGate invalide.",
            PayGateService::PAY_INVALID_PARAMS => 'Paramètres de paiement invalides.',
            PayGateService::PAY_DUPLICATE => 'Une transaction avec le même identifiant existe déjà.',
            default => "Échec de l'initiation du paiement.",
        };
    }

    private function paymentStatusLabel(int $code): string
    {
        return match ($code) {
            PayGateService::PAYMENT_SUCCESS => 'Payé',
            PayGateService::PAYMENT_PENDING => 'En cours',
            PayGateService::PAYMENT_EXPIRED => 'Expiré',
            PayGateService::PAYMENT_CANCELLED => 'Annulé',
            default => 'Inconnu',
        };
    }
}
