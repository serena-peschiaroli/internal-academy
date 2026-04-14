<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Temporary Password</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #171717;">
    <p>Hello {{ $userName }},</p>

    <p>Your account has been created. Use this temporary password to log in:</p>

    <p style="font-size: 16px; font-weight: 700; letter-spacing: 0.3px;">
        {{ $temporaryPassword }}
    </p>

    <p>Login here: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>

    <p>At first login you will be asked to set a new secure password.</p>
</body>
</html>
