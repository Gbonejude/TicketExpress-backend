<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suspension de compte organisateur</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1>Suspension de votre compte organisateur</h1>
        
        <p>Bonjour {{ $user->first_name }},</p>
        
        <p>Nous regrettons de vous informer que votre compte organisateur a été suspendu à partir du {{ $suspensionDate->format('d/m/Y') }}.</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Conséquences :</h3>
            <ul>
                <li>Tous vos événements ({{ $eventsCount }}) ont été mis en brouillon</li>
                <li>Vous ne pouvez plus créer de nouveaux événements</li>
                <li>Les ventes de billets pour vos événements sont suspendues</li>
                <li>Votre accès au tableau de bord organisateur est restreint</li>
            </ul>
        </div>
        
        <p>Si vous pensez qu'il s'agit d'une erreur ou si vous avez des questions concernant cette suspension, veuillez contacter notre support.</p>
        
        <p>Cordialement,<br>
        L'équipe {{ config('app.name') }}</p>
    </div>
</body>
</html>