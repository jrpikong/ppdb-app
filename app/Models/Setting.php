<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_name',
        'school_nsm',
        'school_npsn',
        'school_level',
        'school_status',
        'school_phone',
        'school_email',
        'school_website',
        'school_address',
        'school_province',
        'school_regency',
        'school_district',
        'school_village',
        'school_postal_code',
        'school_logo',
        'school_header_image',
        'school_description',
        'school_vision',
        'school_mission',
        'principal_name',
        'principal_nip',
        'principal_signature',
        'registration_open',
        'registration_info',
        'min_age',
        'max_age',
        'email_notification_enabled',
        'email_from_address',
        'email_from_name',
        'extra_settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registration_open' => 'boolean',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'email_notification_enabled' => 'boolean',
        'extra_settings' => 'array',
    ];

    /**
     * Get the singleton instance of settings.
     */
    public static function getInstance(): self
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'school_name' => 'MTS NEGERI 1 WONOGIRI',
                'school_level' => 'SMP/MTS',
                'school_status' => 'negeri',
                'registration_open' => true,
                'min_age' => 12,
                'max_age' => 18,
                'email_notification_enabled' => true,
            ]);
        }
        
        return $settings;
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getInstance();
        return $settings->$key ?? $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        $settings = static::getInstance();
        $settings->$key = $value;
        $settings->save();
    }

    /**
     * Get school logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->school_logo) {
            return null;
        }
        
        return \Storage::url($this->school_logo);
    }

    /**
     * Get header image URL.
     */
    public function getHeaderImageUrlAttribute(): ?string
    {
        if (!$this->school_header_image) {
            return null;
        }
        
        return \Storage::url($this->school_header_image);
    }

    /**
     * Get principal signature URL.
     */
    public function getSignatureUrlAttribute(): ?string
    {
        if (!$this->principal_signature) {
            return null;
        }
        
        return \Storage::url($this->principal_signature);
    }

    /**
     * Get full school address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->school_address,
            $this->school_village,
            $this->school_district,
            $this->school_regency,
            $this->school_province,
            $this->school_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        return $this->registration_open;
    }

    /**
     * Check if email notifications are enabled.
     */
    public function emailNotificationsEnabled(): bool
    {
        return $this->email_notification_enabled;
    }

    /**
     * Get extra setting value by key.
     */
    public function getExtra(string $key, mixed $default = null): mixed
    {
        return $this->extra_settings[$key] ?? $default;
    }

    /**
     * Set extra setting value.
     */
    public function setExtra(string $key, mixed $value): void
    {
        $extras = $this->extra_settings ?? [];
        $extras[$key] = $value;
        $this->extra_settings = $extras;
        $this->save();
    }
}
