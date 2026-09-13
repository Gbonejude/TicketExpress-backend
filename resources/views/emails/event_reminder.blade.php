<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel d'événement - TicketExpress</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        .event-details {
            background-color: #fffbeb;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border: 1px solid #fde68a;
        }
        .event-details p {
            margin: 8px 0;
            font-size: 15px;
        }
        .event-details strong {
            color: #b45309;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #f59e0b;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #d97706;
        }
        .tips-box {
            background-color: #f8f9fa;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .tips-box strong {
            color: #b45309;
            display: block;
            margin-bottom: 8px;
        }
        .tips-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
            color: #4b5563;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Rappel d'événement</h1>
            <p>TicketExpress</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
            
            <p>C'est un rappel que votre événement approche à grands pas ! Préparez-vous à vivre une excellente expérience.</p>

            <div class="event-details">
                <p><strong>📅 Événement :</strong> {{ $event->title }}</p>
                <p><strong>🗓️ Date de début :</strong> {{ $event->start_date ? $event->start_date->format('d/m/Y à H:i') : 'Non précisée' }}</p>
                @if($event->end_date)
                <p><strong>⏰ Fin prévue :</strong> {{ $event->end_date->format('d/m/Y à H:i') }}</p>
                @endif
                <p><strong>📍 Lieu :</strong> {{ $event->venue?->name ?? $event->online_url ?? 'Lieu non spécifié' }}</p>
                <p><strong>🔢 Commande :</strong> {{ $order->order_number }} ({{ $order->tickets->count() }} billet(s))</p>
            </div>

            <div class="tips-box">
                <strong>💡 Conseils pour le jour J :</strong>
                <ul>
                    <li>Ayez votre QR code de billet accessible sur votre téléphone ou imprimé.</li>
                    <li>Arrivez en avance pour faciliter le contrôle à l'entrée.</li>
                    <li>Une pièce d'identité peut vous être demandée à l'accueil.</li>
                </ul>
            </div>

            <div class="btn-container">
                <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}/tickets" class="btn">Accéder à mes billets</a>
            </div>

            <p>À très bientôt sur TicketExpress !</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe TicketExpress</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
