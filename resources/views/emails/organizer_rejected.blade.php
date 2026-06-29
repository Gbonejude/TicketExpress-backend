<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'inscription - TicketExpress</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
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
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #856404;
            display: block;
            margin-bottom: 8px;
        }
        .info-box p {
            margin: 0;
            color: #856404;
            font-size: 15px;
        }
        .support-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .support-box p {
            margin: 5px 0;
            color: #0c5460;
            font-size: 15px;
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
            <h1>TicketExpress</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $organizer->user->first_name }} {{ $organizer->user->last_name }}</strong>,</p>
            
            <p>Nous avons examiné votre demande d'inscription en tant qu'organisateur d'événements sur la plateforme TicketExpress.</p>

            <div class="info-box">
                <strong>📋 Raison :</strong>
                <p>{{ $rejectionReason }}</p>
            </div>

            <p><strong>Informations de votre société :</strong></p>
            <p>
                📢 Société : <strong>{{ $organizer->company_name }}</strong><br>
                ✉️ Email : {{ $organizer->user->email }}<br>
                📞 Téléphone : {{ $organizer->user->phone }}
            </p>

            <div class="support-box">
                <p><strong>💡 Que faire maintenant ?</strong></p>
                <p>Si vous souhaitez obtenir plus d'informations ou soumettre une nouvelle demande avec des informations supplémentaires, n'hésitez pas à nous contacter.</p>
                <p>📧 Email : <a href="mailto:support@ticketexpress.tg">support@ticketexpress.tg</a></p>
                <p>📱 WhatsApp : <a href="https://wa.me/22890000000">+228 90 00 00 00</a></p>
            </div>

            <p>Nous vous remercions de votre intérêt pour TicketExpress.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe TicketExpress</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
            <p>
                <a href="{{ config('app.frontend_url', 'https://app.ticketexpress.tg') }}">Visitez notre site</a> |
                <a href="mailto:support@ticketexpress.tg">Support</a>
            </p>
        </div>
    </div>
</body>
</html>
