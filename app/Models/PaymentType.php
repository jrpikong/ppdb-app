<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
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
        'amount',
        'is_mandatory',
        'is_active',
        'payment_instructions',
        'bank_name',
        'account_number',
        'account_holder',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all payments of this type.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include active payment types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include mandatory payment types.
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get bank account info as formatted string.
     */
    public function getBankAccountInfoAttribute(): string
    {
        if (!$this->bank_name || !$this->account_number) {
            return '-';
        }

        $info = "{$this->bank_name} - {$this->account_number}";
        
        if ($this->account_holder) {
            $info .= " a/n {$this->account_holder}";
        }

        return $info;
    }

    /**
     * Get total payments received for this type.
     */
    public function getTotalPaymentsReceivedAttribute(): float
    {
        return $this->payments()
            ->where('status', 'verified')
            ->sum('amount');
    }

    /**
     * Get count of verified payments.
     */
    public function getVerifiedPaymentsCountAttribute(): int
    {
        return $this->payments()
            ->where('status', 'verified')
            ->count();
    }
}
