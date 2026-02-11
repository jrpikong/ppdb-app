<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Level Model
 *
 * Represents educational levels (Early Preschool, Grade 1, etc.)
 *
 * @property int $id
 * @property int $school_id
 * @property string $code
 * @property string $name
 * @property string $program_category
 * @property float $age_min
 * @property float $age_max
 * @property int $quota
 * @property int $annual_tuition_fee
 * @property int $current_enrollment
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_accepting_applications
 */
class Level extends Model
{
    use SoftDeletes;

    protected $table = 'levels';

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'program_category',
        'age_min',
        'age_max',
        'quota',
        'annual_tuition_fee',
        'current_enrollment',
        'description',
        'sort_order',
        'is_active',
        'is_accepting_applications',
    ];

    protected function casts(): array
    {
        return [
            'age_min' => 'decimal:1',
            'age_max' => 'decimal:1',
            'quota' => 'integer',
            'current_enrollment' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_accepting_applications' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the school this level belongs to
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get all applications for this level
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get accepted applications only
     */
    public function acceptedApplications(): HasMany
    {
        return $this->applications()->where('status', 'accepted');
    }

    /**
     * Get enrolled students only
     */
    public function enrolledStudents(): HasMany
    {
        return $this->applications()->where('status', 'enrolled');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to get only active levels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get levels accepting applications
     */
    public function scopeAcceptingApplications($query)
    {
        return $query->where('is_active', true)
                     ->where('is_accepting_applications', true);
    }

    /**
     * Scope to get levels by program category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('program_category', $category);
    }

    /**
     * Scope to get Early Years Program levels
     */
    public function scopeEarlyYears($query)
    {
        return $query->where('program_category', 'early_years');
    }

    /**
     * Scope to get Primary Years Program levels
     */
    public function scopePrimaryYears($query)
    {
        return $query->where('program_category', 'primary_years');
    }

    /**
     * Scope to get Middle Years Program levels
     */
    public function scopeMiddleYears($query)
    {
        return $query->where('program_category', 'middle_years');
    }

    /**
     * Scope to get levels suitable for a specific age
     */
    public function scopeForAge($query, float $age)
    {
        return $query->where('age_min', '<=', $age)
                     ->where('age_max', '>=', $age);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get full level name with program
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->name} ({$this->programCategoryLabel})"
        );
    }

    /**
     * Get program category label
     */
    protected function programCategoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->program_category) {
                'early_years' => 'Early Years Program',
                'primary_years' => 'Primary Years Program',
                'middle_years' => 'Middle Years Program',
                default => $this->program_category,
            }
        );
    }

    /**
     * Get age range formatted
     */
    protected function ageRange(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->age_min} - {$this->age_max} years"
        );
    }

    /**
     * Get remaining quota
     */
    protected function remainingQuota(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, $this->quota - $this->current_enrollment)
        );
    }

    /**
     * Get quota utilization percentage
     */
    protected function quotaPercentage(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->quota <= 0) {
                    return 0;
                }
                return round(($this->current_enrollment / $this->quota) * 100, 2);
            }
        );
    }

    /**
     * Check if quota is full
     */
    protected function isQuotaFull(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->current_enrollment >= $this->quota
        );
    }

    /**
     * Get total applicants (all statuses)
     */
    protected function totalApplicants(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->applications()->count()
        );
    }

    /**
     * Get total accepted
     */
    protected function totalAccepted(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->applications()->where('status', 'accepted')->count()
        );
    }

    /**
     * Get total enrolled
     */
    protected function totalEnrolled(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->applications()->where('status', 'enrolled')->count()
        );
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if level can accept more applications
     */
    public function canAcceptApplications(): bool
    {
        return $this->is_active
            && $this->is_accepting_applications
            && !$this->isQuotaFull;
    }

    /**
     * Check if a specific age is eligible for this level
     */
    public function isAgeEligible(float $age): bool
    {
        return $age >= $this->age_min && $age <= $this->age_max;
    }

    /**
     * Increment current enrollment
     */
    public function incrementEnrollment(): bool
    {
        $this->current_enrollment++;
        return $this->save();
    }

    /**
     * Decrement current enrollment
     */
    public function decrementEnrollment(): bool
    {
        if ($this->current_enrollment > 0) {
            $this->current_enrollment--;
            return $this->save();
        }
        return false;
    }

    /**
     * Get color code for program category (for UI)
     */
    public function getCategoryColor(): string
    {
        return match($this->program_category) {
            'early_years' => 'pink',
            'primary_years' => 'blue',
            'middle_years' => 'green',
            default => 'gray',
        };
    }

    /**
     * Generate level code
     */
    public static function generateCode(string $programCategory, string $name): string
    {
        $prefix = match($programCategory) {
            'early_years' => 'EY',
            'primary_years' => 'PY',
            'middle_years' => 'MY',
            default => 'XX',
        };

        // Extract number from name if exists (e.g., "Grade 1" -> "G1")
        if (preg_match('/\d+/', $name, $matches)) {
            return $prefix . '-G' . $matches[0];
        }

        // For non-grade levels (e.g., "Early Preschool" -> "EY-EP")
        $words = explode(' ', $name);
        $code = array_reduce($words, fn($carry, $word) => $carry . strtoupper($word[0]), '');

        return $prefix . '-' . $code;
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate code if not provided
        static::creating(function (Level $level) {
            if (empty($level->code)) {
                $level->code = static::generateCode($level->program_category, $level->name);
            }
        });
    }
}
