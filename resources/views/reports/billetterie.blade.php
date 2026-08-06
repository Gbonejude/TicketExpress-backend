@php
    /**
     * Rapport de billetterie, en PDF (dompdf, A4 paysage).
     *
     * Volontairement sobre : dompdf ne gère ni flex ni grid, donc la mise en page
     * repose sur des tableaux, et les couleurs restent en dur — les variables CSS
     * ne sont pas interprétées.
     */
    $money = fn ($value) => number_format((float) $value, 0, ',', ' ') . ' FCFA';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de billetterie — {{ $period->label }}</title>
    <style>
        @page { margin: 18px 22px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }
        h1 { font-size: 17px; margin: 0 0 2px; color: #b9000a; }
        h2 { font-size: 12px; margin: 16px 0 6px; color: #333; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        .cards td {
            width: 20%;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 8px;
            vertical-align: top;
        }
        .card-label { font-size: 8px; color: #666; text-transform: uppercase; }
        .card-value { font-size: 13px; font-weight: bold; }
        .data th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 5px 6px;
            border-bottom: 1px solid #ccc;
            font-size: 9px;
        }
        .data td {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
        }
        .data tr:nth-child(even) td { background-color: #fafafa; }
        .num { text-align: right; }
        .footer { margin-top: 14px; font-size: 8px; color: #888; text-align: center; }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            background-color: #eee;
        }
    </style>
</head>
<body>
    <h1>Rapport de billetterie</h1>
    <div class="muted">
        {{ $period->label }}
        @if ($filters['organizer']) &middot; Organisateur : {{ $filters['organizer'] }} @endif
        @if ($filters['event']) &middot; Événement : {{ $filters['event'] }} @endif
        @if ($filters['participant']) &middot; Participant : {{ $filters['participant'] }} @endif
        &middot; généré le {{ $generatedAt }}
    </div>

    <h2>Synthèse</h2>
    <table class="cards">
        <tr>
            <td>
                <div class="card-label">Chiffre d'affaires</div>
                <div class="card-value">{{ $money($revenue) }}</div>
            </td>
            <td>
                <div class="card-label">Commission ({{ round($commissionRate * 100) }} %)</div>
                <div class="card-value">{{ $money($commission) }}</div>
            </td>
            <td>
                <div class="card-label">Net organisateurs</div>
                <div class="card-value">{{ $money($netRevenue) }}</div>
            </td>
            <td>
                <div class="card-label">Commandes payées</div>
                <div class="card-value">{{ $paidOrdersCount }} <span class="muted">/ {{ $ordersCount }}</span></div>
            </td>
            <td>
                <div class="card-label">Billets vendus</div>
                <div class="card-value">{{ $ticketsSold }} <span class="muted">· {{ $checkedIn }} entrés</span></div>
            </td>
        </tr>
    </table>

    <h2>Commandes par statut</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Statut</th>
                <th class="num">Commandes</th>
                <th class="num">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ordersByStatus as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ $row['count'] }}</td>
                    <td class="num">{{ $money($row['amount']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($topEvents))
        <h2>Événements les plus vendus</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Événement</th>
                    <th>Organisateur</th>
                    <th class="num">Billets</th>
                    <th class="num">Chiffre d'affaires</th>
                    <th class="num">Commission</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topEvents as $event)
                    <tr>
                        <td>{{ $event['title'] }}</td>
                        <td>{{ $event['organizer'] }}</td>
                        <td class="num">{{ $event['tickets'] }}</td>
                        <td class="num">{{ $money($event['revenue']) }}</td>
                        <td class="num">{{ $money($event['commission']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (count($revenueByCategory))
        <h2>Répartition par catégorie</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th class="num">Billets</th>
                    <th class="num">Chiffre d'affaires</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($revenueByCategory as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="num">{{ $row['tickets'] }}</td>
                        <td class="num">{{ $money($row['revenue']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Journal des commandes</h2>
    <table class="data">
        <thead>
            <tr>
                <th>N° commande</th>
                <th>Participant</th>
                <th>Téléphone</th>
                <th>Événement</th>
                <th class="num">Billets</th>
                <th class="num">Montant</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>#{{ $order['orderNumber'] }}</td>
                    <td>{{ $order['participant'] }}</td>
                    <td>{{ $order['phone'] ?? '—' }}</td>
                    <td>{{ $order['events']->join(', ') ?: '—' }}</td>
                    <td class="num">{{ $order['quantity'] }}</td>
                    <td class="num">{{ $money($order['totalAmount']) }}</td>
                    <td><span class="badge">{{ $order['statusLabel'] }}</span></td>
                    <td>{{ $order['createdAt'] ? \Carbon\Carbon::parse($order['createdAt'])->format('d/m/Y H:i') : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">Aucune commande sur cette période.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($truncated)
        <div class="footer">
            Journal limité aux 500 commandes les plus récentes de la période.
        </div>
    @endif

    <div class="footer">
        TicketExpress &middot; document généré automatiquement le {{ $generatedAt }}
    </div>
</body>
</html>
