<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants - TicketExpress</title>
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
            font-size: 26px;
        }
        .key-icon {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .credentials {
            background-color: #f9f9f9;
            border-left: 4px solid #FF9800;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .credentials p {
            margin: 8px 0;
        }
        .credentials .value {
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            background-color: #ffffff;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-block;
            margin-top: 4px;
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
            <div class="key-icon">🔑</div>
            <h1>Votre compte TicketExpress</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $userName }}</strong>,</p>

            <p>
                Un compte vient d'être créé pour vous sur <strong>TicketExpress</strong>.
                Voici vos identifiants de connexion :
            </p>

            <div class="credentials">
                <p>
                    <strong>Adresse email</strong><br>
                    <span class="value">{{ $email }}</span>
                </p>
                <p style="margin-top: 16px;">
                    <strong>Mot de passe provisoire</strong><br>
                    <span class="value">{{ $password }}</span>
                </p>
            </div>

            <div class="warning">
                <strong>⚠️ À faire dès votre première connexion :</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Changez ce mot de passe provisoire</li>
                    <li>Ne le communiquez à personne, pas même à un administrateur</li>
                </ul>
            </div>

            <p>
                Si vous n'attendiez pas la création de ce compte, signalez-le à l'équipe
                TicketExpress et ne vous connectez pas.
            </p>
        </div>

        <div class="footer">
            <p>Ce message est automatique, merci de ne pas y répondre.</p>
            <p><strong>TicketExpress</strong></p>
        </div>
    </div>
</body>
</html>
