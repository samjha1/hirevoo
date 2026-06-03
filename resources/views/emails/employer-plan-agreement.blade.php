@php
    $planName = (string) ($plan['name'] ?? 'Subscription plan');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hirevo subscription agreement</title>
</head>
<body style="margin:0;padding:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:16px;line-height:1.5;color:#1a1a1a;background:#f5f5f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 16px;font-size:22px;font-weight:700;">Subscription agreement received</h1>
                            <p style="margin:0 0 16px;">Hi {{ $profile->company_name }},</p>
                            <p style="margin:0 0 16px;">
                                Thank you for choosing the <strong>{{ $planName }}</strong> plan on Hirevo.
                                Your cheque payment request is recorded and pending verification.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;">
                                <tr>
                                    <td style="padding:16px 18px;font-size:14px;">
                                        <p style="margin:0 0 8px;"><strong>Cheque number:</strong> {{ $chequeNumber }}</p>
                                        <p style="margin:0 0 8px;"><strong>Cheque date:</strong> {{ \Illuminate\Support\Carbon::parse($chequeDate)->format('d M Y') }}</p>
                                        <p style="margin:0 0 8px;"><strong>Base amount:</strong> ₹{{ number_format((float) ($amounts['base_amount'] ?? 0), 2) }}</p>
                                        <p style="margin:0 0 8px;"><strong>GST ({{ number_format((float) ($amounts['gst_rate'] ?? 18), 0) }}%):</strong> ₹{{ number_format((float) ($amounts['gst_amount'] ?? 0), 2) }}</p>
                                        <p style="margin:0;"><strong>Total payable:</strong> ₹{{ number_format((float) ($amounts['total_amount'] ?? 0), 2) }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 12px;font-size:15px;font-weight:600;">Signed agreement copy</p>
                            <div style="margin:0 0 20px;padding:16px 18px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;line-height:1.6;background:#fafafa;">
                                @include('hirevo.partials._subscription-agreement')
                            </div>

                            <p style="margin:0 0 16px;">
                                {{ config('hirevo_plans.checkout.pending_message') }}
                                Typical cheque clearance takes 3–5 business days after verification.
                            </p>

                            <p style="margin:0;font-size:14px;color:#666;">
                                — {{ config('mail.from.name') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
