<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'quota',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quota' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the registrations for this major (as first choice).
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'major_id');
    }

    /**
     * Get the registrations for this major (as second choice).
     */
    public function registrationsAsSecondChoice(): HasMany
    {
        return $this->hasMany(Registration::class, 'major_id_second');
    }

    /**
     * Get all announcements for this major.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Scope a query to only include active majors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the total number of applicants for this major.
     */
    public function getTotalApplicantsAttribute(): int
    {
        return $this->registrations()
            ->whereIn('status', ['submitted', 'verified', 'passed'])
            ->count();
    }

    /**
     * Get the total number of accepted students.
     */
    public function getTotalAcceptedAttribute(): int
    {
        return $this->registrations()
            ->where('status', 'passed')
            ->count();
    }

    /**
     * Get remaining quota.
     */
    public function getRemainingQuotaAttribute(): int
    {
        return max(0, $this->quota - $this->total_accepted);
    }

    /**
     * Check if quota is full.
     */
    public function isQuotaFull(): bool
    {
        return $this->remaining_quota <= 0;
    }

    /**
     * Get quota utilization percentage.
     */
    public function getQuotaPercentageAttribute(): float
    {
        if ($this->quota <= 0) {
            return 0;
        }
        
        return round(($this->total_accepted / $this->quota) * 100, 2);
    }

    /**
     * Get display name with code.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
