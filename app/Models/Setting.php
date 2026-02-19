<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'app_name',
        'app_version',
        'multi_school_enabled',
        'default_school_id',
        'online_admission_enabled',
        'require_payment_before_submission',
        'application_review_days',
        'email_notifications_enabled',
        'email_from_address',
        'email_from_name',
        'send_submission_confirmation',
        'send_status_updates',
        'send_interview_reminders',
        'send_acceptance_letters',
        'default_currency',
        'payment_instructions',
        'required_documents',
        'max_file_size_mb',
        'allowed_file_types',
        'auto_schedule_interviews',
        'interview_duration_minutes',
        'interview_buffer_minutes',
        'maintenance_mode',
        'maintenance_message',
        'extra_settings',
    ];

    protected function casts(): array
    {
        return [
            'multi_school_enabled' => 'boolean',
            'online_admission_enabled' => 'boolean',
            'require_payment_before_submission' => 'boolean',
            'email_notifications_enabled' => 'boolean',
            'send_submission_confirmation' => 'boolean',
            'send_status_updates' => 'boolean',
            'send_interview_reminders' => 'boolean',
            'send_acceptance_letters' => 'boolean',
            'required_documents' => 'array',
            'allowed_file_types' => 'array',
            'auto_schedule_interviews' => 'boolean',
            'maintenance_mode' => 'boolean',
            'extra_settings' => 'array',
        ];
    }

    public function defaultSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'default_school_id');
    }

    public static function getInstance(): self
    {
        $settings = static::query()->first();

        if (! $settings) {
            $settings = static::query()->create([
                'app_name' => 'VIS Admission System',
                'app_version' => '1.0.0',
                'multi_school_enabled' => false,
                'online_admission_enabled' => true,
                'require_payment_before_submission' => true,
                'application_review_days' => 5,
                'email_notifications_enabled' => true,
                'email_from_name' => 'VIS Admissions',
                'default_currency' => 'IDR',
                'max_file_size_mb' => 10,
                'maintenance_mode' => false,
            ]);
        }

        return $settings;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::getInstance()->{$key} ?? $default;
    }

    public static function set(string $key, mixed $value): bool
    {
        $settings = static::getInstance();
        $settings->{$key} = $value;

        return $settings->save();
    }

    public static function allowsRegistration(): bool
    {
        return (bool) static::get('online_admission_enabled', true);
    }

    public static function requiresEmailVerification(): bool
    {
        $extra = static::get('extra_settings', []);

        return (bool) ($extra['require_email_verification'] ?? true);
    }
}
