<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentInAppNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $body,
        private readonly ?string $url = null,
        private readonly string $level = 'info',
    ) {
    }

    public function via(object $notifiable): array
    {
        if (! empty($notifiable->email)) {
            return ['database', 'mail'];
        }

        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line($this->body)
            ->salutation('The VIS Admission Team');

        if ($this->url) {
            $mail->action('View Details', $this->url);
        }

        return match ($this->level) {
            'success' => $mail->success(),
            'danger' => $mail->error(),
            default => $mail,
        };
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'level' => $this->level,
            'sent_at' => now()->toDateTimeString(),
        ];
    }
}

