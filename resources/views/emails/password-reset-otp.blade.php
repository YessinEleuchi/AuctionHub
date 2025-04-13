<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body>
    <p>Hello {{ $user->name }},</p>
    <p>Use this OTP to reset your password: <strong>{{ $otp }}</strong></p>

</body>
</html>
