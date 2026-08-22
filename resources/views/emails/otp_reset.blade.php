<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); text-align: center;">
        
        <h2 style="color: #333; margin-bottom: 20px;">Password Reset Request</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.5; margin-bottom: 30px;">
            We received a request to reset your password. Use the following 6-digit verification code to proceed:
        </p>
        
        <div style="background-color: #f8f9fa; border: 1px dashed #ccc; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: inline-block;">
            <span style="font-size: 32px; font-weight: bold; color: #e32636; letter-spacing: 5px;">{{ $otp }}</span>
        </div>
        
        <p style="color: #777; font-size: 14px; margin-bottom: 10px;">
            If you did not request a password reset, please ignore this email or contact support if you have concerns.
        </p>
        
        <p style="color: #999; font-size: 12px; margin-top: 40px;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
