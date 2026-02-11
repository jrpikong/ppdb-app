<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

/**
 * Payment Type Model
 *
 * @property int $id
 * @property int $school_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property float $amount
 * @property string $currency
 * @property string $payment_stage
 * @property array $bank_info
 * @property bool $is_mandatory
 * @property bool $is_active
 * @property bool $is_refundable
 * @property bool $requires_proof
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $account_holder
 * @property string|null $payment_instructions
 * @property int $sort_order
 */
class PaymentType extends Model
{
    use SoftDeletes;

    protected $table = 'payment_types';

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'description',
        'amount',
        'currency',
        'payment_stage',
        'bank_info',
        'is_mandatory',
        'is_active',
        'requires_proof',
        'bank_name',
        'account_number',
        'account_holder',
        'payment_instructions',
        'sort_order',
        'is_refundable'
    ];

    protected function casts(): array
    {
        return [
            'bank_info' => 'array',
            'amount' => 'decimal:2',
            'is_mandatory' => 'boolean',
            'is_refundable' => 'boolean',
            'is_active' => 'boolean',
            'requires_proof' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeByStage(Builder $query, string $stage): Builder
    {
        return $query->where('payment_stage', $stage);
    }

    public function scopePreSubmission(Builder $query): Builder
    {
        return $query->where('payment_stage', 'pre_submission');
    }

    public function scopePostAcceptance(Builder $query): Builder
    {
        return $query->where('payment_stage', 'post_acceptance');
    }

    public function scopeEnrollment(Builder $query): Builder
    {
        return $query->where('payment_stage', 'enrollment');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->currency . ' ' . number_format($this->amount, 2, '.', ',')
        );
    }

    protected function stageLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->payment_stage) {
                'pre_submission' => 'Before Submission',
                'post_acceptance' => 'After Acceptance',
                'enrollment' => 'During Enrollment',
                'other' => 'Other',
                default => ucwords(str_replace('_', ' ', $this->payment_stage)),
            }
        );
    }

    protected function bankAccountInfo(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->bank_name || !$this->account_number) {
                    return null;
                }

                $info = "{$this->bank_name} - {$this->account_number}";

                if ($this->account_holder) {
                    $info .= " a/n {$this->account_holder}";
                }

                return $info;
            }
        );
    }

    protected function totalPaid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->payments()->where('status', 'verified')->sum('amount')
        );
    }

    protected function totalPayments(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->payments()->where('status', 'verified')->count()
        );
    }

    // ==================== HELPER METHODS ====================

    public function isMandatory(): bool
    {
        return $this->is_mandatory;
    }

    public function isPreSubmission(): bool
    {
        return $this->payment_stage === 'pre_submission';
    }

    public function isPostAcceptance(): bool
    {
        return $this->payment_stage === 'post_acceptance';
    }

    public function isEnrollmentStage(): bool
    {
        return $this->payment_stage === 'enrollment';
    }

    public function requiresUploadProof(): bool
    {
        return $this->requires_proof;
    }
}
