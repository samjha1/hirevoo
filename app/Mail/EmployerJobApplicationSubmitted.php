<?php

namespace App\Mail;

use App\Models\EmployerJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployerJobApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public EmployerJob $job,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->job->title ?? 'Job';

        return new Envelope(
            subject: 'We received your application — '.$title,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.employer-job-application-submitted',
        );
    }
}
