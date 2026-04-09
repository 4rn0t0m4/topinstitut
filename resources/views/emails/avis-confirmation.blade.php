<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #b5838d;">Confirmez votre avis</h2>

    <p>Bonjour {{ $pseudo }},</p>

    <p>Vous avez déposé un avis sur <strong>{{ $etablissement }}</strong> via Top Institut.</p>

    <p>Pour confirmer votre avis, veuillez cliquer sur le bouton ci-dessous :</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; background-color: #b5838d; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold;">
            Confirmer mon avis
        </a>
    </p>

    <p style="font-size: 13px; color: #888;">Si vous n'avez pas déposé cet avis, ignorez simplement cet email.</p>

    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    <p style="font-size: 12px; color: #aaa;">Top Institut — Annuaire des instituts de beauté</p>
</body>
</html>
