<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Lien expiré' }} — TicketExpress</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 1.5rem;
        }

        .card {
            max-width: 460px;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1.25rem;
        }

        h1 {
            font-size: 1.375rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #111;
        }

        .description {
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #666;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.75rem;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            text-decoration: none;
            background: #111;
            color: #fff;
        }

        .footer {
            margin-top: 1.75rem;
            font-size: 0.8125rem;
            color: #999;
        }

        .footer a {
            color: #555;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $icon ?? '⏰' }}</div>

        <h1>{{ $title ?? 'Lien expiré' }}</h1>

        <p class="description">
            {{ $message ?? 'Ce lien de téléchargement a expiré. Veuillez contacter le support pour obtenir un nouveau lien.' }}
        </p>

        @if(!empty($actionUrl))
            <a href="{{ $actionUrl }}" class="btn">
                {{ $actionLabel ?? "Retour à l'accueil" }}
            </a>
        @endif

        <div class="footer">
            <p>Besoin d'aide ? <a href="mailto:support@ticketexpress.com">Contactez le support</a></p>
        </div>
    </div>
</body>
</html>
