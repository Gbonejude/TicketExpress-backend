<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation de commande</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1>Annulation de commande #{{ $order->order_number }}</h1>
        
        <p>Bonjour,</p>
        
        <p>Votre commande <strong>#{{ $order->order_number }}</strong> a été annulée le {{ $cancellationDate->format('d/m/Y à H:i') }}.</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Détails de l'annulation :</h3>
            <ul>
                <li>Numéro de commande : <strong>{{ $order->order_number }}</strong></li>
                <li>Date d'annulation : <strong>{{ $cancellationDate->format('d/m/Y à H:i') }}</strong></li>
                <li>Nombre d'articles : <strong>{{ $itemsCount }}</strong></li>
                <li>Statut de remboursement : <strong>{{ $refundStatus }}</strong></li>
            </ul>
        </div>
        
        @if($refundStatus === 'En attente de remboursement')
        <p>Le remboursement sera traité dans les 5 à 10 jours ouvrables.</p>
        @endif
        
        <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        L'équipe {{ config('app.name') }}</p>
    </div>
</body>
</html>