<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
</head>
<body>
    <p>Bonjour,</p>
    <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous :</p>

    <p>
        <a href="{{ url('/reset-password?token=' . $token) }}" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
            Réinitialiser mon mot de passe
        </a>
    </p>

    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.</p>

    <p>Merci,</p>
    <p>L'équipe de support</p>
</body>
</html>
