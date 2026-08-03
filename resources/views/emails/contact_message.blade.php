<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact - TicketExpress</title>
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
            border-bottom: 2px solid #b9000a;
        }
        .header h1 {
            color: #b9000a;
            margin: 0;
            font-size: 22px;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .meta th {
            text-align: left;
            padding: 8px 12px 8px 0;
            color: #5b5f63;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
            vertical-align: top;
        }
        .meta td {
            padding: 8px 0;
            font-size: 15px;
        }
        .message {
            padding: 20px;
            background-color: #f8f9fa;
            border-left: 4px solid #b9000a;
            border-radius: 4px;
            white-space: pre-line;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e3e4;
            color: #5b5f63;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Nouveau message de contact</h1>
    </div>

    <table class="meta">
        <tr>
            <th>Nom</th>
            <td>{{ $senderName }}</td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td><a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></td>
        </tr>
        @if ($senderPhone !== '')
            <tr>
                <th>Téléphone</th>
                <td><a href="tel:{{ $senderPhone }}">{{ $senderPhone }}</a></td>
            </tr>
        @endif
        <tr>
            <th>Objet</th>
            <td>{{ $subjectLine }}</td>
        </tr>
    </table>

    <div class="message">{{ $body }}</div>

    <div class="footer">
        Répondre à cet e-mail écrit directement à {{ $senderName }}.
    </div>
</div>
</body>
</html>
