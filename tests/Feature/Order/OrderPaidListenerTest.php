<?php

declare(strict_types=1);

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Events\Order\OrderPaidEvent;
use App\Events\Ticket\TicketIssuedEvent;
use App\Jobs\SendEmailJob;
use App\Listeners\Order\OrderPaidListener;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketDownloadLink;
use App\Models\TicketType;
use App\Support\TicketNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * L'émission des billets (app/Listeners/Order/OrderPaidListener.php).
 *
 * C'est le seul endroit du code qui crée des billets : un paiement accepté
 * entre ici, et il en sort autant de billets que de places achetées, un lien
 * de téléchargement, et l'envoi que le client a choisi.
 *
 * Pourquoi ces tests : `handle()` enferme tout dans un `try/catch` qui se
 * contente de journaliser. Une régression n'y lève donc rien — elle produit
 * **zéro billet en silence**, sur une commande qui reste marquée payée et un
 * client qui ne reçoit rien. Une colonne passée `NOT NULL` par mégarde suffit.
 * Le premier test surveille pour cette raison le journal autant que la base.
 *
 * Les jobs sont interceptés (`Queue::fake`) : sinon la file `sync` des tests
 * exécuterait l'envoi, qui rend le PDF du billet — dix secondes par commande.
 */
beforeEach(function (): void {
    Queue::fake();
});

/**
 * Une commande payée dont les billets ne sont pas encore émis.
 *
 * Le titre de l'événement est fixé : c'est lui qui donne le préfixe des
 * numéros (« Afrobeats… » → AFRO), et les tests le citent.
 */
function paidOrderAwaitingTickets(
    int $quantity = 1,
    DeliveryMethod $delivery = DeliveryMethod::EMAIL,
    string $eventTitle = 'Afrobeats Sunset Live',
): Order {
    $event = Event::factory()->create(['title' => $eventTitle]);
    $ticketType = TicketType::factory()->for($event)->create(['name' => 'Standard', 'price' => 5000]);

    $order = Order::factory()->create([
        'status' => OrderStatus::PAID,
        'delivery_method' => $delivery,
        'first_name' => 'Komi',
        'last_name' => 'Creppy',
        'email' => 'komi.creppy@example.tg',
        'total_amount' => 5000 * $quantity,
    ]);

    OrderItem::factory()->for($order)->for($ticketType)->create([
        'quantity' => $quantity,
        'unit_price' => 5000,
        'subtotal' => 5000 * $quantity,
    ]);

    return $order;
}

function runOrderPaidListener(Order $order): void
{
    (new OrderPaidListener())->handle(new OrderPaidEvent($order));
}

it('issues one ticket per seat bought', function (): void {
    $order = paidOrderAwaitingTickets(quantity: 3);

    Log::spy();

    runOrderPaidListener($order);

    // Trois places achetées, trois billets : le billet est au porteur, l'acheteur
    // les distribue ensuite comme il veut.
    expect(Ticket::query()->where('order_id', $order->id)->count())->toBe(3);

    // `handle()` avale ses exceptions : sans cette assertion, une insertion
    // refusée passerait pour un simple « 0 billet ».
    Log::shouldNotHaveReceived('error');
});

it('names the buyer on every ticket', function (): void {
    $order = paidOrderAwaitingTickets(quantity: 2);

    runOrderPaidListener($order);

    $tickets = Ticket::query()->where('order_id', $order->id)->get();

    expect($tickets)->toHaveCount(2)
        ->and($tickets->pluck('attendee_name')->unique()->all())->toBe(['Komi Creppy'])
        ->and($tickets->pluck('attendee_email')->unique()->all())->toBe(['komi.creppy@example.tg']);
});

it('numbers the tickets in sequence, prefixed by the event', function (): void {
    $order = paidOrderAwaitingTickets(quantity: 3);
    $year = now()->year;

    runOrderPaidListener($order);

    expect(Ticket::query()->where('order_id', $order->id)->orderBy('ticket_number')->pluck('ticket_number')->all())
        ->toBe([
            "AFRO-{$year}-0001",
            "AFRO-{$year}-0002",
            "AFRO-{$year}-0003",
        ]);
});

it('continues the sequence past the numbers already taken', function (): void {
    $order = paidOrderAwaitingTickets();
    $year = now()->year;

    // Un billet du même préfixe existe déjà : la séquence est commune au
    // préfixe, pas à l'événement, et deux titres peuvent le partager.
    Ticket::factory()
        ->for($order)
        ->for(TicketType::factory()->for(Event::factory())->create())
        ->create(['ticket_number' => TicketNumber::format('AFRO', $year, 1)]);

    runOrderPaidListener($order);

    expect(Ticket::query()->where('ticket_number', "AFRO-{$year}-0002")->exists())->toBeTrue();
});

it('gives every ticket an unpredictable qr payload', function (): void {
    $order = paidOrderAwaitingTickets(quantity: 3);

    runOrderPaidListener($order);

    $codes = Ticket::query()->where('order_id', $order->id)->pluck('qr_code');

    expect($codes->unique())->toHaveCount(3);

    foreach ($codes as $code) {
        expect(mb_strlen($code))->toBe(40);
    }

    // Le QR est le seul secret du billet : le numéro imprimé, lui, est
    // séquentiel et donc devinable. `uniqid()` dérivait de l'horloge — deux
    // billets émis dans la même seconde partageaient tout sauf le dernier
    // caractère, et détenir un billet suffisait à deviner celui du voisin.
    expect($codes->map(fn (string $code): string => mb_substr($code, 0, 10))->unique())->toHaveCount(3);
});

it('stamps the order as paid', function (): void {
    $order = paidOrderAwaitingTickets();

    runOrderPaidListener($order);

    expect($order->fresh()->paid_at)->not->toBeNull();
});

it('opens a download link that outlives the event', function (): void {
    $order = paidOrderAwaitingTickets();
    $event = $order->items->first()->ticketType->event;

    runOrderPaidListener($order);

    $link = TicketDownloadLink::query()->where('order_id', $order->id)->first();

    // Le lien survit à l'événement : on récupère son billet après coup, pour
    // une note de frais ou un litige.
    expect($link)->not->toBeNull()
        ->and($link->token)->not->toBeEmpty()
        ->and($link->expires_at->isAfter($event->end_date))->toBeTrue();
});

it('emails the tickets when the buyer chose email', function (): void {
    Illuminate\Support\Facades\Event::fake([TicketIssuedEvent::class]);

    runOrderPaidListener(paidOrderAwaitingTickets(delivery: DeliveryMethod::EMAIL));

    Queue::assertPushed(SendEmailJob::class, 1);
});

it('sends no email when the buyer chose whatsapp', function (): void {
    Illuminate\Support\Facades\Event::fake([TicketIssuedEvent::class]);

    // Livraison WhatsApp : le lien est produit et journalisé, l'email ne part
    // pas. Un client qui a donné un numéro et pas d'adresse n'est pas relancé
    // sur une adresse de circonstance.
    runOrderPaidListener(paidOrderAwaitingTickets(delivery: DeliveryMethod::WHATSAPP));

    Queue::assertNotPushed(SendEmailJob::class);
});

it('announces every issued ticket', function (): void {
    Illuminate\Support\Facades\Event::fake([TicketIssuedEvent::class]);

    runOrderPaidListener(paidOrderAwaitingTickets(quantity: 2));

    Illuminate\Support\Facades\Event::assertDispatchedTimes(TicketIssuedEvent::class, 2);
});
