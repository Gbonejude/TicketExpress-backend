<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'inscription - TicketExpress</title>
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
            font-size: 14px;
            font-weight: 700;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        .title {
            color: #0f172a;
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
        }
        .header-sub {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }
        .ticket-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px 24px;
            margin: 24px 0;
        }
        .ticket-card-title {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .alert-box {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 14px 18px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 14px;
            color: #7c2d12;
        }
        .alert-box strong {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .support-box {
            background-color: #f1f5f9;
            border-left: 4px solid #0f766e;
            padding: 14px 18px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 13px;
            color: #334155;
        }
        .footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 13px;
        }
        .footer p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand-name">TicketExpress</div>
            <h1 class="title">Demande d'inscription</h1>
            <p class="header-sub">Décision concernant votre demande organisateur</p>
        </div>

        <p>Bonjour <strong>{{ $organizer->user->first_name }} {{ $organizer->user->last_name }}</strong>,</p>
        <p>Nous avons examiné votre demande d'inscription en tant qu'organisateur d'événements sur la plateforme TicketExpress.</p>

        <div class="ticket-card">
            <div class="ticket-card-title">Informations de votre société</div>
            <table style="width:100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; width: 45%;">Société :</td>
                    <td style="padding: 6px 0; font-weight: 600; color: #0f172a; text-align: right;">{{ $organizer->company_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; border-top: 1px dashed #e2e8f0;">Email :</td>
                    <td style="padding: 6px 0; font-weight: 600; color: #0f172a; text-align: right; border-top: 1px dashed #e2e8f0;">{{ $organizer->user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; border-top: 1px dashed #e2e8f0;">Téléphone :</td>
                    <td style="padding: 6px 0; font-weight: 600; color: #0f172a; text-align: right; border-top: 1px dashed #e2e8f0;">{{ $organizer->user->phone }}</td>
                </tr>
            </table>
        </div>

        <div class="alert-box">
            <strong>Raison du refus :</strong>
            {{ $rejectionReason }}
        </div>

        <div class="support-box">
            <strong>Que faire maintenant ?</strong><br>
            Si vous souhaitez obtenir plus d'informations ou soumettre une nouvelle demande avec des informations supplémentaires, n'hésitez pas à nous contacter.<br><br>
            Email : <a href="mailto:support@ticketexpress.tg" style="color: #0f766e;">support@ticketexpress.tg</a><br>
            WhatsApp : <a href="https://wa.me/22890000000" style="color: #0f766e;">+228 90 00 00 00</a>
        </div>

        <p>Nous vous remercions de votre intérêt pour TicketExpress.</p>

        <div class="footer">
            <p><strong>TicketExpress</strong> — Plateforme de billetterie en ligne</p>
            <p>Assistance : support@ticketexpress.tg</p>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 12px;">&copy; {{ date('Y') }} TicketExpress. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
