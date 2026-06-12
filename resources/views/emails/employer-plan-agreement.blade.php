@php
    $planName = (string) ($plan['name'] ?? 'Subscription plan');
    $paymentDate = (string) ($payment->meta['payment_date'] ?? $payment->meta['cheque_date'] ?? $chequeDate);
    $utrReference = (string) ($payment->payment_reference ?? $chequeNumber);
    $hasDiscount = ! empty($amounts['coupon_applied']) && (float) ($amounts['discount_amount'] ?? 0) > 0;
    $discountLabel = 'Discount';
    if (! empty($amounts['coupon_code'])) {
        $discountLabel .= ' ('.$amounts['coupon_code'].')';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hirevo subscription — payment received</title>
</head>
<body style="margin:0;padding:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:16px;line-height:1.6;color:#1a1a1a;background:#f5f5f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
                    <tr>
                        <td style="height:4px;background:linear-gradient(90deg,#2563eb,#3b82f6);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 28px;">
                            <p style="margin:0 0 20px;font-size:13px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#2563eb;">Hirevo</p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
                                <tr>
                                    <td style="padding:6px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:999px;font-size:13px;font-weight:600;color:#b45309;">
                                        Payment pending verification
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:0 0 12px;font-size:24px;font-weight:700;line-height:1.3;color:#111827;">Thank you for your payment</h1>
                            <p style="margin:0 0 24px;font-size:15px;color:#4b5563;">
                                Hi {{ $profile->company_name }},
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#374151;">
                                We have received your net banking payment for the <strong>{{ $planName }}</strong> plan.
                                Our team will verify the transaction shortly.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                                <tr>
                                    <td colspan="2" style="padding:14px 18px;background:#f8fafc;border-bottom:1px solid #e5e7eb;font-size:13px;font-weight:600;letter-spacing:.03em;text-transform:uppercase;color:#6b7280;">
                                        Payment summary
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;width:42%;">Plan</td>
                                    <td style="padding:12px 18px;font-size:14px;font-weight:600;color:#111827;border-bottom:1px solid #f3f4f6;">{{ $planName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;">UTR / reference</td>
                                    <td style="padding:12px 18px;font-size:14px;font-weight:600;color:#111827;border-bottom:1px solid #f3f4f6;font-family:ui-monospace,Consolas,monospace;">{{ $utrReference }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;">Payment date</td>
                                    <td style="padding:12px 18px;font-size:14px;font-weight:600;color:#111827;border-bottom:1px solid #f3f4f6;">{{ \Illuminate\Support\Carbon::parse($paymentDate)->format('d M Y') }}</td>
                                </tr>
                                @if ($hasDiscount)
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;">List price</td>
                                    <td style="padding:12px 18px;font-size:14px;color:#111827;border-bottom:1px solid #f3f4f6;">₹{{ number_format((float) ($amounts['original_base_amount'] ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;">{{ $discountLabel }}</td>
                                    <td style="padding:12px 18px;font-size:14px;color:#059669;border-bottom:1px solid #f3f4f6;">−₹{{ number_format((float) ($amounts['discount_amount'] ?? 0), 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;">Base amount</td>
                                    <td style="padding:12px 18px;font-size:14px;color:#111827;border-bottom:1px solid #f3f4f6;">₹{{ number_format((float) ($amounts['base_amount'] ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px;font-size:14px;color:#6b7280;border-bottom:1px solid #f3f4f6;">GST ({{ number_format((float) ($amounts['gst_rate'] ?? 18), 0) }}%)</td>
                                    <td style="padding:12px 18px;font-size:14px;color:#111827;border-bottom:1px solid #f3f4f6;">₹{{ number_format((float) ($amounts['gst_amount'] ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 18px;font-size:14px;font-weight:600;color:#111827;">Total payable</td>
                                    <td style="padding:14px 18px;font-size:16px;font-weight:700;color:#2563eb;">₹{{ number_format((float) ($amounts['total_amount'] ?? 0), 2) }}</td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;background:#f0f4ff;border-radius:10px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#1e40af;">What happens next</p>
                                        <p style="margin:0;font-size:14px;color:#374151;line-height:1.65;">
                                            We will confirm your payment within 1–2 business days. Once verified, we will activate your subscription and email you a PDF copy of the signed agreement.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.5;">
                                Reference #{{ $payment->id }} · No action is required from you at this time.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 28px;border-top:1px solid #f3f4f6;background:#fafafa;">
                            <p style="margin:0;font-size:14px;color:#6b7280;">
                                — {{ config('mail.from.name') }}<br>
                                <span style="font-size:13px;color:#9ca3af;">Questions? Reply to this email and we will be happy to help.</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
