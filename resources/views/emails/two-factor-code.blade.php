<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Код подтверждения входа</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; border:1px solid #e5e7eb;">
        <h2 style="margin-top:0;">Здравствуйте, {{ $user->name }}!</h2>

        <p>Для входа в аккаунт используйте код подтверждения:</p>

        <div style="font-size:32px; font-weight:bold; letter-spacing:6px; margin:24px 0; color:#111827;">
            {{ $code }}
        </div>

        <p>Код действует 10 минут.</p>
        <p>Если это были не вы, просто проигнорируйте письмо.</p>
    </div>
</body>
</html>