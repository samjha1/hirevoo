<?php

namespace App\Mail;

use App\Models\EmployerJobApplication;
use App\Models\InterviewSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class InterviewScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  'candidate'|'employer'  $recipientRole
     */
    public function __construct(
        public EmployerJobApplication $application,
        public InterviewSchedule $interview,
        public string $recipientRole = 'candidate',
    ) {}

    public function envelope(): Envelope
    {
        $jobTitle = $this->application->employerJob?->title ?? 'Interview';
        $candidateName = $this->application->user?->name ?? 'Candidate';

        $subject = $this->recipientRole === 'employer'
            ? "Interview scheduled — {$candidateName} · {$jobTitle}"
            : "Your interview is scheduled — {$jobTitle}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.interview-scheduled',
            with: [
                'application' => $this->application,
                'interview' => $this->interview,
                'recipientRole' => $this->recipientRole,
            ],
        );
    }

    /**
     * Attach a calendar invite to the email.
     */
    public function attachments(): array
    {
        $filename = 'interview-' . ($this->interview->id ?: 'invite') . '.ics';

        return [
            Attachment::fromData(fn () => $this->buildIcs(), $filename)
                ->withMime('text/calendar; charset=utf-8'),
        ];
    }

    private function buildIcs(): string
    {
        $jobTitle = $this->application->employerJob?->title ?? 'Interview';
        $candidateName = $this->application->user?->name ?? 'Candidate';
        $meetingUrl = (string) ($this->interview->meeting_url ?? '');
        $notes = (string) ($this->interview->notes ?? '');

        $startUtc = $this->interview->scheduled_at->copy()->setTimezone('UTC');
        $endUtc = $this->interview->scheduled_at->copy()->addMinutes((int) ($this->interview->duration_minutes ?? 30))->setTimezone('UTC');

        $icsEscape = static function (?string $value): string {
            $value = $value ?? '';
            return str_replace(
                ["\\", "\r\n", "\n", "\r", ',', ';'],
                ["\\\\", "\\n", "\\n", "\\n", '\\,', '\\;'],
                $value
            );
        };

        $uid = ($this->interview->id ?: (string) Str::uuid()) . '@hirevo';
        $dtStamp = now()->utc()->format('Ymd\\THis\\Z');
        $dtStart = $startUtc->format('Ymd\\THis\\Z');
        $dtEnd = $endUtc->format('Ymd\\THis\\Z');

        $summary = $icsEscape("Interview - {$jobTitle} ({$candidateName})");

        $descriptionParts = [
            "Candidate: {$candidateName}",
            "Job: {$jobTitle}",
            $this->interview->interviewer_name ? "Interviewer: {$this->interview->interviewer_name}" : null,
            $meetingUrl !== '' ? "Meeting link: {$meetingUrl}" : null,
            $notes !== '' ? "Notes: {$notes}" : null,
        ];
        $description = $icsEscape(implode("\\n", array_filter($descriptionParts)));

        $location = $this->interview->interview_type === 'in_person'
            ? $icsEscape('On-site / In-Person')
            : $icsEscape($meetingUrl !== '' ? 'Online' : 'TBD');

        return "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//Hirevo//Interview Scheduling//EN\r\n"
            . "CALSCALE:GREGORIAN\r\n"
            . "METHOD:PUBLISH\r\n"
            . "BEGIN:VEVENT\r\n"
            . "UID:{$uid}\r\n"
            . "DTSTAMP:{$dtStamp}\r\n"
            . "DTSTART:{$dtStart}\r\n"
            . "DTEND:{$dtEnd}\r\n"
            . "SUMMARY:{$summary}\r\n"
            . "DESCRIPTION:{$description}\r\n"
            . "LOCATION:{$location}\r\n"
            . "URL:{$icsEscape($meetingUrl)}\r\n"
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";
    }
}

