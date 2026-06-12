<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\ReferrerProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployerPlanAgreementMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $plan
     * @param  array<string, mixed>  $amounts
     */
    public function __construct(
        public User $user,
        public ReferrerProfile $profile,
        public Payment $payment,
        public array $plan,
        public array $amounts,
        public string $chequeNumber,
        public string $chequeDate,
    ) {}

    public function envelope(): Envelope
    {
        $planName = (string) ($this->plan['name'] ?? 'Plan');

        return new Envelope(
            subject: 'Hirevo '.$planName.' — payment received, pending verification',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.employer-plan-agreement',
        );
    }
}
