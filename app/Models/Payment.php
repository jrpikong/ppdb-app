<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Payment Model
 *
 * @property int $id
 * @property int $application_id
 * @property int $payment_type_id
 * @property string $transaction_code
 * @property float $amount
 * @property string $currency
 * @property Carbon $payment_date
 * @property string|null $payment_method
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $reference_number
 * @property string|null $proof_file
 * @property string $status
 * @property string|null $rejection_reason
 * @property string|null $notes
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property float|null $refund_amount
 * @property Carbon|null $refund_date
 * @property string|null $refund_reason
 */
class Payment extends Model
{
    use SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'application_id',
        'payment_type_id',
        'transaction_code',
        'amount',
        'currency',
        'payment_date',
        'payment_method',
        'bank_name',
        'account_number',
        'reference_number',
        'proof_file',
        'status',
        'rejection_reason',
        'notes',
        'verified_by',
        'verified_at',
        'refund_amount',
        'refund_date',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'verified_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'refund_date' => 'date',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ==================== SCOPES ====================

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    // ==================== ACCESSORS ====================

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->currency . ' ' . number_format($this->amount, 2, '.', ',')
        );
    }

    protected function proofUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->proof_file ? Storage::url($this->proof_file) : null
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'pending' => 'Pending',
                'submitted' => 'Awaiting Verification',
                'verified' => 'Verified',
                'rejected' => 'Rejected',
                'refunded' => 'Refunded',
                default => ucfirst($this->status),
            }
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'pending' => 'gray',
                'submitted' => 'yellow',
                'verified' => 'green',
                'rejected' => 'red',
                'refunded' => 'blue',
                default => 'gray',
            }
        );
    }

    // ==================== HELPER METHODS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function canBeVerified(): bool
    {
        return $this->isSubmitted() && $this->proof_file;
    }

    public function verify(int $userId, ?string $notes = null): bool
    {
        $this->status = 'verified';
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->notes = $notes;

        return $this->save();
    }

    public function reject(int $userId, string $reason): bool
    {
        $this->status = 'rejected';
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->rejection_reason = $reason;

        return $this->save();
    }

    public function refund(float $amount, string $reason): bool
    {
        $this->status = 'refunded';
        $this->refund_amount = $amount;
        $this->refund_date = now();
        $this->refund_reason = $reason;

        return $this->save();
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            if (empty($payment->transaction_code)) {
                $payment->transaction_code = $payment->generateTransactionCode();
            }
        });

        static::deleting(function (Payment $payment) {
            if ($payment->proof_file && Storage::exists($payment->proof_file)) {
                Storage::delete($payment->proof_file);
            }
        });
    }

    public function generateTransactionCode(): string
    {
        $date = now()->format('Ymd');
        $school = $this->application?->school ?? School::find($this->application?->school_id);
        $schoolCode = $school?->code ?? 'VIS';

        $lastPayment = static::whereDate('created_at', today())->latest('id')->first();
        $sequence = $lastPayment ? ((int) substr($lastPayment->transaction_code, -4)) + 1 : 1;

        return "{$schoolCode}-PAY-{$date}-" . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
