<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'payment_type_id',
        'transaction_code',
        'amount',
        'payment_date',
        'payment_method',
        'proof_file',
        'status',
        'rejection_reason',
        'notes',
        'verified_by',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate transaction code before creating
        static::creating(function ($payment) {
            if (!$payment->transaction_code) {
                $payment->transaction_code = static::generateTransactionCode();
            }
        });

        // Delete proof file when payment is deleted
        static::deleting(function ($payment) {
            if ($payment->proof_file && Storage::exists($payment->proof_file)) {
                Storage::delete($payment->proof_file);
            }
        });
    }

    /**
     * Generate unique transaction code.
     */
    public static function generateTransactionCode(): string
    {
        $date = now()->format('Ymd');
        $lastPayment = static::whereDate('created_at', today())
            ->latest('id')
            ->first();
        
        $sequence = $lastPayment ? (int) substr($lastPayment->transaction_code, -4) + 1 : 1;
        
        return $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the registration that owns this payment.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the payment type.
     */
    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    /**
     * Get the verifier (user who verified this payment).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include payments waiting verification.
     */
    public function scopeWaitingVerification($query)
    {
        return $query->where('status', 'waiting_verification');
    }

    /**
     * Scope a query to only include verified payments.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope a query to only include rejected payments.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is waiting verification.
     */
    public function isWaitingVerification(): bool
    {
        return $this->status === 'waiting_verification';
    }

    /**
     * Check if payment is verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if payment is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get proof file URL.
     */
    public function getProofUrlAttribute(): ?string
    {
        if (!$this->proof_file) {
            return null;
        }
        
        return Storage::url($this->proof_file);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'waiting_verification' => 'yellow',
            'verified' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Belum Dibayar',
            'waiting_verification' => 'Proses Cek',
            'verified' => 'Sudah Lunas',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }
}
