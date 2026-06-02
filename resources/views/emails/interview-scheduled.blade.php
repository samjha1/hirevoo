@php
    /** @var \App\Models\EmployerJobApplication $application */
    /** @var \App\Models\InterviewSchedule $interview */
    /** @var 'candidate'|'employer' $recipientRole */

    $job = $application->employerJob;
    $candidate = $application->user;
    $employer = $job?->user;

    $jobTitle = $job?->title ?? 'Job';
    $candidateName = $candidate?->name ?? 'Candidate';
    $employerName = $employer?->name ?? 'Employer';

    $tz = config('app.timezone') ?: 'UTC';
    $when = $interview->scheduled_at
        ? $interview->scheduled_at->copy()->timezone($tz)->format('d M Y, g:i A')
        : '—';

    $duration = (int) ($interview->duration_minutes ?? 30);
    $typeLabel = $interview->interview_type === 'in_person' ? 'In-Person' : ucfirst((string) $interview->interview_type);

    $isCandidate = ($recipientRole ?? 'candidate') === 'candidate';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interview scheduled</title>
</head>
<body style="margin:0;padding:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:16px;line-height:1.5;color:#1a1a1a;background:#f5f5f5;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                <tr>
                    <td>
                        <h1 style="margin:0 0 16px;font-size:22px;font-weight:800;">
                            Interview scheduled
                        </h1>

                        @if($isCandidate)
                            <p style="margin:0 0 16px;">Hi {{ $candidateName }},</p>
                            <p style="margin:0 0 20px;">
                                Your interview for <strong>{{ $jobTitle }}</strong> has been scheduled.
                            </p>
                        @else
                            <p style="margin:0 0 16px;">Hi {{ $employerName }},</p>
                            <p style="margin:0 0 20px;">
                                An interview has been scheduled with <strong>{{ $candidateName }}</strong> for <strong>{{ $jobTitle }}</strong>.
                            </p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:16px 16px;margin:0 0 20px;">
                            <tr>
                                <td style="padding:0;">
                                    <p style="margin:0 0 8px;font-size:13px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Meeting details</p>

                                    <p style="margin:0 0 10px;">
                                        <span style="display:inline-block;min-width:110px;color:#334155;font-weight:700;">Date & time</span>
                                        <span style="color:#0f172a;font-weight:700;">{{ $when }}</span>
                                        <span style="color:#64748b;">({{ $tz }})</span>
                                    </p>

                                    <p style="margin:0 0 10px;">
                                        <span style="display:inline-block;min-width:110px;color:#334155;font-weight:700;">Duration</span>
                                        <span style="color:#0f172a;">{{ $duration }} mins</span>
                                    </p>

                                    <p style="margin:0 0 10px;">
                                        <span style="display:inline-block;min-width:110px;color:#334155;font-weight:700;">Type</span>
                                        <span style="color:#0f172a;">{{ $typeLabel }}</span>
                                    </p>

                                    @if(!empty($interview->interviewer_name))
                                        <p style="margin:0 0 10px;">
                                            <span style="display:inline-block;min-width:110px;color:#334155;font-weight:700;">Interviewer</span>
                                            <span style="color:#0f172a;">{{ $interview->interviewer_name }}</span>
                                        </p>
                                    @endif

                                    @if(!empty($interview->meeting_url))
                                        <p style="margin:0 0 0;">
                                            <span style="display:inline-block;min-width:110px;color:#334155;font-weight:700;">Meeting link</span>
                                            <a href="{{ $interview->meeting_url }}" style="color:#2563eb;text-decoration:none;font-weight:700;" target="_blank" rel="noopener">
                                                Join meeting
                                            </a>
                                            <span style="display:block;margin-left:110px;color:#64748b;font-size:13px;word-break:break-all;">
                                                {{ $interview->meeting_url }}
                                            </span>
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 16px;font-size:14px;color:#475569;">
                            <strong>Add to calendar:</strong> We’ve attached a calendar invite (.ics) to this email.
                        </p>

                        @if(!empty($interview->notes))
                            <div style="margin:0 0 20px;padding:14px 14px;border-left:4px solid #2563eb;background:#eff6ff;border-radius:8px;">
                                <div style="font-size:13px;color:#1d4ed8;font-weight:800;margin-bottom:6px;">Notes</div>
                                <div style="white-space:pre-wrap;color:#0f172a;">{{ $interview->notes }}</div>
                            </div>
                        @endif

                        <p style="margin:20px 0 0;padding-top:20px;border-top:1px solid #eee;font-size:14px;color:#666;">
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

