<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Код подтверждения регистрации</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; border:1px solid #e5e7eb;">
        <h2 style="margin-top:0;">Здравствуйте, {{ $user->name }}!</h2>

        <p>Спасибо за регистрацию.</p>
        <p>Введите этот код для подтверждения аккаунта:</p>

        <div style="font-size:32px; font-weight:bold; letter-spacing:6px; margin:24px 0;">
            {{ $code }}
        </div>

        <p>Код действует 10 минут.</p>
    </div>
</body>
</html>