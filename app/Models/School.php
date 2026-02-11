<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * School Model
 *
 * Represents a VIS campus (Bintaro, Kelapa Gading, Bali)
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $full_name
 * @property string $type
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string $city
 * @property string $country
 * @property string|null $address
 * @property string|null $postal_code
 * @property string $timezone
 * @property string|null $logo
 * @property string|null $banner
 * @property string|null $description
 * @property string|null $principal_name
 * @property string|null $principal_email
 * @property string|null $principal_signature
 * @property bool $is_active
 * @property bool $allow_online_admission
 * @property array|null $settings
 */
class School extends Model
{
    use SoftDeletes;

    protected $table = 'schools';

    protected $fillable = [
        'code',
        'name',
        'full_name',
        'type',
        'email',
        'phone',
        'website',
        'city',
        'country',
        'address',
        'postal_code',
        'timezone',
        'logo',
        'banner',
        'description',
        'principal_name',
        'principal_email',
        'principal_signature',
        'is_active',
        'allow_online_admission',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allow_online_admission' => 'boolean',
            'settings' => 'array',
        ];
    }

    // ============================================
    // TENANT INTERFACE IMPLEMENTATION
    // ============================================

    /**
     * Get the tenant identifier (slug for URL)
     */
    public function getRouteKeyName(): string
    {
        return 'code'; // URL: /school/s/vis-bin
    }

    /**
     * Get tenant avatar (logo)
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    /**
     * Get tenant name for display
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get all academic years for this school
     */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * Get all levels for this school
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Get all admission periods for this school
     */
    public function admissionPeriods(): HasMany
    {
        return $this->hasMany(AdmissionPeriod::class);
    }

    /**
     * Get all applications for this school
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get all payment types for this school
     */
    public function paymentTypes(): HasMany
    {
        return $this->hasMany(PaymentType::class);
    }

    /**
     * Get all staff users for this school
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active staff only
     */
    public function activeUsers(): HasMany
    {
        return $this->users()->where('is_active', true);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to get only active schools
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get schools accepting online admissions
     */
    public function scopeAcceptingAdmissions($query)
    {
        return $query->where('is_active', true)
                     ->where('allow_online_admission', true);
    }

    /**
     * Scope to search schools by code or name
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('code', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%")
              ->orWhere('city', 'like', "%{$term}%");
        });
    }

    // ==================== ACCESSORS ====================

    /**
     * Get full display name
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->full_name ?? $this->name
        );
    }

    /**
     * Get logo URL
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->logo
                ? Storage::url($this->logo)
                : asset('images/default-school-logo.png')
        );
    }

    /**
     * Get banner URL
     */
    protected function bannerUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->banner
                ? Storage::url($this->banner)
                : asset('images/default-school-banner.jpg')
        );
    }

    /**
     * Get principal signature URL
     */
    protected function principalSignatureUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->principal_signature
                ? Storage::url($this->principal_signature)
                : null
        );
    }

    /**
     * Get full address formatted
     */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $parts = array_filter([
                    $this->address,
                    $this->city,
                    $this->postal_code,
                    $this->country,
                ]);
                return implode(', ', $parts);
            }
        );
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get active academic year for this school
     */
    public function getActiveAcademicYear(): ?AcademicYear
    {
        return $this->academicYears()->where('is_active', true)->first();
    }

    /**
     * Get active admission period for this school
     */
    public function getActiveAdmissionPeriod(): ?AdmissionPeriod
    {
        return $this->admissionPeriods()->where('is_active', true)->first();
    }

    /**
     * Get total applications count
     */
    public function getTotalApplicationsAttribute(): int
    {
        return $this->applications()->count();
    }

    /**
     * Get total accepted students
     */
    public function getTotalAcceptedAttribute(): int
    {
        return $this->applications()->where('status', 'accepted')->count();
    }

    /**
     * Get total enrolled students
     */
    public function getTotalEnrolledAttribute(): int
    {
        return $this->applications()->where('status', 'enrolled')->count();
    }

    /**
     * Get setting value by key
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set setting value
     */
    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Check if school is accepting applications
     */
    public function isAcceptingApplications(): bool
    {
        return $this->is_active
            && $this->allow_online_admission
            && $this->getActiveAdmissionPeriod() !== null;
    }

    /**
     * Generate unique school code
     */
    public static function generateCode(string $city): string
    {
        $prefix = strtoupper(substr($city, 0, 3));
        $count = static::where('code', 'like', "VIS-{$prefix}%")->count();

        return "VIS-{$prefix}-" . str_pad((string)($count + 1), 2, '0', STR_PAD_LEFT);
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        // Auto-activate only one school if multi-school is disabled
        static::saving(function (School $school) {
            if ($school->is_active && !config('app.multi_school_enabled', false)) {
                static::where('id', '!=', $school->id)->update(['is_active' => false]);
            }
        });

        // Delete logo and banner when school is deleted
        static::deleting(function (School $school) {
            if ($school->logo && Storage::exists($school->logo)) {
                Storage::delete($school->logo);
            }
            if ($school->banner && Storage::exists($school->banner)) {
                Storage::delete($school->banner);
            }
            if ($school->principal_signature && Storage::exists($school->principal_signature)) {
                Storage::delete($school->principal_signature);
            }
        });
    }
}
