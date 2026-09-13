<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'événement commence ! - TicketExpress</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
            background-color: #ecfdf5;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border: 1px solid #a7f3d0;
        }
        .event-details p {
            margin: 8px 0;
            font-size: 15px;
        }
        .event-details strong {
            color: #047857;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #059669;
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
            <h1>🎉 C'est parti ! L'événement commence !</h1>
            <p>TicketExpress</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
            
            <p>Le grand moment est arrivé ! Votre événement commence maintenant.</p>

            <div class="event-details">
                <p><strong>📅 Événement :</strong> {{ $event->title }}</p>
                <p><strong>📍 Lieu :</strong> {{ $event->venue?->name ?? $event->online_url ?? 'Lieu non spécifié' }}</p>
                <p><strong>🔢 Commande :</strong> {{ $order->order_number }}</p>
            </div>

            <p>Pour entrer sans attendre, ouvrez vos billets ci-dessous et présentez votre QR code au contrôle.</p>

            <div class="btn-container">
                <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}/tickets" class="btn">Afficher mes billets & QR Code</a>
            </div>

            <p>Nous vous souhaitons un merveilleux moment !</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe TicketExpress</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
