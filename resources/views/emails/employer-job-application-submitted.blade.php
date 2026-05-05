@php
    $company = trim((string) ($job->company_name ?? ''));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application received</title>
</head>
<body style="margin:0;padding:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:16px;line-height:1.5;color:#1a1a1a;background:#f5f5f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 16px;font-size:22px;font-weight:700;">Application received</h1>
                            <p style="margin:0 0 16px;">Hi {{ $user->name }},</p>
                            <p style="margin:0 0 16px;">
                                Thank you for applying to <strong>{{ $job->title }}</strong>@if($company !== '') at <strong>{{ $company }}</strong>@endif.
                            </p>
                            <p style="margin:0 0 24px;">We have recorded your application. The hiring team may contact you if your profile is a good match.</p>
                            <p style="margin:0 0 24px;">
                                <a href="{{ route('job-openings') }}" style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:12px 20px;border-radius:999px;font-weight:600;">Browse more openings</a>
                            </p>
                            @if(!empty($job->apply_link))
                                <p style="margin:0;font-size:14px;color:#555;">If this role included an external apply link, complete any extra steps on the employer’s site when you’re ready.</p>
                            @endif
                            <p style="margin:24px 0 0;padding-top:24px;border-top:1px solid #eee;font-size:14px;color:#666;">
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
