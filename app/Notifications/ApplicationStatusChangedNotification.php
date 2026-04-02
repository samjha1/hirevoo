<?php

namespace App\Notifications;

use App\Models\EmployerJobApplication;
use App\Models\InterviewSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public EmployerJobApplication $application,
        public string $previousStatus,
        public string $newStatus,
        public ?InterviewSchedule $interview = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $job = $this->application->employerJob;
        $company = $job->company_name ?? ($job->user?->referrerProfile?->company_name ?? '—');
        $labels = EmployerJobApplication::statusOptions();
        $newLabel = $labels[$this->newStatus] ?? ucfirst($this->newStatus);

        if ($this->interview && $this->interview->scheduled_at) {
            $when = $this->interview->scheduled_at->timezone(config('app.timezone'))->format('d M Y, g:i A');
            $title = 'Interview scheduled';
            $body = "Interview for «{$job->title}» at {$company} on {$when}. Current stage: {$newLabel}.";
        } else {
            $title = 'Application status updated';
            $body = "Your application for «{$job->title}» at {$company} is now: {$newLabel}.";
        }

        return [
            'title' => $title,
            'body' => $body,
            'employer_job_application_id' => $this->application->id,
            'job_title' => $job->title,
            'company' => $company,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'new_status_label' => $newLabel,
            'url' => route('candidate.dashboard'),
        ];
    }
}
