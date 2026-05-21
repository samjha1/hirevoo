<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification OTP</title>
</head>
<body style="margin:0;padding:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:16px;line-height:1.5;color:#1a1a1a;background:#f5f5f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 16px;font-size:22px;font-weight:700;">Verify Your Email</h1>
                            <p style="margin:0 0 24px;">Hi {{ $user->name }},</p>
                            <p style="margin:0 0 16px;">
                                Thank you for signing up on Hirevo Employer Dashboard. To activate your account and start posting jobs, please verify your email address.
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#666;">
                                Your One-Time Password (OTP) is:
                            </p>
                            
                            <!-- OTP Display -->
                            <div style="margin:32px 0;padding:20px;background:#f0f4ff;border-radius:8px;text-align:center;">
                                <div style="font-size:36px;font-weight:700;letter-spacing:8px;color:#2563eb;font-family:monospace;">
                                    {{ $otp }}
                                </div>
                            </div>
                            
                            <p style="margin:0 0 24px;font-size:14px;color:#666;">
                                This OTP is valid for <strong>10 minutes</strong>. If you didn't request this, please ignore this email.
                            </p>
                            
                            <p style="margin:0 0 24px;">
                                <a href="{{ route('verify-email') }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 24px;border-radius:999px;font-weight:600;">Enter OTP</a>
                            </p>
                            
                            <div style="margin:24px 0 0;padding:24px 0;border-top:1px solid #eee;border-bottom:1px solid #eee;">
                                <p style="margin:0 0 8px;font-size:13px;color:#999;font-weight:600;">SECURITY NOTE:</p>
                                <p style="margin:0;font-size:13px;color:#666;">
                                    Never share this OTP with anyone. Hirevo will never ask for your OTP via email or phone call.
                                </p>
                            </div>
                            
                            <p style="margin:24px 0 0;font-size:14px;color:#666;">
                                — Hirevo Team<br>
                                <a href="https://hirevo.com" style="color:#2563eb;text-decoration:none;">Visit our website</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
