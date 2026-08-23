<?php

declare(strict_types=1);

use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Venue;

/**
 * Le contenu du reçu et des billets (resources/views/pdfs/ticket.blade.php).
 *
 * Le rendu est vérifié en HTML plutôt qu'en PDF : c'est le même gabarit, alors
 * que produire le PDF coûte une dizaine de secondes par commande (les QR codes,
 * l'embarquement des polices). Ce qui est testé ici est ce que la version
 * précédente se trompait à afficher — un événement sans nom, un numéro de
 * commande qui était un ULID, le tarif du jour au lieu du prix payé.
 */
function renderTicketPdfView(Order $order): string
{
    return view('pdfs.ticket', ['order' => $order->load([
        'tickets.ticketType.event.venue',
        'tickets.ticketType.event.organizer',
        'tickets.ticketType.occurrence',
        'items.ticketType.event',
        'payments',
    ])])->render();
}

/**
 * Une commande payée d'un billet, sur un événement physique.
 *
 * @param  array<string, mixed>  $ticketTypeAttributes
 * @param  array<string, mixed>  $ticketAttributes
 */
function paidOrderWithOneTicket(
    array $ticketTypeAttributes = [],
    array $ticketAttributes = [],
    float $unitPricePaid = 3750,
): Order {
    $venue = Venue::factory()->create([
        'name' => 'Stade de Kégué',
        'address' => 'Route de Kpalimé',
        'city' => 'Lomé',
    ]);

    $event = Event::factory()->create([
        'title' => 'Afrobeats Sunset Live',
        'venue_id' => $venue->id,
        'event_type' => EventType::PHYSICAL,
    ]);

    $ticketType = TicketType::factory()->for($event)->create([
        'name' => 'Standard',
        'price' => 5000,
        ...$ticketTypeAttributes,
    ]);

    $order = Order::factory()->create([
        'status' => OrderStatus::PAID,
        'payment_method' => PaymentMethod::FLOOZ->value,
        'total_amount' => $unitPricePaid,
        'first_name' => 'Komi',
        'last_name' => 'Creppy',
    ]);

    OrderItem::factory()->for($order)->for($ticketType)->create([
        'quantity' => 1,
        'unit_price' => $unitPricePaid,
        'subtotal' => $unitPricePaid,
    ]);

    Ticket::factory()->for($order)->for($ticketType)->create([
        'attendee_name' => 'Ama Sossou',
        ...$ticketAttributes,
    ]);

    return $order;
}

it('names the event on every ticket', function (): void {
    $html = renderTicketPdfView(paidOrderWithOneTicket());

    // La version précédente lisait `$event->name`, une colonne qui n'existe
    // pas : le billet sortait sans le nom de l'événement.
    expect($html)->toContain('Afrobeats Sunset Live');
});

it('locates a physical event by its venue', function (): void {
    $html = renderTicketPdfView(paidOrderWithOneTicket());

    // `$event->location` n'existe pas non plus : le lieu est porté par la salle.
    expect($html)
        ->toContain('Stade de Kégué')
        ->toContain('Route de Kpalimé')
        ->toContain('Lomé');
});

it('gives the online address instead of a venue for an online event', function (): void {
    $order = paidOrderWithOneTicket();
    $event = $order->tickets->first()->ticketType->event;
    $event->update([
        'event_type' => EventType::ONLINE,
        'online_url' => 'https://live.ticketexpress.tg/abcd',
    ]);

    $html = renderTicketPdfView($order->fresh());

    expect($html)
        ->toContain('https://live.ticketexpress.tg/abcd')
        ->toContain('En ligne');
});

it('shows the order number rather than its identifier', function (): void {
    $order = paidOrderWithOneTicket();

    $html = renderTicketPdfView($order);

    expect($html)
        ->toContain((string) $order->order_number)
        ->not->toContain($order->id);
});

it('bills the price actually paid, not the current list price', function (): void {
    // Billet acheté 3 750 en promotion, alors que le type affiche 5 000
    // aujourd'hui. Un reçu dit ce qui a été payé.
    $html = renderTicketPdfView(paidOrderWithOneTicket(unitPricePaid: 3750));

    expect($html)
        ->toContain('3 750 FCFA')
        ->not->toContain('5 000 FCFA');
});

it('reports the payment that went through', function (): void {
    $order = paidOrderWithOneTicket();
    Payment::factory()->for($order)->create([
        'method' => PaymentMethod::TMONEY,
        'status' => PaymentStatus::PAYE,
        'transaction_reference' => 'TXN-1796-DJJD-0367',
    ]);

    $html = renderTicketPdfView($order->fresh());

    expect($html)
        ->toContain('TXN-1796-DJJD-0367')
        ->toContain(PaymentMethod::TMONEY->label());
});

it('warns that a refunded ticket no longer opens the gate', function (): void {
    $order = paidOrderWithOneTicket(ticketAttributes: [
        'status' => TicketStatus::REFUNDED,
    ]);

    $html = renderTicketPdfView($order);

    expect($html)->toContain('Billet remboursé');
});

it('leaves out the pictograms the PDF font cannot draw', function (): void {
    // Les avantages d'un type de billet contiennent des emoji, absents de
    // DejaVu Sans : dompdf les dessinait en carrés vides.
    $html = renderTicketPdfView(paidOrderWithOneTicket(ticketTypeAttributes: [
        'benefits' => ['🎫 Entrée générale', '🥤 Boisson de bienvenue'],
    ]));

    expect($html)
        ->toContain('Entrée générale')
        ->toContain('Boisson de bienvenue')
        ->not->toContain('🎫')
        ->not->toContain('🥤');
});

it('says so when an order carries no ticket yet', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatus::PENDING,
        'total_amount' => 6000,
    ]);

    $html = renderTicketPdfView($order);

    expect($html)
        ->toContain('Cette commande est en attente de paiement.')
        ->toContain('Total à payer')
        ->toContain("Aucun billet n'est rattaché à cette commande");
});

it('gives each ticket its own page', function (): void {
    $order = paidOrderWithOneTicket();
    $ticketType = $order->tickets->first()->ticketType;
    Ticket::factory()->for($order)->for($ticketType)->count(2)->create();

    $html = renderTicketPdfView($order->fresh());

    // Un reçu, puis un billet par page.
    expect(substr_count($html, 'class="sheet"'))->toBe(4);
});
