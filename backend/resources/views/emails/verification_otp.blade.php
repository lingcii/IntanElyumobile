<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account - Intan Elyu</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #0f172a; color: #e2e8f0; margin: 0; padding: 0; }
        .main-table { background-color: #1e293b; margin: 40px auto; max-width: 500px; border-radius: 20px; border: 1px solid rgba(56,189,248,0.2); overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .header { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 36px 20px; text-align: center; color: #ffffff; }
        .content { padding: 32px 28px; text-align: center; }
        .otp-box { background: rgba(56,189,248,0.1); border: 2px dashed #38bdf8; border-radius: 16px; padding: 20px; margin: 24px 0; font-size: 34px; font-weight: 900; letter-spacing: 10px; color: #38bdf8; font-family: monospace; }
        .footer { background: #0f172a; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <table class="main-table" align="center">
        <tr>
            <td class="header">
                <h1 style="margin:0; font-size:24px; font-weight:800;">Intan Elyu Verification</h1>
            </td>
        </tr>
        <tr>
            <td class="content">
                <h2 style="margin:0 0 10px; color:#f8fafc; font-size:20px;">Verify Your Email Address</h2>
                <p style="color:#94a3b8; font-size:14px; line-height:1.6; margin:0;">
                    Hello <strong>{{ $name }}</strong>,<br>
                    Welcome to Intan Elyu! Please use the 6-digit verification code below to activate your account.
                </p>
                <div class="otp-box">{{ $otp }}</div>
                <p style="color:#64748b; font-size:12px; margin:0;">This verification code will expire in 10 minutes. If you did not request this code, please ignore this email.</p>
            </td>
        </tr>
        <tr>
            <td class="footer">
                Need help? Contact Customer Support at <a href="mailto:support@intan-elyu.online" style="color:#38bdf8; text-decoration:none;">support@intan-elyu.online</a>
            </td>
        </tr>
    </table>
</body>
</html>
