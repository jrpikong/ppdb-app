<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReRegistration extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'announcement_id',
        're_registration_number',
        're_registration_date',
        'total_payment',
        'payment_proof',
        'status',
        'notes',
        'original_documents_submitted',
        'submitted_documents',
        'verified_by',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        're_registration_date' => 'date',
        'total_payment' => 'decimal:2',
        'original_documents_submitted' => 'boolean',
        'submitted_documents' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate re-registration number before creating
        static::creating(function ($reReg) {
            if (!$reReg->re_registration_number) {
                $reReg->re_registration_number = static::generateReRegistrationNumber();
            }
        });

        // Delete payment proof when deleted
        static::deleting(function ($reReg) {
            if ($reReg->payment_proof && Storage::exists($reReg->payment_proof)) {
                Storage::delete($reReg->payment_proof);
            }
        });
    }

    /**
     * Generate unique re-registration number.
     */
    public static function generateReRegistrationNumber(): string
    {
        $year = now()->format('Y');
        $lastReReg = static::whereYear('created_at', now()->year)
            ->latest('id')
            ->first();
        
        $sequence = $lastReReg ? (int) substr($lastReReg->re_registration_number, -4) + 1 : 1;
        
        return "REREG/{$year}/" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the registration.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the announcement.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the verifier.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope to only include pending re-registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to only include completed re-registrations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to only include verified re-registrations.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Check if re-registration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if re-registration is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if re-registration is verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if re-registration is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get formatted payment amount.
     */
    public function getFormattedPaymentAttribute(): string
    {
        return 'Rp ' . number_format($this->total_payment, 0, ',', '.');
    }

    /**
     * Get payment proof URL.
     */
    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof) {
            return null;
        }
        
        return Storage::url($this->payment_proof);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'blue',
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
            'pending' => 'Pending',
            'completed' => 'Completed',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            default => $this->status,
        };
    }

    /**
     * Check if all required documents are submitted.
     */
    public function hasAllDocuments(): bool
    {
        if (!$this->submitted_documents || !is_array($this->submitted_documents)) {
            return false;
        }

        $requiredDocs = ['kartu_keluarga', 'akta_kelahiran', 'ijazah'];
        
        foreach ($requiredDocs as $doc) {
            if (!in_array($doc, $this->submitted_documents)) {
                return false;
            }
        }

        return true;
    }
}
