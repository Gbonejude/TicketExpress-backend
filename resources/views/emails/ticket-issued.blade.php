<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre billet - {{ $event->title ?? 'TicketExpress' }}</title>
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
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .ticket-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 24px;
            margin: 24px 0;
        }
        .ticket-card-title {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
        }
        .details-table td.label {
            color: #64748b;
            font-weight: 500;
            width: 45%;
        }
        .details-table td.value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }
        .qr-section {
            text-align: center;
            padding-top: 12px;
        }
        .qr-wrapper {
            background-color: #ffffff;
            padding: 16px;
            display: inline-block;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }
        .ticket-code {
            margin-top: 10px;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            letter-spacing: 1.5px;
        }
        .notice-box {
            background-color: #f1f5f9;
            border-left: 4px solid #0f766e;
            padding: 14px 18px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 13px;
            color: #334155;
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 13px;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand-name">TicketExpress</div>
            <h1 class="title">{{ $event->title ?? 'Votre billet d\'accès' }}</h1>
        </div>

        <p>Bonjour,</p>
        <p>Votre billet pour l'événement <strong>{{ $event->title ?? 'votre réservation' }}</strong> est prêt et confirmé.</p>

        <div class="ticket-card">
            <div class="ticket-card-title">Détails du billet</div>

            <table class="details-table">
                <tr>
                    <td class="label">Numéro de billet :</td>
                    <td class="value">{{ $ticket->ticket_number }}</td>
                </tr>
                <tr>
                    <td class="label">Catégorie / Type :</td>
                    <td class="value">{{ $ticketType->name ?? 'Standard' }}</td>
                </tr>
                <tr>
                    <td class="label">Prix unitaire :</td>
                    <td class="value">{{ number_format((float) ($ticketType->price ?? 0), 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($ticket->attendee_name)
                <tr>
                    <td class="label">Participant :</td>
                    <td class="value">{{ $ticket->attendee_name }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Accès :</td>
                    <td class="value">{{ $accessMethod ?? 'Physique' }}</td>
                </tr>
            </table>

            <div class="qr-section">
                <div class="qr-wrapper">
                    @php
                        $qrBytes = $qrPng ?? \App\Support\QrImage::png($ticket->qr_code ?: $ticket->ticket_number, 300, 2);
                        $qrSrc = isset($message) ? $message->embedData($qrBytes, 'qrcode.png', 'image/png') : \App\Support\QrImage::dataUri($ticket->qr_code ?: $ticket->ticket_number, 300, 2);
                    @endphp
                    <img src="{{ $qrSrc }}"
                         alt="QR code - {{ $ticket->ticket_number }}"
                         width="180"
                         height="180"
                         style="display: block; margin: 0 auto; border: 0;" />
                </div>
                <div class="ticket-code">{{ $ticket->ticket_number }}</div>
            </div>
        </div>

        <div class="notice-box">
            <strong>Consignes d'accès :</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 18px;">
                <li>Présentez ce code QR sur votre smartphone ou imprimé à l'entrée.</li>
                <li>Chaque billet est individuel et à usage unique lors du contrôle.</li>
                <li>Le billet au format PDF est également joint à ce message.</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>TicketExpress</strong> — Plateforme de billetterie en ligne</p>
            <p>Pour toute question ou assistance : support@ticketexpress.tg</p>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 12px;">© {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>