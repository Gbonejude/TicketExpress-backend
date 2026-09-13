<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de l'événement - TicketExpress</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content p {
            margin: 0 0 15px;
            font-size: 16px;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #1e40af;
            display: block;
            margin-bottom: 8px;
        }
        .info-box p {
            margin: 0;
            color: #1e40af;
            font-size: 15px;
        }
        .event-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .event-details p {
            margin: 8px 0;
            font-size: 15px;
        }
        .event-details strong {
            color: #1d4ed8;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2563eb;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #666666;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Mise à jour de votre événement</h1>
            <p>TicketExpress</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
            
            <p>L'organisateur de l'événement pour lequel vous avez réservé des billets a apporté des modifications aux informations de l'événement.</p>

            <div class="event-details">
                <p><strong>📅 Événement :</strong> {{ $event->title }}</p>
                <p><strong>🗓️ Date de début :</strong> {{ $event->start_date ? $event->start_date->format('d/m/Y à H:i') : 'Non précisée' }}</p>
                @if($event->end_date)
                <p><strong>⏰ Date de fin :</strong> {{ $event->end_date->format('d/m/Y à H:i') }}</p>
                @endif
                <p><strong>📍 Lieu :</strong> {{ $event->venue?->name ?? $event->online_url ?? 'Lieu non spécifié' }}</p>
            </div>

            @if($changesSummary)
            <div class="info-box">
                <strong>📝 Détails des modifications :</strong>
                <p>{{ $changesSummary }}</p>
            </div>
            @endif

            <p>Vos billets réservés (Commande <strong>{{ $order->order_number }}</strong>) restent valides avec ces nouvelles informations.</p>

            <div class="btn-container">
                <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}/events/{{ $event->slug }}" class="btn">Voir les détails de l'événement</a>
            </div>

            <p>Si vous avez la moindre question, n'hésitez pas à nous contacter à <a href="mailto:support@ticketexpress.tg">support@ticketexpress.tg</a>.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe TicketExpress</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
