<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre demande de retrait - TicketExpress</title>
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
            font-size: 24px;
        }
        .icon {
            font-size: 50px;
            margin-bottom: 10px;
        }
        .amount {
            font-size: 26px;
            font-weight: bold;
            color: #146c43;
            text-align: center;
            margin: 10px 0 20px;
        }
        .details {
            background-color: #f9f9f9;
            border-left: 4px solid #FF9800;
            padding: 18px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .details p {
            margin: 6px 0;
        }
        .reference {
            font-family: monospace;
            background-color: #ffffff;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-block;
        }
        .refused {
            background-color: #FFF3CD;
            border-left: 4px solid #FFC107;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">
                @if ($status === \App\Enums\WithdrawalStatus::PAID)
                    💸
                @elseif ($status === \App\Enums\WithdrawalStatus::REJECTED)
                    ⚠️
                @else
                    ✅
                @endif
            </div>
            <h1>Demande de retrait — {{ $statusLabel }}</h1>
        </div>

        <div class="content">
            <p>Bonjour <strong>{{ $companyName }}</strong>,</p>

            <div class="amount">{{ $amount }} FCFA</div>

            @if ($status === \App\Enums\WithdrawalStatus::PAID)
                <p>Le versement a été effectué sur le numéro que vous avez indiqué :</p>
            @elseif ($status === \App\Enums\WithdrawalStatus::APPROVED)
                <p>
                    Votre demande est approuvée. Le versement part sur le numéro que vous
                    avez indiqué :
                </p>
            @else
                <p>Votre demande de retrait n'a pas été retenue.</p>
            @endif

            @if ($status !== \App\Enums\WithdrawalStatus::REJECTED)
                <div class="details">
                    <p><strong>Numéro :</strong> {{ $phone }}</p>
                    <p><strong>Opérateur :</strong> {{ $method }}</p>
                    @if ($reference)
                        <p>
                            <strong>Référence du transfert :</strong><br>
                            <span class="reference">{{ $reference }}</span>
                        </p>
                    @endif
                </div>
            @endif

            @if ($notes)
                <div class="refused">
                    <strong>Précision de l'équipe :</strong><br>
                    {{ $notes }}
                </div>
            @endif

            @if ($status === \App\Enums\WithdrawalStatus::PAID)
                <p style="font-size: 13px; color: #666;">
                    Si vous ne voyez rien arriver sur ce numéro, répondez en citant la
                    référence ci-dessus : elle permet de retrouver le transfert chez
                    l'opérateur.
                </p>
            @endif
        </div>

        <div class="footer">
            <p>Ce message est automatique, merci de ne pas y répondre directement.</p>
            <p><strong>TicketExpress</strong></p>
        </div>
    </div>
</body>
</html>
