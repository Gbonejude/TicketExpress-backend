<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - TicketExpress</title>
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
            border-bottom: 2px solid #FF9800;
        }
        .header h1 {
            color: #FF9800;
            margin: 0;
            font-size: 28px;
        }
        .lock-icon {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #FF9800;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background-color: #F57C00;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .warning {
            background-color: #FFF3CD;
            border-left: 4px solid #FFC107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning strong {
            color: #F57C00;
        }
        .info-box {
            background-color: #E3F2FD;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .link-text {
            word-break: break-all;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="lock-icon">🔐</div>
            <h1>Réinitialisation de mot de passe</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $firstName }} {{ $lastName }}</strong>,</p>
            
            <p>Vous avez demandé la réinitialisation de votre mot de passe sur <strong>TicketExpress</strong>.</p>

            <div class="info-box">
                <p style="margin: 0;">
                    <strong>ℹ️ Information:</strong><br>
                    Pour des raisons de sécurité, ce lien est valable pendant <strong>60 minutes</strong> seulement.
                </p>
            </div>

            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="button">
                    Réinitialiser mon mot de passe
                </a>
            </div>

            <p style="font-size: 12px; color: #666;">
                Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
            </p>
            <div class="link-text">
                {{ $resetUrl }}
            </div>

            <div class="warning">
                <strong>⚠️ Attention :</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Si vous n'avez pas demandé cette réinitialisation, <strong>ignorez cet email</strong></li>
                    <li>Votre mot de passe actuel reste inchangé tant que vous ne cliquez pas sur le lien</li>
                    <li>Ne partagez jamais ce lien avec quelqu'un d'autre</li>
                </ul>
            </div>

            <p style="margin-top: 20px;">
                Pour votre sécurité, nous vous recommandons de :
            </p>
            <ul>
                <li>Choisir un mot de passe fort (minimum 8 caractères)</li>
                <li>Utiliser une combinaison de lettres, chiffres et symboles</li>
                <li>Ne pas réutiliser un ancien mot de passe</li>
            </ul>
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
            <p style="font-size: 11px; color: #999; margin-top: 10px;">
                Ceci est un email automatique, merci de ne pas y répondre directement.
            </p>
        </div>
    </div>
</body>
</html>
