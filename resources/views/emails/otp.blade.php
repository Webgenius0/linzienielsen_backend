<html>
    <body>
        <h3>Hello, {{ $user->last_name }}</h3>
        <p>Thank you for registering with us. We are excited to have you on board!</p>
        <p>Your OTP: <strong>{{ $otp }}</strong></p>
        <p>Best regards,<br>Thanks</p>
    </body>
</html>