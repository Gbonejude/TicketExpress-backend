<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événement annulé - TicketExpress</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
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
        .alert-box {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .alert-box strong {
            color: #721c24;
            display: block;
            margin-bottom: 8px;
        }
        .alert-box p {
            margin: 0;
            color: #721c24;
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
            color: #667eea;
        }
        .refund-box {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .refund-box p {
            margin: 5px 0;
            color: #155724;
            font-size: 15px;
        }
        .refund-box strong {
            color: #155724;
        }
        .support-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
            text-align: center;
        }
        .support-box p {
            margin: 5px 0;
            color: #0c5460;
            font-size: 15px;
        }
        .support-box a {
            color: #0c5460;
            text-decoration: none;
            font-weight: 600;
        }
        .support-box a:hover {
            text-decoration: underline;
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
            color: #667eea;
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
            <h1>❌ Événement annulé</h1>
            <p>TicketExpress</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $order->first_name }} {{ $order->last_name }}</strong>,</p>
            
            <p>Nous vous informons avec regret que l'événement suivant a été annulé :</p>

            <div class="event-details">
                <p><strong>📅 Événement :</strong> {{ $event->name }}</p>
                <p><strong>🗓️ Date prévue :</strong> {{ $event->start_date->format('d/m/Y à H:i') }}</p>
                <p><strong>📍 Lieu :</strong> {{ $event->location }}</p>
                @if($event->end_date)
                <p><strong>⏰ Fin prévue :</strong> {{ $event->end_date->format('d/m/Y à H:i') }}</p>
                @endif
            </div>

            <div class="alert-box">
                <strong>📋 Raison de l'annulation :</strong>
                <p>{{ $cancellationReason }}</p>
            </div>

            <p><strong>🔢 Votre commande :</strong></p>
            <p>
                Numéro de commande : <strong>{{ $order->order_number }}</strong><br>
                Nombre de tickets : <strong>{{ $order->tickets->count() }}</strong><br>
                Montant payé : <strong>{{ number_format($order->total_amount, 0, ',', ' ') }} XOF</strong>
            </p>

            <div class="refund-box">
                <p><strong>💰 Informations sur le remboursement</strong></p>
                <p>Montant du remboursement : <strong>{{ number_format($order->total_amount, 0, ',', ' ') }} XOF</strong></p>
                <p>⏱️ Délai de traitement : <strong>5 à 7 jours ouvrés</strong></p>
                <p>Le remboursement sera effectué sur le moyen de paiement utilisé lors de l'achat.</p>
            </div>

            <p>Nous nous excusons sincèrement pour ce désagrément et comprenons votre déception.</p>

            <div class="support-box">
                <p><strong>💬 Besoin d'aide ?</strong></p>
                <p>Pour toute question concernant votre remboursement, contactez-nous :</p>
                <p>📧 <a href="mailto:support@ticketexpress.tg">support@ticketexpress.tg</a></p>
                <p>📱 <a href="https://wa.me/22890000000">WhatsApp: +228 90 00 00 00</a></p>
            </div>

            <p>Nous espérons vous voir à d'autres événements sur TicketExpress.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe TicketExpress</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
            <p>
                <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}">Découvrir d'autres événements</a> |
                <a href="mailto:support@ticketexpress.tg">Support</a>
            </p>
        </div>
    </div>
</body>
</html>
