<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class RegistrationPeriod extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'announcement_date',
        're_registration_start',
        're_registration_end',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'announcement_date' => 'date',
        're_registration_start' => 'date',
        're_registration_end' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-deactivate other registration periods when this one is activated
        static::saving(function ($period) {
            if ($period->is_active) {
                static::where('id', '!=', $period->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * Get the academic year that owns this period.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get all registrations for this period.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Scope a query to only include active periods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include open periods.
     */
    public function scopeOpen($query)
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('is_active', true);
    }

    /**
     * Check if registration is currently open.
     */
    public function isOpen(): bool
    {
        $today = Carbon::today();
        return $this->is_active 
            && $today->between($this->start_date, $this->end_date);
    }

    /**
     * Check if registration is closed.
     */
    public function isClosed(): bool
    {
        return !$this->isOpen();
    }

    /**
     * Check if announcement date has passed.
     */
    public function isAnnouncementDatePassed(): bool
    {
        if (!$this->announcement_date) {
            return false;
        }
        return Carbon::today()->gte($this->announcement_date);
    }

    /**
     * Check if re-registration is open.
     */
    public function isReRegistrationOpen(): bool
    {
        if (!$this->re_registration_start || !$this->re_registration_end) {
            return false;
        }
        
        $today = Carbon::today();
        return $today->between($this->re_registration_start, $this->re_registration_end);
    }

    /**
     * Get days remaining until registration closes.
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->isClosed()) {
            return 0;
        }
        
        return Carbon::today()->diffInDays($this->end_date, false);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isOpen()) {
            return 'Open';
        } elseif (Carbon::today()->lt($this->start_date)) {
            return 'Upcoming';
        } else {
            return 'Closed';
        }
    }

    /**
     * Get total registrations count.
     */
    public function getTotalRegistrationsAttribute(): int
    {
        return $this->registrations()->count();
    }
}
