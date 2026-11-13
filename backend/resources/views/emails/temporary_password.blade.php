<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Temporary Password - Intan Elyu</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f1f5f9; color: #0f172a; margin: 0; padding: 32px 16px; }
        .main-table { background-color: #ffffff; margin: 0 auto; max-width: 500px; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%); padding: 32px 24px; text-align: center; color: #ffffff; }
        .content { padding: 32px 28px; text-align: left; background: #ffffff; }
        .temp-pwd-box { background: #eff6ff; border: 2px dashed #2563eb; border-radius: 14px; padding: 18px 20px; margin: 22px 0; font-size: 26px; font-weight: 800; letter-spacing: 3px; color: #1e3a8a; font-family: monospace; text-align: center; word-break: break-all; -webkit-user-select: all; user-select: all; }
        .step-list { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px 16px 36px; margin: 18px 0; font-size: 13.5px; color: #334155; line-height: 1.6; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center; font-size: 11.5px; color: #94a3b8; }
    </style>
</head>
<body>
    <table class="main-table" align="center" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header">
                <h1 style="margin:0; font-size:22px; font-weight:800; letter-spacing:-0.3px;">Intan Elyu Tourism</h1>
                <p style="margin:4px 0 0; font-size:13px; color:rgba(255,255,255,0.9); font-weight:600;">Welcome to La Union!</p>
            </td>
        </tr>
        <tr>
            <td class="content">
                <p style="margin:0 0 12px; color:#0f172a; font-size:15px;">Hello <strong>{{ $name }}</strong>,</p>
                <p style="color:#475569; font-size:14px; line-height:1.6; margin:0 0 16px;">
                    Your Intan Elyu account has been created successfully! Here is your secure temporary login password:
                </p>
                
                <div class="temp-pwd-box">{{ $tempPassword }}</div>

                <div style="font-size: 13.5px; font-weight: 700; color: #1e293b; margin-top: 18px;">How to complete your registration:</div>
                <ol class="step-list">
                    <li>Open the Intan Elyu Mobile App.</li>
                    <li>Go to the <strong>Log In</strong> screen.</li>
                    <li>Enter your email (<strong>{{ $email }}</strong>) and this temporary password.</li>
                    <li>You will be immediately prompted to set your <strong>permanent password</strong>.</li>
                </ol>

                <p style="color:#64748b; font-size:12px; text-align:center; line-height:1.5; margin:20px 0 0;">
                    🔒 For security purposes, please do not share this temporary password with anyone.
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
