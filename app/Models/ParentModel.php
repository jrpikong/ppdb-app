<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'type',
        'name',
        'nik',
        'birth_place',
        'birth_date',
        'status',
        'citizenship',
        'education',
        'occupation',
        'monthly_income',
        'phone',
        'email',
        'living_location',
        'address',
        'province',
        'regency',
        'district',
        'village',
        'postal_code',
        'relationship',
        'kk_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'monthly_income' => 'decimal:2',
    ];

    /**
     * Get the registration that owns this parent data.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Scope a query to only include fathers.
     */
    public function scopeFathers($query)
    {
        return $query->where('type', 'ayah');
    }

    /**
     * Scope a query to only include mothers.
     */
    public function scopeMothers($query)
    {
        return $query->where('type', 'ibu');
    }

    /**
     * Scope a query to only include guardians.
     */
    public function scopeGuardians($query)
    {
        return $query->where('type', 'wali');
    }

    /**
     * Check if parent is father.
     */
    public function isFather(): bool
    {
        return $this->type === 'ayah';
    }

    /**
     * Check if parent is mother.
     */
    public function isMother(): bool
    {
        return $this->type === 'ibu';
    }

    /**
     * Check if parent is guardian.
     */
    public function isGuardian(): bool
    {
        return $this->type === 'wali';
    }

    /**
     * Check if parent is still alive.
     */
    public function isAlive(): bool
    {
        return $this->status === 'masih_hidup';
    }

    /**
     * Get parent type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'ayah' => 'Ayah',
            'ibu' => 'Ibu',
            'wali' => 'Wali',
            default => $this->type,
        };
    }

    /**
     * Get education level label.
     */
    public function getEducationLabelAttribute(): string
    {
        return match($this->education) {
            'tidak_sekolah' => 'Tidak Sekolah',
            'sd' => 'SD',
            'smp' => 'SMP',
            'sma' => 'SMA',
            'diploma' => 'Diploma',
            'sarjana' => 'Sarjana (S1)',
            'magister' => 'Magister (S2)',
            'doktor' => 'Doktor (S3)',
            default => $this->education ?? '-',
        };
    }

    /**
     * Get formatted monthly income.
     */
    public function getFormattedIncomeAttribute(): string
    {
        if (!$this->monthly_income) {
            return 'Rp 0';
        }
        
        return 'Rp ' . number_format($this->monthly_income, 0, ',', '.');
    }

    /**
     * Get full address.
     */
    public function getFullAddressAttribute(): string
    {
        if (!$this->address) {
            return '-';
        }

        $parts = array_filter([
            $this->address,
            $this->village,
            $this->district,
            $this->regency,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
