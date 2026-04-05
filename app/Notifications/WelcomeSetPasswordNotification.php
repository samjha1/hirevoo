<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $setupUrl,
        protected string $resumeName = 'your resume'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your resume analysis is ready — set your password to save it')
            ->greeting('Hi ' . ($notifiable->name ?? 'there') . '!')
            ->line('We\'ve analysed **' . $this->resumeName . '** and your ATS score + job matches are ready.')
            ->line('Set a password to save your results and access them anytime:')
            ->action('Set My Password & View Results', $this->setupUrl)
            ->line('This link expires in **60 minutes**.')
            ->line('If you didn\'t upload a resume on Hirevo, you can safely ignore this email.')
            ->salutation('The Hirevo Team');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
