<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-deactivate other academic years when this one is activated
        static::saving(function ($academicYear) {
            if ($academicYear->is_active) {
                static::where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * Get the registration periods for this academic year.
     */
    public function registrationPeriods(): HasMany
    {
        return $this->hasMany(RegistrationPeriod::class);
    }

    /**
     * Get all registrations for this academic year.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Scope a query to only include active academic years.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full academic year name (e.g., "2023/2024").
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Check if this academic year is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get total registrations count.
     */
    public function getTotalRegistrationsAttribute(): int
    {
        return $this->registrations()->count();
    }
}
