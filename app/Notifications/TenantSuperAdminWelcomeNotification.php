<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSuperAdminWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly School $school,
        private readonly string $plainPassword,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $schoolLoginUrl = "{$appUrl}/school/login";
        $schoolDashboardUrl = "{$appUrl}/school/s/{$this->school->code}";

        return (new MailMessage)
            ->subject("{$this->school->name} - Super Admin Access")
            ->greeting("Hello {$notifiable->name},")
            ->line('Your school super admin account has been created successfully.')
            ->line("School: {$this->school->name} ({$this->school->code})")
            ->line("Login email: {$notifiable->email}")
            ->line("Temporary password: {$this->plainPassword}")
            ->line("Login URL: {$schoolLoginUrl}")
            ->line('After login, use your tenant dashboard URL below:')
            ->action('Open School Dashboard', $schoolDashboardUrl)
            ->line('Important: Please change your password immediately after first login.')
            ->line('If you cannot access your account, contact the system administrator.')
            ->salutation('VIS Admission Platform');
    }
}

