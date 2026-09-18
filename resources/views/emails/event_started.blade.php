<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'événement commence ! - TicketExpress</title>
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
        .notice-box {
            background-color: #f1f5f9;
            border-left: 4px solid #0f766e;
            padding: 14px 18px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 13px;
            color: #334155;
        }
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
            <h1 class="title">L'événement commence !</h1>
            <p class="header-sub">Le grand moment est arrivé</p>
        </div>

        <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
        <p>Votre événement commence maintenant. Préparez votre QR code et profitez de l'expérience !</p>

        <div class="ticket-card">
            <div class="ticket-card-title">Votre réservation</div>
            <table class="details-table">
                <tr>
                    <td class="label">Événement :</td>
                    <td class="value">{{ $event->title }}</td>
                </tr>
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

        <div class="notice-box">
            Pour entrer sans attendre, ouvrez vos billets ci-dessous et présentez votre QR code au contrôle.
        </div>

        <div class="button-container">
            <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}/tickets" class="button">
                Afficher mes billets et QR Code
            </a>
        </div>

        <p>Nous vous souhaitons un merveilleux moment !</p>

        <div class="footer">
            <p><strong>TicketExpress</strong> — Plateforme de billetterie en ligne</p>
            <p>Assistance : support@ticketexpress.tg</p>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 12px;">&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
