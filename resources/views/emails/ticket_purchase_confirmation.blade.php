<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'achat - TicketExpress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 28px;
        }
        .ticket-icon {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .info-section {
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }
        .info-section h3 {
            margin-top: 0;
            color: #4CAF50;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .ticket-list {
            margin: 20px 0;
        }
        .ticket-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 3px solid #4CAF50;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #4CAF50;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #25D366;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #20BA5A;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .whatsapp-section {
            background-color: #E8F5E9;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="ticket-icon">🎟️</div>
            <h1>Confirmation d'achat</h1>
            <p>Merci pour votre confiance !</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
            
            <p>Votre commande a été confirmée avec succès ! Vous trouverez ci-joint votre ticket au format PDF.</p>

            <div class="info-section">
                <h3>📋 Informations de commande</h3>
                <div class="info-row">
                    <span class="info-label">Numéro de commande:</span>
                    <span class="info-value">{{ $order->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $order->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $order->phone }}</span>
                </div>
            </div>

            @if($order->tickets->isNotEmpty())
                @php
                    $firstTicket = $order->tickets->first();
                    $event = $firstTicket->ticketType->event ?? null;
                @endphp

                @if($event)
                <div class="info-section">
                    <h3>🎉 Détails de l'événement</h3>
                    <div class="info-row">
                        <span class="info-label">Événement:</span>
                        <span class="info-value">{{ $event->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ $event->start_date->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lieu:</span>
                        <span class="info-value">{{ $event->location }}</span>
                    </div>
                </div>
                @endif

                <div class="ticket-list">
                    <h3>🎫 Vos tickets ({{ $order->tickets->count() }})</h3>
                    @foreach($order->tickets as $ticket)
                        <div class="ticket-item">
                            <div class="info-row">
                                <span class="info-label">Type:</span>
                                <span class="info-value">{{ $ticket->ticketType->name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Code:</span>
                                <span class="info-value"><strong>{{ $ticket->ticket_number }}</strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Participant:</span>
                                <span class="info-value">{{ $ticket->attendee_name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="total">
                Total payé: {{ number_format((float) $order->total_amount, 0, ',', ' ') }} XOF
            </div>

            @if($includeWhatsAppLink && $whatsappLink)
            <div class="whatsapp-section">
                <h3>📱 Recevez votre confirmation sur WhatsApp</h3>
                <p>Cliquez sur le bouton ci-dessous pour recevoir votre confirmation directement sur WhatsApp :</p>
                <a href="{{ $whatsappLink }}" class="button" target="_blank">
                    📱 Envoyer sur WhatsApp
                </a>
                <p style="font-size: 12px; color: #666; margin-top: 10px;">
                    Un message pré-rempli s'ouvrira. Il vous suffit de l'envoyer !
                </p>
            </div>
            @endif

            <div style="background-color: #FFF3CD; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong>⚠️ Important:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Présentez votre QR code à l'entrée de l'événement</li>
                    <li>Conservez ce ticket en sécurité</li>
                    <li>Le PDF est joint à cet email</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>TicketExpress</strong></p>
            <p>La plateforme de réservation de tickets au Togo</p>
            <p>
                Besoin d'aide ? Contactez-nous :<br>
                📧 support@ticketexpress.tg<br>
                📞 +228 XX XX XX XX
            </p>
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                © {{ date('Y') }} TicketExpress. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
