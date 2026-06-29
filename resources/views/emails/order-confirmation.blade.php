<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1>Confirmation de commande #{{ $order->order_number }}</h1>
        
        <p>Bonjour,</p>
        
        <p>Merci pour votre commande ! Voici les détails :</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Détails de la commande :</h3>
            <ul>
                <li>Numéro de commande : <strong>{{ $order->order_number }}</strong></li>
                <li>Date : <strong>{{ $orderDate->format('d/m/Y à H:i') }}</strong></li>
                <li>Montant total : <strong>{{ number_format($totalAmount, 0, ',', ' ') }} FCFA</strong></li>
                <li>Nombre d'articles : <strong>{{ $items->count() }}</strong></li>
            </ul>
            
            <h4>Articles :</h4>
            <ul>
                @foreach($items as $item)
                <li>{{ $item->quantity }} × {{ $item->ticketType->name }} - {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</li>
                @endforeach
            </ul>
        </div>
        
        <p>Vous recevrez un email séparé avec vos billets une fois le paiement confirmé.</p>
        
        <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        L'équipe {{ config('app.name') }}</p>
    </div>
</body>
</html>