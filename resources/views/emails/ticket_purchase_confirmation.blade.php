<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'achat - TicketExpress</title>
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
            margin-bottom: 0;
        }
        .details-table td {
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 14px;
        }
        .details-table tr:last-child td {
            border-bottom: none;
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
        .ticket-item {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 10px 0 0 0;
        }
        .total-row td {
            border-bottom: none !important;
            padding-top: 14px !important;
            font-size: 15px !important;
        }
        .total-row td.label {
            color: #0f172a !important;
            font-weight: 700 !important;
        }
        .total-row td.value {
            color: #0f766e !important;
            font-size: 17px !important;
            font-weight: 700 !important;
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
            <h1 class="title">Confirmation d'achat</h1>
            <p class="header-sub">Merci pour votre commande. Vos billets sont validés.</p>
        </div>

        <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
        <p>Votre commande a été confirmée avec succès. Vous trouverez ci-joint votre billet au format PDF.</p>

        @php
            $firstTicket = $order->tickets?->first();
            $event = $firstTicket?->ticketType?->event ?? null;
        @endphp

        {{-- Informations de commande + événement --}}
        <div class="ticket-card">
            <div class="ticket-card-title">Informations de commande</div>
            <table class="details-table">
                <tr>
                    <td class="label">Numéro de commande :</td>
                    <td class="value">{{ $order->order_number ?: $order->id }}</td>
                </tr>
                <tr>
                    <td class="label">Date :</td>
                    <td class="value">{{ $order->created_at ? $order->created_at->format('d/m/Y à H:i') : now()->format('d/m/Y à H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Email :</td>
                    <td class="value">{{ $order->email }}</td>
                </tr>
                @if($event)
                <tr>
                    <td class="label">Événement :</td>
                    <td class="value">{{ $event->title ?? $event->name ?? 'Événement' }}</td>
                </tr>
                @if($event->start_date)
                <tr>
                    <td class="label">Date de l'événement :</td>
                    <td class="value">{{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y à H:i') }}</td>
                </tr>
                @endif
                @if($event->venue?->name ?? $event->location ?? null)
                <tr>
                    <td class="label">Lieu :</td>
                    <td class="value">{{ $event->venue?->name ?? $event->location }}</td>
                </tr>
                @endif
                @endif
            </table>
        </div>

        {{-- Billets réservés --}}
        @if($order->tickets && $order->tickets->isNotEmpty())
        <div class="ticket-card">
            <div class="ticket-card-title">Billets réservés ({{ $order->tickets->count() }})</div>
            @foreach($order->tickets as $ticket)
            <div class="ticket-item">
                <table class="details-table">
                    <tr>
                        <td class="label">Type :</td>
                        <td class="value">{{ $ticket->ticketType?->name ?? 'Standard' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Numéro :</td>
                        <td class="value" style="font-family: 'SFMono-Regular', Consolas, monospace; letter-spacing: 1px; font-size: 13px;">{{ $ticket->ticket_number }}</td>
                    </tr>
                    @if($ticket->attendee_name)
                    <tr>
                        <td class="label">Participant :</td>
                        <td class="value">{{ $ticket->attendee_name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Prix unitaire :</td>
                        <td class="value">{{ number_format((float) ($ticket->ticketType?->price ?? 0), 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
            @endforeach

            {{-- Total --}}
            <table class="details-table" style="margin-top: 16px; border-top: 2px solid #e2e8f0; padding-top: 4px;">
                <tr class="total-row">
                    <td class="label" style="color: #0f172a; font-weight: 700; font-size: 15px; border-bottom: none; padding-top: 14px;">Montant total payé :</td>
                    <td class="value" style="color: #0f766e; font-weight: 700; font-size: 17px; border-bottom: none; padding-top: 14px;">{{ number_format((float) $order->total_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>
        @endif

        @if($includeWhatsAppLink && $whatsappLink)
        <div style="background-color: #f0fdf4; border: 1px solid #dcfce7; border-radius: 10px; padding: 20px; text-align: center; margin: 24px 0;">
            <p style="font-weight: 600; margin: 0 0 8px 0; color: #166534; font-size: 15px;">Recevoir la confirmation sur WhatsApp</p>
            <p style="font-size: 13px; color: #4b5563; margin: 0 0 14px 0;">Accédez directement à vos billets sur WhatsApp :</p>
            <a href="{{ $whatsappLink }}"
               style="display: inline-block; padding: 11px 24px; background-color: #25D366; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;"
               target="_blank">
                Ouvrir sur WhatsApp
            </a>
        </div>
        @endif

        <div class="notice-box">
            <strong>Consignes d'accès :</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 18px;">
                <li>Présentez votre QR code (sur votre téléphone ou imprimé) à l'entrée.</li>
                <li>Le billet au format PDF officiel est joint à cet e-mail.</li>
                <li>Chaque billet est individuel et à usage unique lors du contrôle.</li>
                <li>Conservez ce message jusqu'à la fin de l'événement.</li>
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
