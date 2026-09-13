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
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            color: #e2e8f0;
            padding: 1.5rem;
        }

        .card {
            max-width: 480px;
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
        }

        .icon--expired {
            background: rgba(239, 68, 68, 0.15);
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        .icon--limit {
            background: rgba(251, 191, 36, 0.15);
            border: 2px solid rgba(251, 191, 36, 0.3);
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #fff;
        }

        .description {
            font-size: 1rem;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            font-size: 0.9375rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn--primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
        }

        .btn--primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }

        .footer {
            margin-top: 2rem;
            font-size: 0.8125rem;
            color: #64748b;
        }

        .footer a {
            color: #818cf8;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon {{ $iconClass ?? 'icon--expired' }}">
            {{ $icon ?? '⏰' }}
        </div>

        <h1>{{ $title ?? 'Lien expiré' }}</h1>

        <p class="description">
            {{ $message ?? 'Ce lien de téléchargement a expiré. Veuillez contacter le support pour obtenir un nouveau lien.' }}
        </p>

        @if(!empty($actionUrl))
            <a href="{{ $actionUrl }}" class="btn btn--primary">
                {{ $actionLabel ?? 'Retour à l\'accueil' }}
            </a>
        @endif

        <div class="footer">
            <p>Besoin d'aide ? <a href="mailto:support@ticketexpress.com">Contactez le support</a></p>
        </div>
    </div>
</body>
</html>
