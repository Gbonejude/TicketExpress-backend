<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de remboursement</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1>Confirmation de remboursement</h1>
        
        <p>Bonjour,</p>
        
        <p>Nous confirmons le remboursement de votre billet pour l'événement <strong>{{ $event->title }}</strong>.</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Détails du remboursement :</h3>
            <ul>
                <li>Numéro de billet : <strong>{{ $ticket->ticket_number }}</strong></li>
                <li>Événement : <strong>{{ $event->title }}</strong></li>
                <li>Montant remboursé : <strong>{{ number_format((float) $refundAmount, 0, ',', ' ') }} FCFA</strong></li>
                <li>Date du remboursement : <strong>{{ $refundDate->format('d/m/Y à H:i') }}</strong></li>
            </ul>
        </div>
        
        <p>Le remboursement sera traité dans les 5 à 10 jours ouvrables selon votre banque.</p>
        
        <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        L'équipe {{ config('app.name') }}</p>
    </div>
</body>
</html>