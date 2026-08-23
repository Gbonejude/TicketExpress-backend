@php
    /**
     * Le reçu d'achat d'une commande, suivi de ses billets — un par page
     * (dompdf, A4 portrait).
     *
     * Ce document sort par deux chemins qui doivent montrer la même chose : la
     * pièce jointe du mail de confirmation (TicketPurchaseConfirmationMail) et
     * le lien de téléchargement (TicketDownloadController::downloadPdf). Il ne
     * reçoit que la commande ; tout le reste se lit à travers ses relations.
     *
     * Contraintes de dompdf, qui expliquent la mise en page :
     *
     *  - ni flex ni grid : les colonnes sont des `<table>` ;
     *  - pas de variables CSS : les couleurs de la marque sont écrites en dur,
     *    reprises du `tokens.css` du site public (rouge #b9000a / #e31919) ;
     *  - `@page { margin: 0 }` : c'est ce qui permet le bandeau rouge à fond
     *    perdu en haut de chaque page, d'où le rembourrage porté par `.sheet` ;
     *  - un élément `position: fixed` est redessiné sur chaque page : c'est le
     *    pied, et `counter(page)` y donne la pagination ;
     *  - la seule police embarquée est DejaVu Sans, qui n'a AUCUN emoji. Ceux
     *    de la version précédente sortaient en carrés vides (□) jusque dans le
     *    titre du document — d'où les symboles retenus ici (✓ ✂ ✉ ☎ ⚠ ●), tous
     *    vérifiés présents dans la fonte, et le filtre `$plain` appliqué aux
     *    textes venant de la base (les avantages d'un type de billet, eux,
     *    contiennent des emoji).
     */

    /** Montant tel que l'affichent le site et les rapports : « 18 750 FCFA ». */
    $money = static fn ($value): string => number_format((float) $value, 0, ',', ' ').' FCFA';

    /**
     * Un texte de la base débarrassé de ce que DejaVu Sans ne sait pas dessiner.
     *
     * Sans ce filtre, « 🎫 Entrée générale » — un avantage tel qu'il est saisi —
     * s'imprime « □ Entrée générale ». Les plages retirées sont celles des
     * pictogrammes ; aucun texte français n'y puise.
     */
    $plain = static function (?string $text): string {
        if ($text === null || $text === '') {
            return '';
        }

        $stripped = preg_replace(
            '/[\x{FE00}-\x{FE0F}\x{200D}\x{2300}-\x{23FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{3000}-\x{303F}\x{E000}-\x{F8FF}\x{1F000}-\x{1FFFF}]/u',
            '',
            $text,
        ) ?? $text;

        return trim(preg_replace('/\s{2,}/u', ' ', $stripped) ?? $stripped);
    };

    /**
     * « sam. 5 sept. 2026 ».
     *
     * Le français est forcé plutôt que déduit de la locale de l'application :
     * le PDF est aussi produit depuis une file d'attente, sans requête HTTP à
     * partir de laquelle négocier la langue, et `APP_LOCALE` vaut `en`.
     */
    $day = static fn ($date): string => $date
        ? \Carbon\CarbonImmutable::parse($date)->locale('fr')->translatedFormat('D j M Y')
        : '—';

    $hour = static fn ($date): string => $date
        ? \Carbon\CarbonImmutable::parse($date)->format('H:i')
        : '—';

    $stamp = static fn ($date): string => $date
        ? \Carbon\CarbonImmutable::parse($date)->format('d/m/Y à H:i')
        : '—';

    /**
     * Le prix réellement payé pour un type de billet, ligne de commande à
     * l'appui.
     *
     * `ticket_types.price` est le tarif d'aujourd'hui, pas celui de l'achat :
     * la version précédente imprimait 25 000 FCFA sur un billet acheté 18 750
     * en promotion. Un reçu dit ce qui a été payé, donc `order_items`.
     */
    $paidUnit = $order->items->pluck('unit_price', 'ticket_type_id');

    $itemsTotal = (float) $order->items->sum('subtotal');
    $orderTotal = (float) $order->total_amount;

    /** L'écart entre les lignes et le total encaissé (remise, ajustement) : montré plutôt que tu. */
    $adjustment = round($orderTotal - $itemsTotal, 2);

    $status = $order->status;
    $isHonoured = $status === \App\Enums\OrderStatus::PAID;

    /**
     * L'état de la commande, écrit en toutes lettres.
     *
     * Les libellés de l'énumération sont au masculin (« Annulé ») : les coller
     * derrière « Commande » donnait « Commande annulé ». Une phrase par état
     * plutôt qu'un accord bricolé.
     */
    $orderNotice = match ($status) {
        \App\Enums\OrderStatus::PENDING => 'Cette commande est en attente de paiement.',
        \App\Enums\OrderStatus::CANCELLED => 'Cette commande a été annulée.',
        \App\Enums\OrderStatus::REFUNDED => 'Cette commande a été remboursée.',
        default => 'Cette commande n\'est pas honorée.',
    };

    /** « Total remboursé » sur une commande remboursée : l'argent est bien passé, puis reparti. */
    $totalLabel = match ($status) {
        \App\Enums\OrderStatus::PAID => 'Total payé',
        \App\Enums\OrderStatus::PENDING => 'Total à payer',
        \App\Enums\OrderStatus::REFUNDED => 'Total remboursé',
        default => 'Montant total',
    };

    /**
     * Le paiement fait foi sur le moyen employé : c'est la trace de la
     * transaction, alors que `orders.payment_method` n'est que le choix fait à
     * la caisse. On retient l'encaissement réussi, sinon la dernière tentative.
     */
    $payment = $order->payments->firstWhere('status', \App\Enums\PaymentStatus::PAYE)
        ?? $order->payments->sortByDesc('created_at')->first();

    $paymentLabel = $payment?->method?->label()
        ?? \App\Enums\PaymentMethod::tryFrom((string) $order->payment_method)?->label()
        ?? '—';

    $tickets = $order->tickets;
    $ticketCount = $tickets->count();

    /** Une commande ne porte aujourd'hui qu'un événement, mais rien ne l'interdit : le reçu les regroupe. */
    $events = $tickets
        ->map(static fn ($ticket) => $ticket->ticketType?->event)
        ->filter()
        ->unique('id')
        ->values();

    $orderRef = $order->order_number ?: $order->id;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu et billets — commande n° {{ $orderRef }} — TicketExpress</title>
    <style>
        @page { margin: 0; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #191c1d;
        }

        /* --- Pages ------------------------------------------------------- */
        /* 58 px en bas : la hauteur du pied fixe, qui sort du flux. */
        .sheet { padding-bottom: 58px; }
        .sheet + .sheet { page-break-before: always; }
        .sheet-body { padding: 20px 32px 0; }

        /* --- Bandeau de tête --------------------------------------------- */
        .band { background-color: #b9000a; color: #ffffff; padding: 15px 32px 13px; }
        .band table { width: 100%; }
        .band td { vertical-align: top; }
        .wordmark { font-size: 18px; font-weight: bold; }
        .band-sub,
        .band-kind {
            font-size: 7.5px;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #ffdad5;
        }
        .band-right { text-align: right; }
        .band-ref { font-size: 15px; font-weight: bold; }

        /* --- Titres de section ------------------------------------------- */
        .section {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            color: #b9000a;
            border-bottom: 1px solid #d5d8da;
            padding-bottom: 4px;
            margin: 13px 0 8px;
        }

        /* --- Blocs et libellés ------------------------------------------- */
        .cols { width: 100%; }
        .cols td { vertical-align: top; }
        .gutter { width: 14px; }

        .card {
            border: 1px solid #d5d8da;
            padding: 11px 13px;
            background-color: #ffffff;
        }
        .card-title {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #5b5f63;
            margin-bottom: 6px;
        }

        .pairs { width: 100%; }
        .pairs td { padding: 2px 0; vertical-align: top; }
        .pairs .k { width: 38%; color: #5b5f63; padding-right: 8px; }
        .pairs .v { font-weight: bold; }

        .muted { color: #5b5f63; }
        .tiny { font-size: 8.5px; }
        .plain { font-weight: normal; }

        /* --- Pastilles d'état -------------------------------------------- */
        .chip {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8.5px;
            font-weight: bold;
            border-radius: 8px;
        }
        .chip--ok { background-color: #d1e7dd; color: #0f5132; }
        .chip--wait { background-color: #ffddb7; color: #7f5000; }
        .chip--dead { background-color: #ffdad6; color: #93000a; }
        .chip--type { background-color: #b9000a; color: #ffffff; }
        .chip--neutral { background-color: #e7e8e9; color: #5b5f63; }

        .warning {
            border-left: 3px solid #ba1a1a;
            background-color: #ffdad6;
            color: #93000a;
            padding: 8px 11px;
            margin-bottom: 13px;
            font-size: 9.5px;
        }

        /* --- Tableaux de détail ------------------------------------------ */
        .grid { width: 100%; border-collapse: collapse; }
        .grid th {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: #5b5f63;
            text-align: left;
            background-color: #f3f4f5;
            border-bottom: 1px solid #d5d8da;
            padding: 6px 8px;
        }
        .grid td { padding: 5px 8px; border-bottom: 1px solid #e7e8e9; vertical-align: top; }
        .grid .num { text-align: right; }
        .grid .total td {
            border-top: 1.5px solid #b9000a;
            border-bottom: none;
            padding-top: 8px;
            font-size: 12px;
            font-weight: bold;
        }
        .grid .total .lbl {
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #b9000a;
        }
        .mono { font-family: 'DejaVu Sans Mono', monospace; }

        /* --- Billet ------------------------------------------------------ */
        /*
         * Un billet occupe une page entière : les billets d'une même commande
         * portent des participants différents, et chacun doit pouvoir partir
         * seul. Le cadre est donc dimensionné pour remplir la page — d'où la
         * hauteur posée sur la zone centrale, qui absorbe le vide plutôt que de
         * le laisser en bas de feuille. Une hauteur, pas une position absolue :
         * si le contenu grandit (titre long, bandeau d'avertissement), le
         * tableau grandit avec lui au lieu de déborder sur une deuxième page.
         */
        .event-title { font-size: 19px; font-weight: bold; line-height: 1.2; }
        .event-when { font-size: 11px; color: #5b5f63; margin-top: 3px; }

        .ticket { width: 100%; border: 1px solid #d5d8da; border-collapse: collapse; }
        .ticket td { vertical-align: top; }
        /* Les bordures sont posées sur la cellule elle-même, pas via `.rangée td` :
           un sélecteur descendant atteindrait aussi les cellules des tableaux
           imbriqués, et dessinait un filet sous chaque rappel. */
        .ticket-head-cell {
            background-color: #f8f9fa;
            border-bottom: 1px solid #d5d8da;
            padding: 14px 17px 15px;
        }
        .ticket-facts { padding: 15px 17px 17px; }
        .ticket-facts .pairs td { padding: 4px 0; }
        .ticket-facts .pairs .k { width: 42%; }

        /* Le QR sous les informations, et non à côté : en pleine largeur il tient
           en 300 px (79 mm à l'impression), ce qui se scanne du premier coup
           depuis une feuille comme depuis un écran de téléphone. C'est aussi ce
           qui donne au billet sa hauteur, sans hauteur forcée nulle part. */
        .ticket-qr-cell {
            border-top: 1px dashed #b9000a;
            padding: 18px 12px 16px;
            text-align: center;
        }
        .qr-frame {
            width: 316px;
            margin: 0 auto;
            border: 1px solid #d5d8da;
            padding: 7px;
            background-color: #ffffff;
        }
        .qr-number {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        .qr-hint {
            font-size: 7.5px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #5b5f63;
        }

        .benefits { margin-top: 13px; font-size: 9.5px; color: #5b5f63; }
        .benefits span { padding-right: 12px; }

        .ticket-foot-cell {
            background-color: #f8f9fa;
            border-top: 1px solid #d5d8da;
            padding: 10px 17px;
            font-size: 9px;
            color: #5b5f63;
        }
        .ticket-foot-cell table { width: 100%; }
        .ticket-foot-cell .dot { width: 11px; color: #b9000a; }
        .ticket-foot-cell .r { padding: 1px 12px 1px 0; }

        /* Ligne de découpe : la souche que garde le contrôle à l'entrée. */
        .ticket-stub-cell { border-top: 1px dashed #7a7f82; padding: 4px 17px 12px; }
        .cut { color: #7a7f82; font-size: 8.5px; margin-bottom: 6px; }
        .stub { width: 100%; font-size: 9px; }
        .stub td { padding-right: 10px; vertical-align: top; }
        .stub .sk {
            font-size: 7px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #5b5f63;
        }
        .stub .sv { font-weight: bold; }

        /* --- Conditions -------------------------------------------------- */
        .terms {
            background-color: #f3f4f5;
            border-left: 3px solid #b9000a;
            padding: 11px 13px;
            page-break-inside: avoid;
        }
        .terms table { width: 100%; }
        .terms td { font-size: 9px; padding: 1.5px 10px 1.5px 0; vertical-align: top; }
        .terms .dot { width: 11px; color: #b9000a; }

        /* --- Pied de page, répété sur chaque page ------------------------ */
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            height: 46px;
            padding: 7px 32px 0;
            border-top: 1px solid #d5d8da;
            color: #5b5f63;
            font-size: 7.5px;
        }
        .footer table { width: 100%; }
        .footer td { vertical-align: top; }
        .footer .right { text-align: right; }
        .footer strong { color: #b9000a; }
        /* dompdf dessine le pied avant de connaître le nombre total de pages :
           `counter(pages)` y sort 0. Seul le numéro courant est donc affiché. */
        .page-counter:before { content: counter(page); }
    </style>
</head>
<body>

    {{-- Pied répété : dompdf redessine les éléments `position: fixed` sur chaque page. --}}
    <div class="footer">
        <table>
            <tr>
                <td>
                    <strong>TicketExpress</strong> — la billetterie en ligne du Togo<br>
                    ✉ support@ticketexpress.tg &nbsp;·&nbsp; ☎ +228 90 00 00 00 &nbsp;·&nbsp; www.ticketexpress.tg
                </td>
                <td class="right">
                    Commande n° {{ $orderRef }} &nbsp;·&nbsp; page <span class="page-counter"></span><br>
                    Édité le {{ $stamp(now()) }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ================================ REÇU ================================ --}}
    <div class="sheet">
        <div class="band">
            <table>
                <tr>
                    <td>
                        <div class="wordmark">TicketExpress</div>
                        <div class="band-sub">Billetterie en ligne · Togo</div>
                    </td>
                    <td class="band-right">
                        <div class="band-kind">Reçu d'achat</div>
                        <div class="band-ref">N° {{ $orderRef }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="sheet-body">
            @unless($isHonoured)
                <div class="warning">
                    <strong>{{ $orderNotice }}</strong>
                    Ce document vaut justificatif, mais les billets qui suivent ne donnent pas
                    accès à l'événement.
                </div>
            @endunless

            <table class="cols">
                <tr>
                    <td width="50%">
                        <div class="card">
                            <div class="card-title">Acheteur</div>
                            <table class="pairs">
                                <tr>
                                    <td class="k">Nom</td>
                                    <td class="v">{{ $plain($order->first_name.' '.$order->last_name) }}</td>
                                </tr>
                                <tr>
                                    <td class="k">Email</td>
                                    <td class="v">{{ $order->email }}</td>
                                </tr>
                                <tr>
                                    <td class="k">Téléphone</td>
                                    <td class="v">{{ $order->phone }}</td>
                                </tr>
                                <tr>
                                    <td class="k">Commandé le</td>
                                    <td class="v">{{ $stamp($order->created_at) }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td class="gutter"></td>
                    <td width="50%">
                        <div class="card">
                            <div class="card-title">Paiement</div>
                            <table class="pairs">
                                <tr>
                                    <td class="k">État</td>
                                    <td class="v">
                                        <span class="chip {{ $isHonoured ? 'chip--ok' : ($status === \App\Enums\OrderStatus::PENDING ? 'chip--wait' : 'chip--dead') }}">{{ $status->label() }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="k">Moyen</td>
                                    <td class="v">{{ $paymentLabel }}</td>
                                </tr>
                                <tr>
                                    <td class="k">Référence</td>
                                    <td class="v mono">{{ $payment?->transaction_reference ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="k">Encaissé le</td>
                                    <td class="v">{{ $stamp($payment?->paid_at ?? $order->paid_at) }}</td>
                                </tr>
                                @if($order->refunded_at)
                                    <tr>
                                        <td class="k">Remboursé le</td>
                                        <td class="v">{{ $stamp($order->refunded_at) }}</td>
                                    </tr>
                                @elseif($order->cancelled_at)
                                    <tr>
                                        <td class="k">Annulé le</td>
                                        <td class="v">{{ $stamp($order->cancelled_at) }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            @foreach($events as $event)
                @php
                    $venue = $event->venue;
                    $isOnline = $event->event_type === \App\Enums\EventType::ONLINE;
                @endphp
                <div class="section">
                    {{ $events->count() > 1 ? 'Événement '.$loop->iteration.' / '.$events->count() : 'Événement' }}
                </div>
                <div class="event-title">{{ $plain($event->title) }}</div>
                <div class="event-when">
                    {{ $day($event->start_date) }} · {{ $hour($event->start_date) }}@if($event->end_date) – {{ $hour($event->end_date) }}@endif
                </div>
                <table class="pairs" style="margin-top: 7px;">
                    <tr>
                        <td class="k" style="width: 20%;">{{ $isOnline ? 'Accès' : 'Lieu' }}</td>
                        <td class="v">
                            @if($isOnline)
                                En ligne
                                @if($event->online_url)
                                    <span class="mono plain">{{ $event->online_url }}</span>
                                @endif
                            @elseif($venue)
                                {{ $plain($venue->name) }}<span class="plain muted"> — {{ $plain($venue->address) }}, {{ $plain($venue->city) }}@if($venue->country), {{ $plain($venue->country) }}@endif</span>
                            @else
                                Communiqué par l'organisateur
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="k">Organisateur</td>
                        <td class="v">{{ $plain($event->organizer?->company_name) ?: '—' }}</td>
                    </tr>
                </table>
            @endforeach

            <div class="section">Détail de la commande</div>
            <table class="grid">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th class="num" width="85">Prix unitaire</th>
                        <th class="num" width="45">Qté</th>
                        <th class="num" width="95">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $plain($item->ticketType?->name) ?: 'Billet' }}</strong>
                                @if($item->ticketType?->event)
                                    <div class="tiny muted">{{ $plain($item->ticketType->event->title) }}</div>
                                @endif
                            </td>
                            <td class="num">{{ $money($item->unit_price) }}</td>
                            <td class="num">{{ $item->quantity }}</td>
                            <td class="num">{{ $money($item->subtotal) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">Aucune ligne de commande enregistrée.</td>
                        </tr>
                    @endforelse

                    @if(abs($adjustment) >= 0.01)
                        <tr>
                            <td colspan="3">{{ $adjustment < 0 ? 'Remise' : 'Ajustement' }}</td>
                            <td class="num">{{ $money($adjustment) }}</td>
                        </tr>
                    @endif

                    <tr class="total">
                        <td colspan="3" class="lbl">{{ $totalLabel }}</td>
                        <td class="num">{{ $money($orderTotal) }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="tiny muted" style="margin-top: 5px;">
                Montants en francs CFA (XOF)@if($ticketCount) · {{ $ticketCount }} billet{{ $ticketCount > 1 ? 's' : '' }} émis @endif
            </div>

            @if($ticketCount)
                <div class="section">Billets émis</div>
                <table class="grid">
                    <thead>
                        <tr>
                            <th width="24">#</th>
                            <th>N° de billet</th>
                            <th>Catégorie</th>
                            <th>Participant</th>
                            <th width="65">État</th>
                            <th class="num" width="90">Prix payé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td class="muted">{{ $loop->iteration }}</td>
                                <td class="mono">{{ $ticket->ticket_number }}</td>
                                <td>{{ $plain($ticket->ticketType?->name) ?: '—' }}</td>
                                <td>{{ $plain($ticket->attendee_name) ?: '—' }}</td>
                                <td>{{ $ticket->status->label() }}</td>
                                <td class="num">{{ $money($paidUnit[$ticket->ticket_type_id] ?? $ticket->ticketType?->price ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Les conditions d'entrée, elles, sont rappelées sur chaque billet :
                 c'est la page qu'on imprime et qu'on présente au portique. Ici,
                 seulement ce qui concerne la commande entière. --}}
            <div class="section">Bon à savoir</div>
            <div class="terms">
                <table>
                    <tr>
                        <td class="dot">●</td>
                        <td>
                            Chaque billet est nominatif, porte son propre QR code et n'autorise
                            qu'une seule entrée. Les conditions d'accès sont rappelées sur chacun.
                        </td>
                    </tr>
                    <tr>
                        <td class="dot">●</td>
                        <td>
                            Conservez ce document jusqu'à la fin de l'événement : il vaut
                            justificatif d'achat.
                            @if($ticketCount)
                                {{ $ticketCount > 1
                                    ? 'Vos '.$ticketCount.' billets suivent, un par page.'
                                    : 'Votre billet figure à la page suivante.' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- =============================== BILLETS =============================== --}}
    @foreach($tickets as $ticket)
        @php
            $ticketType = $ticket->ticketType;
            $event = $ticketType?->event;
            $occurrence = $ticketType?->occurrence;
            $venue = $event?->venue;
            $isOnline = $event?->event_type === \App\Enums\EventType::ONLINE;

            /* La séance visée par le type de billet prime sur l'événement :
               deux dates d'un même événement ne s'échangent pas. */
            $startsAt = $occurrence?->start_date ?? $event?->start_date;
            $endsAt = $occurrence?->end_date ?? $event?->end_date;

            /* L'heure à laquelle le portique commence à accepter ce billet,
               telle que la calcule celui qui la fait appliquer. */
            $opensAt = $event ? \App\Support\CheckInWindow::opensAt($event, $occurrence) : null;

            $isUsable = $isHonoured && $ticket->status === \App\Enums\TicketStatus::VALID;

            $benefits = collect($ticketType?->benefits ?? [])
                ->map(static fn ($benefit) => $plain(is_string($benefit) ? $benefit : ''))
                ->filter()
                ->take(4);
        @endphp

        <div class="sheet">
            <div class="band">
                <table>
                    <tr>
                        <td>
                            <div class="wordmark">TicketExpress</div>
                            <div class="band-sub">Billet électronique</div>
                        </td>
                        <td class="band-right">
                            <div class="band-kind">Billet {{ $loop->iteration }} / {{ $ticketCount }}</div>
                            <div class="band-ref">{{ $ticket->ticket_number }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="sheet-body">
                {{-- Un billet peut être inutilisable pour deux raisons distinctes :
                     son propre état, ou celui de la commande. Le bandeau nomme la
                     bonne — « Billet valide » sur une commande annulée serait un
                     contresens. --}}
                @unless($isUsable)
                    <div class="warning">
                        @if($ticket->status === \App\Enums\TicketStatus::USED)
                            <strong>Billet utilisé.</strong>
                            Il a été validé le {{ $stamp($ticket->checked_in_at) }} : un QR code ne
                            permet qu'une seule entrée.
                        @elseif($ticket->status !== \App\Enums\TicketStatus::VALID)
                            <strong>Billet {{ mb_strtolower($ticket->status->label()) }}.</strong>
                            Ce billet ne donne plus accès à l'événement.
                        @else
                            <strong>{{ $orderNotice }}</strong>
                            Ce billet ne donne pas accès à l'événement.
                        @endif
                    </div>
                @endunless

                <table class="ticket">
                    <tr>
                        <td class="ticket-head-cell">
                            <div class="event-title">{{ $event ? $plain($event->title) : 'Événement non renseigné' }}</div>
                            <div class="event-when">
                                {{ $day($startsAt) }} · {{ $hour($startsAt) }}@if($endsAt) – {{ $hour($endsAt) }}@endif
                            </div>
                            <div style="margin-top: 8px;">
                                <span class="chip chip--type">{{ $plain($ticketType?->name) ?: 'Billet' }}</span>
                                @if($ticketType?->location_details)
                                    <span class="chip chip--neutral">{{ $plain($ticketType->location_details) }}</span>
                                @endif
                                <span class="chip {{ $ticket->status === \App\Enums\TicketStatus::VALID ? 'chip--ok' : 'chip--dead' }}">{{ $ticket->status->label() }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="ticket-facts">
                            <table class="cols">
                                <tr>
                                    <td width="50%">
                                        <table class="pairs">
                                            <tr>
                                                <td class="k">Participant</td>
                                                <td class="v">{{ $plain($ticket->attendee_name) ?: '—' }}</td>
                                            </tr>
                                            @if($ticket->attendee_email)
                                                <tr>
                                                    <td class="k">Email</td>
                                                    <td class="v">{{ $ticket->attendee_email }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="k">Prix payé</td>
                                                <td class="v">{{ $money($paidUnit[$ticket->ticket_type_id] ?? $ticketType?->price ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="k">Commande</td>
                                                <td class="v">
                                                    n° {{ $orderRef }}
                                                    <div class="tiny muted plain">{{ $plain($order->first_name.' '.$order->last_name) }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="gutter"></td>
                                    <td width="50%">
                                        <table class="pairs">
                                            <tr>
                                                <td class="k">{{ $isOnline ? 'Accès' : 'Lieu' }}</td>
                                                <td class="v">
                                                    @if($isOnline)
                                                        En ligne
                                                        @if($event?->online_url)
                                                            <div class="tiny mono plain">{{ $event->online_url }}</div>
                                                        @endif
                                                    @elseif($venue)
                                                        {{ $plain($venue->name) }}
                                                        <div class="tiny muted plain">
                                                            {{ $plain($venue->address) }}, {{ $plain($venue->city) }}@if($venue->country), {{ $plain($venue->country) }}@endif
                                                        </div>
                                                    @else
                                                        Communiqué par l'organisateur
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($opensAt)
                                                <tr>
                                                    <td class="k">Contrôle ouvert dès</td>
                                                    <td class="v">{{ $stamp($opensAt) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="k">Organisateur</td>
                                                <td class="v">{{ $plain($event?->organizer?->company_name) ?: '—' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if($benefits->isNotEmpty())
                                <div class="benefits">
                                    @foreach($benefits as $benefit)
                                        <span>✓ {{ $benefit }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="ticket-qr-cell">
                            {{-- Image en base64 : dompdf ne rend pas un <svg> inline et ne va pas
                                 chercher l'URL protégée par jeton pendant le rendu (le PDF est
                                 parfois produit en file d'attente, hors contexte HTTP). Le PNG
                                 est écrit en 600 px pour un affichage en 300 px, soit deux fois
                                 la résolution nécessaire — ce qui garde les modules francs sur
                                 une feuille pliée ou photocopiée. --}}
                            <div class="qr-frame">
                                <img src="{{ \App\Support\QrImage::dataUri($ticket->qr_code, 600, 1) }}"
                                     alt="QR code du billet {{ $ticket->ticket_number }}"
                                     width="300" height="300">
                            </div>
                            <div class="qr-number">{{ $ticket->ticket_number }}</div>
                            <div class="qr-hint">à scanner à l'entrée</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="ticket-foot-cell">
                            <table>
                                <tr>
                                    <td class="dot">●</td>
                                    <td class="r">Billet nominatif et non transférable — une pièce d'identité peut être demandée.</td>
                                    <td class="dot">●</td>
                                    <td class="r">Une seule entrée par QR code : une fois scanné, il ne sert plus.</td>
                                </tr>
                                <tr>
                                    <td class="dot">●</td>
                                    <td class="r">Sur papier ou sur l'écran du téléphone, au choix.</td>
                                    <td class="dot">●</td>
                                    <td class="r">Arrivez 30 minutes avant le début pour éviter l'attente.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="ticket-stub-cell">
                            <div class="cut">✂ &nbsp;souche à conserver</div>
                            <table class="stub">
                                <tr>
                                    <td>
                                        <div class="sk">Billet</div>
                                        <div class="sv mono">{{ $ticket->ticket_number }}</div>
                                    </td>
                                    <td>
                                        <div class="sk">Participant</div>
                                        <div class="sv">{{ $plain($ticket->attendee_name) ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="sk">Événement</div>
                                        <div class="sv">{{ $event ? $plain($event->title) : '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="sk">Date</div>
                                        <div class="sv">{{ $day($startsAt) }} · {{ $hour($startsAt) }}</div>
                                    </td>
                                    <td>
                                        <div class="sk">Catégorie</div>
                                        <div class="sv">{{ $plain($ticketType?->name) ?: '—' }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach

    @unless($ticketCount)
        <div class="sheet">
            <div class="sheet-body">
                <div class="warning">
                    Aucun billet n'est rattaché à cette commande : ils sont émis à l'encaissement
                    du paiement.
                </div>
            </div>
        </div>
    @endunless

</body>
</html>
