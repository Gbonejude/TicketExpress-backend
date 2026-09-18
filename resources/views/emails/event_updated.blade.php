<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de l'événement - TicketExpress</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            max-width: 600px;
            margin: 0 auto;
            padding: 24px 16px;
            background-color: #f8fafc;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .brand-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        .title {
            color: #0f172a;
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
        }
        .header-sub {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .ticket-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .ticket-card-title {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .details-table td { padding: 7px 0; border-bottom: 1px dashed #e2e8f0; }
        .details-table tr:last-child td { border-bottom: none; }
        .details-table td.label { color: #64748b; width: 45%; }
        .details-table td.value { font-weight: 600; color: #0f172a; text-align: right; }
        .info-box {
            background-color: #f1f5f9;
            border-left: 4px solid #0f766e;
            padding: 14px 18px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 13px;
            color: #334155;
        }
        .info-box strong { display: block; margin-bottom: 6px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.4px; }
        .button-container { text-align: center; margin: 28px 0; }
        .button {
            display: inline-block;
            padding: 12px 28px;
            background-color: #0f766e;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 13px;
        }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand-name">TicketExpress</div>
            <h1 class="title">Mise à jour de votre événement</h1>
            <p class="header-sub">Des modifications ont été apportées à l'événement</p>
        </div>

        <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
        <p>L'organisateur de l'événement pour lequel vous avez réservé des billets a apporté des modifications.</p>

        <div class="ticket-card">
            <div class="ticket-card-title">Détails de l'événement</div>
            <table class="details-table">
                <tr>
                    <td class="label">Événement :</td>
                    <td class="value">{{ $event->title }}</td>
                </tr>
                <tr>
                    <td class="label">Date de début :</td>
                    <td class="value">{{ $event->start_date ? $event->start_date->format('d/m/Y à H:i') : 'Non précisée' }}</td>
                </tr>
                @if($event->end_date)
                <tr>
                    <td class="label">Date de fin :</td>
                    <td class="value">{{ $event->end_date->format('d/m/Y à H:i') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Lieu :</td>
                    <td class="value">{{ $event->venue?->name ?? $event->online_url ?? 'Non spécifié' }}</td>
                </tr>
                <tr>
                    <td class="label">Numéro de commande :</td>
                    <td class="value">{{ $order->order_number }}</td>
                </tr>
            </table>
        </div>

        @if($changesSummary)
        <div class="info-box">
            <strong>Détails des modifications :</strong>
            {{ $changesSummary }}
        </div>
        @endif

        <p>Vos billets réservés restent valides avec ces nouvelles informations.</p>

        <div class="button-container">
            <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}/events/{{ $event->slug }}" class="button">
                Voir les détails de l'événement
            </a>
        </div>

        <p>Si vous avez la moindre question, n'hésitez pas à nous contacter à <a href="mailto:support@ticketexpress.tg" style="color: #0f766e;">support@ticketexpress.tg</a>.</p>

        <div class="footer">
            <p><strong>TicketExpress</strong> — Plateforme de billetterie en ligne</p>
            <p>Assistance : support@ticketexpress.tg</p>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 12px;">&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
