<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

/**
 * Activity Log Model
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $event
 * @property array|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
class ActivityLog extends Model
{

    protected $table = 'activity_logs';

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

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    public function scopeByLogName(Builder $query, string $logName): Builder
    {
        return $query->where('log_name', $logName);
    }

    public function scopeByEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query->where('subject_type', get_class($subject))
                     ->where('subject_id', $subject->id);
    }

    public function scopeRecent(Builder $query, int $limit = 50): Builder
    {
        return $query->latest()->limit($limit);
    }

    public function scopeCausedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ==================== ACCESSORS ====================

    protected function changes(): Attribute
    {
        return Attribute::make(
            get: function() {
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
        );
    }

    protected function browser(): Attribute
    {
        return Attribute::make(
            get: function() {
                $userAgent = $this->user_agent ?? '';

                return match(true) {
                    str_contains($userAgent, 'Chrome') => 'Chrome',
                    str_contains($userAgent, 'Firefox') => 'Firefox',
                    str_contains($userAgent, 'Safari') => 'Safari',
                    str_contains($userAgent, 'Edge') => 'Edge',
                    str_contains($userAgent, 'Opera') => 'Opera',
                    default => 'Unknown',
                };
            }
        );
    }

    protected function device(): Attribute
    {
        return Attribute::make(
            get: function() {
                $userAgent = $this->user_agent ?? '';

                return match(true) {
                    str_contains($userAgent, 'Mobile') => 'Mobile',
                    str_contains($userAgent, 'Tablet') => 'Tablet',
                    default => 'Desktop',
                };
            }
        );
    }

    protected function eventLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->event) {
                'created' => 'Created',
                'updated' => 'Updated',
                'deleted' => 'Deleted',
                'restored' => 'Restored',
                'status_changed' => 'Status Changed',
                'verified' => 'Verified',
                'rejected' => 'Rejected',
                default => ucfirst($this->event ?? 'action'),
            }
        );
    }

    // ==================== STATIC HELPER METHODS ====================

    public static function logActivity(
        string $description,
        ?Model $subject = null,
        ?string $logName = null,
        ?string $event = null,
        ?array $properties = null,
        ?int $userId = null
    ): self {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
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

    public static function logApplicationActivity(Application $application, string $event, string $description): self
    {
        return static::logActivity(
            description: $description,
            subject: $application,
            logName: 'application',
            event: $event
        );
    }

    public static function logPaymentActivity(Payment $payment, string $event, string $description): self
    {
        return static::logActivity(
            description: $description,
            subject: $payment,
            logName: 'payment',
            event: $event
        );
    }

    public static function logUserActivity(User $user, string $event, string $description): self
    {
        return static::logActivity(
            description: $description,
            subject: $user,
            logName: 'user',
            event: $event
        );
    }

    // ==================== HELPER METHODS ====================

    public function hasChanges(): bool
    {
        return !empty($this->changes);
    }

    public function getOldValue(string $attribute): mixed
    {
        return $this->properties['old'][$attribute] ?? null;
    }

    public function getNewValue(string $attribute): mixed
    {
        return $this->properties['attributes'][$attribute] ?? null;
    }
}
