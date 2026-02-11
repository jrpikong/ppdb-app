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
 * Enrollment Model
 *
 * @property int $id
 * @property int $application_id
 * @property string $student_id
 * @property string $enrollment_number
 * @property Carbon $enrollment_date
 * @property Carbon $start_date
 * @property string|null $class_name
 * @property string|null $homeroom_teacher
 * @property float $total_amount_due
 * @property float $total_amount_paid
 * @property float $balance
 * @property string $payment_status
 * @property string $status
 * @property Carbon|null $withdrawal_date
 * @property string|null $withdrawal_reason
 * @property string|null $notes
 * @property int|null $enrolled_by
 */
class Enrollment extends Model
{
    use SoftDeletes;

    protected $table = 'enrollments';

    protected $fillable = [
        'application_id',
        'student_id',
        'enrollment_number',
        'enrollment_date',
        'start_date',
        'class_name',
        'homeroom_teacher',
        'total_amount_due',
        'total_amount_paid',
        'balance',
        'payment_status',
        'status',
        'withdrawal_date',
        'withdrawal_reason',
        'notes',
        'enrolled_by',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
            'start_date' => 'date',
            'total_amount_due' => 'decimal:2',
            'total_amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'withdrawal_date' => 'date',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    // ==================== SCOPES ====================

    public function scopeEnrolled(Builder $query): Builder
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->where('status', 'withdrawn');
    }

    public function scopeGraduated(Builder $query): Builder
    {
        return $query->where('status', 'graduated');
    }

    public function scopePaymentPending(Builder $query): Builder
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaymentComplete(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    // ==================== ACCESSORS ====================

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'enrolled' => 'Enrolled',
                'active' => 'Active',
                'completed' => 'Completed',
                'transferred' => 'Transferred',
                'withdrawn' => 'Withdrawn',
                'expelled' => 'Expelled',
                'graduated' => 'Graduated',
                default => ucfirst($this->status),
            }
        );
    }

    protected function paymentStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->payment_status) {
                'pending' => 'Pending',
                'partial' => 'Partially Paid',
                'paid' => 'Paid in Full',
                default => ucfirst($this->payment_status),
            }
        );
    }

    protected function formattedBalance(): Attribute
    {
        return Attribute::make(
            get: fn() => 'IDR ' . number_format($this->balance, 2, '.', ',')
        );
    }

    protected function formattedTotalDue(): Attribute
    {
        return Attribute::make(
            get: fn() => 'IDR ' . number_format($this->total_amount_due, 2, '.', ',')
        );
    }

    protected function formattedTotalPaid(): Attribute
    {
        return Attribute::make(
            get: fn() => 'IDR ' . number_format($this->total_amount_paid, 2, '.', ',')
        );
    }

    protected function paymentPercentage(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->total_amount_due <= 0) return 100;
                return round(($this->total_amount_paid / $this->total_amount_due) * 100, 2);
            }
        );
    }

    protected function daysEnrolled(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->enrollment_date->diffInDays(now())
        );
    }

    // ==================== HELPER METHODS ====================

    public function isEnrolled(): bool
    {
        return $this->status === 'enrolled';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    public function isGraduated(): bool
    {
        return $this->status === 'graduated';
    }

    public function hasFullyPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->balance <= 0;
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->balance > 0;
    }

    public function withdraw(string $reason): bool
    {
        $this->status = 'withdrawn';
        $this->withdrawal_date = now();
        $this->withdrawal_reason = $reason;

        return $this->save();
    }

    public function activate(): bool
    {
        $this->status = 'active';
        return $this->save();
    }

    public function updatePaymentStatus(): bool
    {
        if ($this->total_amount_paid >= $this->total_amount_due) {
            $this->payment_status = 'paid';
            $this->balance = 0;
        } elseif ($this->total_amount_paid > 0) {
            $this->payment_status = 'partial';
            $this->balance = $this->total_amount_due - $this->total_amount_paid;
        } else {
            $this->payment_status = 'pending';
            $this->balance = $this->total_amount_due;
        }

        return $this->save();
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Enrollment $enrollment) {
            if (empty($enrollment->student_id)) {
                $enrollment->student_id = $enrollment->generateStudentId();
            }

            if (empty($enrollment->enrollment_number)) {
                $enrollment->enrollment_number = $enrollment->generateEnrollmentNumber();
            }
        });
    }

    public function generateStudentId(): string
    {
        $school = $this->application?->school;
        $schoolCode = $school?->code ?? 'VIS';
        $year = now()->year;

        $lastStudent = static::whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $lastStudent ? ((int) substr($lastStudent->student_id, -4)) + 1 : 1;

        return "{$schoolCode}-{$year}-S-" . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }

    public function generateEnrollmentNumber(): string
    {
        $year = now()->year;

        $lastEnrollment = static::whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $lastEnrollment ? ((int) substr($lastEnrollment->enrollment_number, -4)) + 1 : 1;

        return "ENR-{$year}-" . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
