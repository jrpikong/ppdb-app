<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'major_id',
        'announcement_number',
        'status',
        'rank',
        'final_score',
        'announced_at',
        're_registration_deadline',
        'notes',
        'announcement_letter',
        'email_sent',
        'email_sent_at',
        'published_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rank' => 'integer',
        'final_score' => 'decimal:2',
        'announced_at' => 'date',
        're_registration_deadline' => 'date',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate announcement number before creating
        static::creating(function ($announcement) {
            if (!$announcement->announcement_number) {
                $announcement->announcement_number = static::generateAnnouncementNumber();
            }
        });
    }

    /**
     * Generate unique announcement number.
     */
    public static function generateAnnouncementNumber(): string
    {
        $year = now()->format('Y');
        $lastAnnouncement = static::whereYear('created_at', now()->year)
            ->latest('id')
            ->first();
        
        $sequence = $lastAnnouncement ? (int) substr($lastAnnouncement->announcement_number, -4) + 1 : 1;
        
        return "ANN/{$year}/" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the registration.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the major where student was accepted.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the publisher (admin who published this announcement).
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Get the re-registration data.
     */
    public function reRegistration(): HasOne
    {
        return $this->hasOne(ReRegistration::class);
    }

    /**
     * Scope to only include passed students.
     */
    public function scopePassed($query)
    {
        return $query->whereIn('status', ['lulus', 'lulus_cadangan', 'lulus_pilihan_2']);
    }

    /**
     * Scope to only include failed students.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'tidak_lulus');
    }

    /**
     * Check if student passed.
     */
    public function isPassed(): bool
    {
        return in_array($this->status, ['lulus', 'lulus_cadangan', 'lulus_pilihan_2']);
    }

    /**
     * Check if student failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'tidak_lulus';
    }

    /**
     * Check if re-registration is still open.
     */
    public function isReRegistrationOpen(): bool
    {
        if (!$this->re_registration_deadline) {
            return false;
        }
        
        return now()->lte($this->re_registration_deadline);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'lulus' => 'Lulus',
            'lulus_cadangan' => 'Lulus Cadangan',
            'lulus_pilihan_2' => 'Lulus Pilihan 2',
            'tidak_lulus' => 'Tidak Lulus',
            default => $this->status,
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'lulus' => 'green',
            'lulus_cadangan' => 'yellow',
            'lulus_pilihan_2' => 'blue',
            'tidak_lulus' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get rank with suffix.
     */
    public function getRankWithSuffixAttribute(): string
    {
        if (!$this->rank) {
            return '-';
        }

        $suffix = match($this->rank % 10) {
            1 => $this->rank % 100 === 11 ? 'th' : 'st',
            2 => $this->rank % 100 === 12 ? 'th' : 'nd',
            3 => $this->rank % 100 === 13 ? 'th' : 'rd',
            default => 'th',
        };

        return $this->rank . $suffix;
    }

    /**
     * Get days remaining for re-registration.
     */
    public function getDaysRemainingForReRegistrationAttribute(): int
    {
        if (!$this->re_registration_deadline || !$this->isReRegistrationOpen()) {
            return 0;
        }
        
        return now()->diffInDays($this->re_registration_deadline, false);
    }
}
