<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * Setting Model (Singleton)
 *
 * System-wide configuration
 *
 * @property int $id
 * @property int|null $school_id
 * @property bool $multi_school_enabled
 * @property int|null $default_school_id
 * @property string|null $system_name
 * @property string|null $system_logo
 * @property string|null $system_favicon
 * @property string|null $admin_email
 * @property string|null $support_email
 * @property string|null $contact_phone
 * @property bool $maintenance_mode
 * @property string|null $maintenance_message
 * @property bool $allow_registration
 * @property bool $require_email_verification
 * @property string $default_timezone
 * @property string $default_currency
 * @property string $default_language
 * @property bool $email_notifications_enabled
 * @property string|null $email_from_address
 * @property string|null $email_from_name
 * @property bool $sms_notifications_enabled
 * @property string|null $sms_gateway
 * @property array|null $payment_gateways
 * @property array|null $admission_settings
 * @property array|null $email_templates
 * @property array|null $custom_settings
 */
class Setting extends Model
{

    protected $table = 'settings';

    protected $fillable = [
        'school_id',
        'multi_school_enabled',
        'default_school_id',
        'system_name',
        'system_logo',
        'system_favicon',
        'admin_email',
        'support_email',
        'contact_phone',
        'maintenance_mode',
        'maintenance_message',
        'allow_registration',
        'require_email_verification',
        'default_timezone',
        'default_currency',
        'default_language',
        'email_notifications_enabled',
        'email_from_address',
        'email_from_name',
        'sms_notifications_enabled',
        'sms_gateway',
        'payment_gateways',
        'admission_settings',
        'email_templates',
        'custom_settings',
    ];

    protected function casts(): array
    {
        return [
            'multi_school_enabled' => 'boolean',
            'maintenance_mode' => 'boolean',
            'allow_registration' => 'boolean',
            'require_email_verification' => 'boolean',
            'email_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
            'payment_gateways' => 'array',
            'admission_settings' => 'array',
            'email_templates' => 'array',
            'custom_settings' => 'array',
            'allowed_file_types' => 'array',
            'required_documents' => 'array',
            'extra_settings' => 'array',

        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function defaultSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'default_school_id');
    }

    // ==================== SINGLETON PATTERN ====================

    public static function getInstance(): self
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'system_name' => 'VIS Admission System',
                'multi_school_enabled' => false,
                'allow_registration' => true,
                'require_email_verification' => true,
                'default_timezone' => 'Asia/Jakarta',
                'default_currency' => 'IDR',
                'default_language' => 'en',
                'email_notifications_enabled' => true,
                'maintenance_mode' => false,
            ]);
        }

        return $settings;
    }

    // ==================== ACCESSORS ====================

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->system_logo
                ? Storage::url($this->system_logo)
                : asset('images/default-logo.png')
        );
    }

    protected function faviconUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->system_favicon
                ? Storage::url($this->system_favicon)
                : asset('images/default-favicon.png')
        );
    }

    // ==================== STATIC GETTERS/SETTERS ====================

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getInstance();
        return $settings->$key ?? $default;
    }

    public static function set(string $key, mixed $value): bool
    {
        $settings = static::getInstance();
        $settings->$key = $value;
        return $settings->save();
    }

    public static function isMultiSchoolEnabled(): bool
    {
        return static::get('multi_school_enabled', false);
    }

    public static function isMaintenanceMode(): bool
    {
        return static::get('maintenance_mode', false);
    }

    public static function allowsRegistration(): bool
    {
        return static::get('allow_registration', true);
    }

    public static function requiresEmailVerification(): bool
    {
        return static::get('require_email_verification', true);
    }

    public static function emailNotificationsEnabled(): bool
    {
        return static::get('email_notifications_enabled', true);
    }

    public static function smsNotificationsEnabled(): bool
    {
        return static::get('sms_notifications_enabled', false);
    }

    // ==================== ADMISSION SETTINGS ====================

    public function getAdmissionSetting(string $key, mixed $default = null): mixed
    {
        return $this->admission_settings[$key] ?? $default;
    }

    public function setAdmissionSetting(string $key, mixed $value): bool
    {
        $settings = $this->admission_settings ?? [];
        $settings[$key] = $value;
        $this->admission_settings = $settings;
        return $this->save();
    }

    // ==================== EMAIL TEMPLATES ====================

    public function getEmailTemplate(string $name): ?array
    {
        return $this->email_templates[$name] ?? null;
    }

    public function setEmailTemplate(string $name, array $template): bool
    {
        $templates = $this->email_templates ?? [];
        $templates[$name] = $template;
        $this->email_templates = $templates;
        return $this->save();
    }

    // ==================== PAYMENT GATEWAYS ====================

    public function getActivePaymentGateways(): array
    {
        if (!$this->payment_gateways) return [];

        return array_filter($this->payment_gateways, fn($gateway) => $gateway['enabled'] ?? false);
    }

    public function isPaymentGatewayEnabled(string $gateway): bool
    {
        return ($this->payment_gateways[$gateway]['enabled'] ?? false);
    }

    // ==================== CUSTOM SETTINGS ====================

    public function getCustom(string $key, mixed $default = null): mixed
    {
        return $this->custom_settings[$key] ?? $default;
    }

    public function setCustom(string $key, mixed $value): bool
    {
        $customs = $this->custom_settings ?? [];
        $customs[$key] = $value;
        $this->custom_settings = $customs;
        return $this->save();
    }

    // ==================== HELPER METHODS ====================

    public function enableMaintenanceMode(string $message = 'System is under maintenance'): bool
    {
        $this->maintenance_mode = true;
        $this->maintenance_message = $message;
        return $this->save();
    }

    public function disableMaintenanceMode(): bool
    {
        $this->maintenance_mode = false;
        $this->maintenance_message = null;
        return $this->save();
    }

    public function enableMultiSchool(): bool
    {
        $this->multi_school_enabled = true;
        return $this->save();
    }

    public function disableMultiSchool(): bool
    {
        $this->multi_school_enabled = false;
        return $this->save();
    }
}
