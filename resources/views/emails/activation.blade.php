<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body>
    <p>Hello {{ $user->name }},</p>
    <p>Your email verification code is: <strong>{{ $otp }}</strong></p>
    <p>This code will expire in 10 minutes.</p>

</body>
</html>
