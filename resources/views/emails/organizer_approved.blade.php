<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte organisateur approuvé - TicketExpress</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            max-width: 600px;
            margin: 0 auto;
            padding: 24px 16px;
            background-color: #f8fafc;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .brand-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }
        .header h1 {
            color: #0f172a;
            margin: 0 0 6px 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header-sub {
            color: #16a34a;
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }
        .company-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            padding: 18px 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 700;
            color: #166534;
            margin-bottom: 4px;
        }
        .button-container {
            text-align: center;
            margin: 28px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #0f766e;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .section-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 20px;
            margin: 20px 0;
        }
        .section-title {
            margin: 0 0 12px 0;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .feature-list {
            margin: 0;
            padding-left: 18px;
            color: #334155;
            font-size: 14px;
        }
        .feature-list li {
            margin: 6px 0;
        }
        .steps-box {
            background-color: #f8fafc;
            border-left: 4px solid #0f766e;
            padding: 16px 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 13px;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand-name">TicketExpress</div>
            <h1>Compte organisateur approuvé</h1>
            <p class="header-sub">Votre demande a été validée par notre équipe</p>
        </div>

        <p>Bonjour <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,</p>
        <p>Votre compte organisateur sur <strong>TicketExpress</strong> est désormais actif et validé.</p>

        <div class="company-box">
            <div class="company-name">{{ $organizer->company_name }}</div>
            @if($organizer->description)
                <p style="color: #475569; margin: 4px 0 0 0; font-size: 14px;">{{ $organizer->description }}</p>
            @endif
            @if($organizer->website)
                <p style="margin: 6px 0 0 0; font-size: 13px;">
                    <a href="{{ $organizer->website }}" target="_blank" style="color: #0f766e; text-decoration: underline;">{{ $organizer->website }}</a>
                </p>
            @endif
        </div>

        <div class="button-container">
            <a href="{{ $dashboardUrl }}" class="button">
                Accéder à votre espace organisateur
            </a>
        </div>

        <div class="section-card">
            <div class="section-title">Fonctionnalités disponibles :</div>
            <ul class="feature-list">
                <li>Création et gestion de vos événements en ligne</li>
                <li>Configuration des types de billets et tarifs</li>
                <li>Suivi des ventes et statistiques en temps réel</li>
                <li>Contrôle d'accès et scan des billets par QR code</li>
                <li>Gestion des demandes de retraits de vos recettes</li>
                <li>Communication directe avec vos participants</li>
            </ul>
        </div>

        <div class="steps-box">
            <div style="font-weight: 600; color: #0f172a; margin-bottom: 8px;">Premières étapes recommandées :</div>
            <ol style="margin: 0; padding-left: 18px; color: #334155; font-size: 13px;">
                <li style="margin: 4px 0;">Complétez les informations de votre profil organisateur</li>
                <li style="margin: 4px 0;">Créez votre premier événement et ajoutez vos visuels</li>
                <li style="margin: 4px 0;">Définissez vos quotas et types de billets</li>
                <li style="margin: 4px 0;">Publiez et diffusez votre lien de billetterie</li>
            </ol>
        </div>

        <p style="font-size: 14px; color: #475569;">
            Notre équipe d'assistance reste à votre entière disposition pour vous accompagner dans le lancement de vos événements.
        </p>

        <div class="footer">
            <p><strong>TicketExpress</strong> — Plateforme de billetterie au Togo</p>
            <p>Assistance organisateurs : support@ticketexpress.tg</p>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 14px;">© {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
