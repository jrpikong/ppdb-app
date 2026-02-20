<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

/**
 * Academic Year Model
 *
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property int $start_year
 * @property int $end_year
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_active
 * @property string|null $description
 */
class AcademicYear extends Model
{
    use SoftDeletes;

    protected $table = 'academic_years';

    protected $fillable = [
        'school_id',
        'name',
        'start_year',
        'end_year',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_year' => 'integer',
            'end_year' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the school this academic year belongs to
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get all admission periods for this academic year
     */
    public function admissionPeriods(): HasMany
    {
        return $this->hasMany(AdmissionPeriod::class);
    }

    /**
     * Get all applications for this academic year
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to get only active academic years
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get academic years for a specific school
     */
    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope to get current academic year (based on dates)
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }

    /**
     * Scope to get upcoming academic years
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', Carbon::today())
                     ->orderBy('start_date');
    }

    /**
     * Scope to get past academic years
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', Carbon::today())
                     ->orderBy('end_date', 'desc');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get full year name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->name
        );
    }

    /**
     * Check if academic year is current
     */
    protected function isCurrent(): Attribute
    {
        return Attribute::make(
            get: function() {
                $today = Carbon::today();
                return $today->between($this->start_date, $this->end_date);
            }
        );
    }

    /**
     * Get duration in months
     */
    protected function durationMonths(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->start_date->diffInMonths($this->end_date)
        );
    }

    /**
     * Get days remaining
     */
    protected function daysRemaining(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->isCurrent) {
                    return 0;
                }
                return Carbon::today()->diffInDays($this->end_date, false);
            }
        );
    }

    /**
     * Get status label
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->isCurrent) {
                    return 'Current';
                }
                if ($this->start_date->isFuture()) {
                    return 'Upcoming';
                }
                return 'Past';
            }
        );
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get total applications count
     */
    public function getTotalApplicationsAttribute(): int
    {
        return $this->applications()->count();
    }

    /**
     * Get total enrolled students
     */
    public function getTotalEnrolledAttribute(): int
    {
        return $this->applications()->where('status', 'enrolled')->count();
    }

    /**
     * Generate academic year name from years
     */
    public static function generateName(int $startYear, int $endYear): string
    {
        return "{$startYear}-{$endYear}";
    }

    /**
     * Check if this academic year is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Activate this academic year (deactivate others in same school)
     */
    public function activate(): bool
    {
        // Deactivate all other academic years for this school
        static::where('school_id', $this->school_id)
              ->where('id', '!=', $this->id)
              ->update(['is_active' => false]);

        // Activate this one
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate this academic year
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate name if not provided
        static::creating(function (AcademicYear $year) {
            if (empty($year->name) && $year->start_year && $year->end_year) {
                $year->name = static::generateName($year->start_year, $year->end_year);
            }
        });

        // Ensure only one active academic year per school
        static::saving(function (AcademicYear $year) {
            if ($year->is_active && $year->isDirty('is_active')) {
                static::where('school_id', $year->school_id)
                      ->where('id', '!=', $year->id)
                      ->update(['is_active' => false]);
            }

            // Auto-deactivate other academic years in the same school only.
            if ($year->is_active) {
                static::where('school_id', $year->school_id)
                    ->where('id', '!=', $year->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
