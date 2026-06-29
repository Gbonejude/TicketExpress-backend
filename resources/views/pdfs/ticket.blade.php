<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - TicketExpress</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .order-info {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .order-info h3 {
            color: #4CAF50;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 40%;
            color: #666;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }
        .ticket-container {
            page-break-inside: avoid;
            margin-bottom: 30px;
            border: 2px solid #4CAF50;
            border-radius: 10px;
            padding: 20px;
            background: linear-gradient(to bottom, #ffffff 0%, #f9f9f9 100%);
        }
        .ticket-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #4CAF50;
        }
        .event-name {
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }
        .event-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .event-location {
            font-size: 12px;
            color: #999;
        }
        .ticket-body {
            margin: 20px 0;
        }
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
        }
        .qr-code {
            margin: 15px auto;
        }
        .ticket-number {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #E8F5E9;
            border-radius: 5px;
            color: #2E7D32;
        }
        .ticket-type {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .attendee-info {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #4CAF50;
            color: #666;
            font-size: 10px;
        }
        .instructions {
            background-color: #FFF3CD;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #FFC107;
        }
        .instructions h4 {
            color: #F57C00;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .instructions ul {
            margin-left: 20px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🎟️ TicketExpress</div>
        <div class="subtitle">Votre ticket électronique</div>
    </div>

    <div class="order-info">
        <h3>📋 Informations de commande</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Numéro de commande:</div>
                <div class="info-value">{{ $order->id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'achat:</div>
                <div class="info-value">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Client:</div>
                <div class="info-value">{{ $order->first_name }} {{ $order->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $order->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone:</div>
                <div class="info-value">{{ $order->phone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total payé:</div>
                <div class="info-value"><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} XOF</strong></div>
            </div>
        </div>
    </div>

    @foreach($order->tickets as $index => $ticket)
        @php
            $ticketType = $ticket->ticketType;
            $event = $ticketType->event ?? null;
        @endphp

        <div class="ticket-container">
            @if($event)
                <div class="ticket-header">
                    <div class="event-name">{{ $event->name }}</div>
                    <div class="event-date">
                        📅 {{ $event->start_date->format('d/m/Y à H:i') }}
                    </div>
                    <div class="event-location">
                        📍 {{ $event->location }}
                    </div>
                </div>
            @endif

            <div class="ticket-body">
                <div style="text-align: center;">
                    <span class="ticket-type">{{ $ticketType->name }}</span>
                </div>

                <div class="ticket-number">
                    🎫 {{ $ticket->ticket_number }}
                </div>

                <div class="qr-section">
                    <div class="qr-code">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($ticket->qr_code) !!}
                    </div>
                    <p style="color: #666; font-size: 10px; margin-top: 10px;">
                        Scannez ce QR code à l'entrée
                    </p>
                </div>

                <div class="attendee-info">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Participant:</div>
                            <div class="info-value">{{ $ticket->attendee_name }}</div>
                        </div>
                        @if($ticket->attendee_email)
                        <div class="info-row">
                            <div class="info-label">Email participant:</div>
                            <div class="info-value">{{ $ticket->attendee_email }}</div>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-label">Prix:</div>
                            <div class="info-value">{{ number_format($ticketType->price, 0, ',', ' ') }} XOF</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

    <div class="instructions">
        <h4>⚠️ Instructions importantes</h4>
        <ul>
            <li>Présentez ce QR code à l'entrée de l'événement</li>
            <li>Conservez ce ticket jusqu'à la fin de l'événement</li>
            <li>Ce ticket est personnel et non transférable</li>
            <li>Arrivez 30 minutes avant le début pour éviter la queue</li>
        </ul>
    </div>

    <div class="footer">
        <p><strong>TicketExpress</strong> - La plateforme de réservation de tickets au Togo</p>
        <p style="margin-top: 10px;">
            📧 support@ticketexpress.tg | 📞 +228 XX XX XX XX<br>
            🌐 www.ticketexpress.tg
        </p>
        <p style="margin-top: 15px; font-size: 9px; color: #999;">
            Document généré le {{ now()->format('d/m/Y à H:i') }} | Ticket valide sous présentation du QR code
        </p>
    </div>
</body>
</html>
