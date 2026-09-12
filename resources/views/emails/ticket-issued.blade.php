<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre billet</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1>Votre billet pour {{ $event->title }}</h1>
        
        <p>Bonjour,</p>
        
        <p>Votre billet pour l'événement <strong>{{ $event->title }}</strong> est prêt !</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <h3>Détails du billet :</h3>
            <p><strong>Numéro :</strong> {{ $ticket->ticket_number }}</p>
            <p><strong>Type :</strong> {{ $ticketType->name }}</p>
            <p><strong>Prix :</strong> {{ number_format((float) $ticketType->price, 0, ',', ' ') }} FCFA</p>
            <p><strong>Méthode d'accès :</strong> {{ $accessMethod }}</p>
            
            @if($qrCode)
            <div style="margin: 20px 0;">
                <p><strong>Code QR :</strong></p>
                <div style="background: white; padding: 10px; display: inline-block;">
                    <!-- QR code serait généré ici -->
                    <div style="width: 150px; height: 150px; background: #eee; display: flex; align-items: center; justify-content: center;">
                        QR Code
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <p>Présentez ce billet à l'entrée de l'événement.</p>
        
        <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        L'équipe {{ config('app.name') }}</p>
    </div>
</body>
</html>