<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Schedule Model
 *
 * @property int $id
 * @property int $application_id
 * @property string $type
 * @property Carbon $scheduled_date
 * @property string $scheduled_time
 * @property int $duration_minutes
 * @property string|null $location
 * @property string|null $location_details
 * @property bool $is_online
 * @property string|null $online_meeting_link
 * @property int|null $interviewer_id
 * @property string|null $assigned_staff
 * @property string $status
 * @property string|null $notes
 * @property string|null $result
 * @property int|null $score
 * @property string|null $recommendation
 * @property bool $notification_sent
 * @property Carbon|null $notification_sent_at
 * @property bool $reminder_sent
 * @property Carbon|null $reminder_sent_at
 * @property int|null $created_by
 * @property int|null $completed_by
 * @property Carbon|null $completed_at
 */
class Schedule extends Model
{
    use SoftDeletes;

    protected $table = 'schedules';

    protected $fillable = [
        'application_id',
        'type',
        'scheduled_date',
        'scheduled_time',
        'duration_minutes',
        'location',
        'location_details',
        'is_online',
        'online_meeting_link',
        'interviewer_id',
        'assigned_staff',
        'status',
        'notes',
        'result',
        'score',
        'recommendation',
        'notification_sent',
        'notification_sent_at',
        'reminder_sent',
        'reminder_sent_at',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'duration_minutes' => 'integer',
            'is_online' => 'boolean',
            'score' => 'integer',
            'notification_sent' => 'boolean',
            'notification_sent_at' => 'datetime',
            'reminder_sent' => 'boolean',
            'reminder_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'assigned_staff' => 'array',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // ==================== SCOPES ====================

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeObservations(Builder $query): Builder
    {
        return $query->where('type', 'observation');
    }

    public function scopeTests(Builder $query): Builder
    {
        return $query->where('type', 'test');
    }

    public function scopeInterviews(Builder $query): Builder
    {
        return $query->where('type', 'interview');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_date', '>=', Carbon::today())
                     ->whereIn('status', ['scheduled', 'confirmed'])
                     ->orderBy('scheduled_date')
                     ->orderBy('scheduled_time');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_date', Carbon::today())
                     ->orderBy('scheduled_time');
    }

    // ==================== ACCESSORS ====================

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->type) {
                'observation' => 'Observation Day',
                'test' => 'Assessment Test',
                'interview' => 'Interview',
                default => ucfirst($this->type),
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'scheduled' => 'Scheduled',
                'confirmed' => 'Confirmed',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'rescheduled' => 'Rescheduled',
                'no_show' => 'No Show',
                default => ucfirst($this->status),
            }
        );
    }

    protected function scheduledDateTime(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse("{$this->scheduled_date} {$this->scheduled_time}")
        );
    }

    protected function endTime(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->scheduledDateTime->copy()->addMinutes($this->duration_minutes)
        );
    }

    protected function isPast(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->scheduledDateTime->isPast()
        );
    }

    protected function isToday(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->scheduled_date->isToday()
        );
    }

    protected function hoursUntil(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, Carbon::now()->diffInHours($this->scheduledDateTime, false))
        );
    }

    // ==================== HELPER METHODS ====================

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && !$this->isPast;
    }

    public function canBeCompleted(): bool
    {
        return $this->isScheduled() && !$this->isCompleted();
    }

    public function needsReminder(): bool
    {
        return !$this->reminder_sent
            && $this->hoursUntil <= 24
            && $this->hoursUntil > 0
            && in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function complete(int $userId, string $result, ?int $score = null, ?string $recommendation = null): bool
    {
        $this->status = 'completed';
        $this->result = $result;
        $this->score = $score;
        $this->recommendation = $recommendation;
        $this->completed_by = $userId;
        $this->completed_at = now();

        return $this->save();
    }

    public function cancel(string $reason): bool
    {
        $this->status = 'cancelled';
        $this->notes = $reason;

        return $this->save();
    }

    public function markAsNoShow(): bool
    {
        $this->status = 'no_show';
        return $this->save();
    }

    public function reschedule(Carbon $newDate, string $newTime): bool
    {
        $this->status = 'rescheduled';
        $this->scheduled_date = $newDate;
        $this->scheduled_time = $newTime;

        return $this->save();
    }
}
