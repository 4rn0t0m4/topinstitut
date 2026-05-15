<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #db2777;">Confirmez votre revendication</h2>

    <p>Bonjour {{ $manager_name }},</p>

    <p>Vous avez demandé à devenir propriétaire de l'établissement <strong>{{ $establishment }}</strong> sur Top Institut.</p>

    <p>Pour valider votre demande, cliquez sur le bouton ci-dessous :</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; background-color: #db2777; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold;">
            Confirmer ma revendication
        </a>
    </p>

    <p style="font-size: 14px; color: #555;">Une fois votre email vérifié, notre équipe étudiera votre demande. Vous recevrez un email dès qu'elle sera traitée.</p>

    <p style="font-size: 13px; color: #888;">Si vous n'avez pas fait cette demande, ignorez simplement cet email.</p>

    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    <p style="font-size: 12px; color: #aaa;">Top Institut — Annuaire des instituts de beauté</p>
</body>
</html>
