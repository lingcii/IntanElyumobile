<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account - Intan Elyu</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f1f5f9; color: #0f172a; margin: 0; padding: 32px 16px; }
        .main-table { background-color: #ffffff; margin: 0 auto; max-width: 500px; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%); padding: 32px 24px; text-align: center; color: #ffffff; }
        .content { padding: 32px 28px; text-align: left; background: #ffffff; }
        .otp-box { background: #eff6ff; border: 2px dashed #3b82f6; border-radius: 14px; padding: 20px; margin: 24px 0; font-size: 34px; font-weight: 900; letter-spacing: 10px; color: #1e3a8a; font-family: monospace; text-align: center; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center; font-size: 11.5px; color: #94a3b8; }
    </style>
</head>
<body>
    <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header">
                <h1 style="margin:0; font-size:22px; font-weight:800; letter-spacing:-0.3px;">Intan Elyu Tourism</h1>
                <p style="margin:4px 0 0; font-size:13px; color:rgba(255,255,255,0.9); font-weight:600;">Account Verification Code</p>
            </td>
        </tr>
        <tr>
            <td class="content">
                <p style="margin:0 0 12px; color:#0f172a; font-size:15px;">Hello <strong>{{ $name }}</strong>,</p>
                <p style="color:#475569; font-size:14px; line-height:1.6; margin:0 0 20px;">
                    Welcome to Intan Elyu! Please use the 6-digit verification code below to activate and verify your account:
                </p>
                <div class="otp-box">{{ $otp }}</div>
                <p style="color:#64748b; font-size:12.5px; text-align:center; line-height:1.5; margin:0;">
                    This verification code will expire in <strong style="color:#0f172a;">10 minutes</strong>.<br>If you did not request this code, please ignore this email.
                </p>
            </td>
        </tr>
        <tr>
            <td class="footer">
                Need help? Contact Customer Support at <a href="mailto:support@intan-elyu.online" style="color:#2563eb; text-decoration:none; font-weight:700;">support@intan-elyu.online</a>
            </td>
        </tr>
    </table>
</body>
</html>
