<?php

declare(strict_types=1);

use App\Actions\V1\Payment\MarkPaymentPaidAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\Order\OrderPaidEvent;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PayGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/** Stub the PayGate status endpoints (v1 by tx_reference, v2 by identifier). */
function fakePayGateStatus(int $code): void
{
    config(['services.paygate.base_url' => 'https://paygate.test']);

    $body = ['status' => $code, 'datetime' => now()->toDateTimeString()];

    Http::fake([
        '*/api/v1/status' => Http::response($body),
        '*/api/v2/status' => Http::response($body),
    ]);
}

function pendingPayment(OrderStatus $orderStatus = OrderStatus::PENDING): Payment
{
    $order = Order::factory()->create(['status' => $orderStatus]);

    return Payment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::NON_PAYE,
        'paid_at' => null,
    ]);
}

it('marks payment and order paid and dispatches OrderPaidEvent', function (): void {
    Event::fake([OrderPaidEvent::class]);
    $payment = pendingPayment();

    app(MarkPaymentPaidAction::class)->execute(['payment' => $payment]);

    expect($payment->fresh()->status)->toBe(PaymentStatus::PAYE)
        ->and($payment->order->fresh()->status)->toBe(OrderStatus::PAID);
    Event::assertDispatched(OrderPaidEvent::class);
});

it('records payment on a cancelled order without resurrecting it or issuing tickets', function (): void {
    Event::fake([OrderPaidEvent::class]);
    $payment = pendingPayment(OrderStatus::CANCELLED);

    app(MarkPaymentPaidAction::class)->execute(['payment' => $payment]);

    expect($payment->fresh()->status)->toBe(PaymentStatus::PAYE)
        ->and($payment->order->fresh()->status)->toBe(OrderStatus::CANCELLED);
    Event::assertNotDispatched(OrderPaidEvent::class);
});

it('is idempotent for an already-paid payment', function (): void {
    Event::fake([OrderPaidEvent::class]);
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);
    $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => PaymentStatus::PAYE]);

    app(MarkPaymentPaidAction::class)->execute(['payment' => $payment]);

    Event::assertNotDispatched(OrderPaidEvent::class);
});

it('reconciles a pending payment that PayGate reports as paid', function (): void {
    Event::fake([OrderPaidEvent::class]);
    $payment = pendingPayment();
    fakePayGateStatus(PayGateService::PAYMENT_SUCCESS);

    $this->artisan('payments:reconcile-pending')->assertExitCode(0);

    expect($payment->fresh()->status)->toBe(PaymentStatus::PAYE)
        ->and($payment->order->fresh()->status)->toBe(OrderStatus::PAID);
    Event::assertDispatched(OrderPaidEvent::class);
});

it('leaves a still-pending payment untouched during reconciliation', function (): void {
    $payment = pendingPayment();
    fakePayGateStatus(PayGateService::PAYMENT_PENDING);

    $this->artisan('payments:reconcile-pending')->assertExitCode(0);

    expect($payment->fresh()->status)->toBe(PaymentStatus::NON_PAYE)
        ->and($payment->order->fresh()->status)->toBe(OrderStatus::PENDING);
});
