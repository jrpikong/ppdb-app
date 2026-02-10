<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject (polymorphic relation).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by log name.
     */
    public function scopeLogName($query, string $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to get recent activities.
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Log an activity.
     */
    public static function log(
        string $description,
        ?User $user = null,
        ?Model $subject = null,
        ?string $logName = null,
        ?string $event = null,
        ?array $properties = null
    ): self {
        return static::create([
            'user_id' => $user?->id ?? auth()->id(),
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'event' => $event,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get changes from properties.
     */
    public function getChangesAttribute(): array
    {
        if (!$this->properties || !isset($this->properties['attributes']) || !isset($this->properties['old'])) {
            return [];
        }

        $changes = [];
        $new = $this->properties['attributes'];
        $old = $this->properties['old'];

        foreach ($new as $key => $value) {
            if (isset($old[$key]) && $old[$key] != $value) {
                $changes[$key] = [
                    'old' => $old[$key],
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get the browser name from user agent.
     */
    public function getBrowserAttribute(): string
    {
        $userAgent = $this->user_agent ?? '';
        
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        
        return 'Unknown';
    }

    /**
     * Get the device type from user agent.
     */
    public function getDeviceAttribute(): string
    {
        $userAgent = $this->user_agent ?? '';
        
        if (str_contains($userAgent, 'Mobile')) return 'Mobile';
        if (str_contains($userAgent, 'Tablet')) return 'Tablet';
        
        return 'Desktop';
    }
}
