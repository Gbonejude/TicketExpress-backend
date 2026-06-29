<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte organisateur approuvé - TicketExpress</title>
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
        .success-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .content {
            margin-bottom: 30px;
        }
        .company-box {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2E7D32;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #4CAF50;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            text-align: center;
            font-weight: bold;
        }
        .button:hover {
            background-color: #45a049;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .features {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .features h3 {
            color: #4CAF50;
            margin-top: 0;
        }
        .features ul {
            margin: 10px 0;
            padding-left: 25px;
        }
        .features li {
            margin: 8px 0;
            line-height: 1.6;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Félicitations !</h1>
            <p style="font-size: 18px; color: #4CAF50; margin: 10px 0;">Votre compte a été approuvé</p>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,</p>
            
            <p>Excellente nouvelle ! Votre compte organisateur sur <strong>TicketExpress</strong> a été approuvé par notre équipe.</p>

            <div class="company-box">
                <div class="company-name">{{ $organizer->company_name }}</div>
                @if($organizer->description)
                    <p style="color: #666; margin: 5px 0;">{{ $organizer->description }}</p>
                @endif
                @if($organizer->website)
                    <p style="margin: 5px 0;">
                        <a href="{{ $organizer->website }}" target="_blank" style="color: #4CAF50;">{{ $organizer->website }}</a>
                    </p>
                @endif
            </div>

            <p style="font-size: 16px; font-weight: bold; color: #2E7D32; text-align: center; margin: 30px 0;">
                🎉 Vous pouvez maintenant créer et gérer vos événements !
            </p>

            <div class="button-container">
                <a href="{{ $dashboardUrl }}" class="button">
                    🎯 Accéder à mon tableau de bord
                </a>
            </div>

            <div class="features">
                <h3>📋 Ce que vous pouvez faire maintenant :</h3>
                <ul>
                    <li>✨ <strong>Créer des événements</strong> et vendre des tickets en ligne</li>
                    <li>🎫 <strong>Gérer les types de tickets</strong> et les prix</li>
                    <li>📊 <strong>Suivre vos ventes</strong> en temps réel</li>
                    <li>👥 <strong>Gérer les participants</strong> et scanner les QR codes</li>
                    <li>💰 <strong>Suivre vos revenus</strong> et demander des retraits</li>
                    <li>📧 <strong>Communiquer avec vos participants</strong></li>
                </ul>
            </div>

            <div style="background-color: #E3F2FD; padding: 20px; border-radius: 5px; border-left: 4px solid #2196F3; margin: 20px 0;">
                <h3 style="color: #1976D2; margin-top: 0;">💡 Conseils pour démarrer</h3>
                <ol style="margin: 10px 0; padding-left: 20px;">
                    <li>Complétez votre profil organisateur</li>
                    <li>Créez votre premier événement</li>
                    <li>Configurez vos types de tickets</li>
                    <li>Partagez votre événement sur les réseaux sociaux</li>
                </ol>
            </div>

            <p style="margin-top: 30px;">
                Besoin d'aide pour démarrer ? Notre équipe support est là pour vous accompagner !
            </p>
        </div>

        <div class="footer">
            <p><strong>TicketExpress</strong></p>
            <p>La plateforme de réservation de tickets au Togo</p>
            <p style="margin-top: 15px;">
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
