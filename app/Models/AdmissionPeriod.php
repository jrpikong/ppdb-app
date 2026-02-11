<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Admission Period Model
 *
 * @property int $id
 * @property int $school_id
 * @property int $academic_year_id
 * @property string $name
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $decision_date
 * @property Carbon|null $enrollment_deadline
 * @property bool $is_active
 * @property bool $allow_applications
 * @property bool $is_rolling
 * @property string|null $description
 * @property array|null $settings
 */
class AdmissionPeriod extends Model
{
    use SoftDeletes;

    protected $table = 'admission_periods';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'decision_date',
        'enrollment_deadline',
        'is_active',
        'is_rolling',
        'allow_applications',
        'description',
        'settings'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'decision_date' => 'date',
            'enrollment_deadline' => 'date',
            'is_active' => 'boolean',
            'allow_applications' => 'boolean',
            'is_rolling' => 'boolean',
            'settings' => 'array',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today)
                     ->where('is_active', true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', Carbon::today())
                     ->orderBy('start_date');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('end_date', '<', Carbon::today());
    }

    public function scopeRolling(Builder $query): Builder
    {
        return $query->where('is_rolling', true);
    }

    // ==================== ACCESSORS ====================

    protected function isOpen(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->is_active) return false;
                if ($this->is_rolling) return true;

                $today = Carbon::today();
                return $today->between($this->start_date, $this->end_date);
            }
        );
    }

    protected function isClosed(): Attribute
    {
        return Attribute::make(
            get: fn() => !$this->isOpen
        );
    }

    protected function daysRemaining(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->isOpen) return 0;
                if ($this->is_rolling) return 999; // Unlimited

                return Carbon::today()->diffInDays($this->end_date, false);
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->isOpen) return 'Open';
                if ($this->start_date->isFuture()) return 'Upcoming';
                return 'Closed';
            }
        );
    }

    protected function totalApplications(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->applications()->count()
        );
    }

    // ==================== HELPER METHODS ====================

    public function canAcceptApplications(): bool
    {
        return $this->isOpen && $this->is_active;
    }

    public function hasReachedDecisionDate(): bool
    {
        return $this->decision_date && Carbon::today()->gte($this->decision_date);
    }

    public function hasPassedEnrollmentDeadline(): bool
    {
        return $this->enrollment_deadline && Carbon::today()->gt($this->enrollment_deadline);
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (AdmissionPeriod $period) {
            if ($period->is_active && $period->isDirty('is_active')) {
                static::where('school_id', $period->school_id)
                      ->where('id', '!=', $period->id)
                      ->update(['is_active' => false]);
            }
        });
    }
}
