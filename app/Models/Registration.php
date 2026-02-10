<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'registration_period_id',
        'major_id',
        'major_id_second',
        'registration_number',
        'registration_type',
        'nisn',
        'nik',
        'nis_lokal',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'citizenship',
        'child_number',
        'siblings_count',
        'phone',
        'email',
        'hobby',
        'previous_school',
        'npsn_previous_school',
        'living_status',
        'transportation',
        'distance_to_school',
        'travel_time',
        'has_kip',
        'kip_number',
        'has_kks',
        'kks_number',
        'has_pkh',
        'pkh_number',
        'special_needs',
        'has_tk_ra',
        'has_paud',
        'immunization_hepatitis_b',
        'immunization_bcg',
        'immunization_dpt',
        'immunization_polio',
        'immunization_campak',
        'immunization_covid',
        'status',
        'rejection_reason',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'child_number' => 'integer',
        'siblings_count' => 'integer',
        'distance_to_school' => 'decimal:2',
        'travel_time' => 'integer',
        'has_kip' => 'boolean',
        'has_kks' => 'boolean',
        'has_pkh' => 'boolean',
        'has_tk_ra' => 'boolean',
        'has_paud' => 'boolean',
        'immunization_hepatitis_b' => 'boolean',
        'immunization_bcg' => 'boolean',
        'immunization_dpt' => 'boolean',
        'immunization_polio' => 'boolean',
        'immunization_campak' => 'boolean',
        'immunization_covid' => 'boolean',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns this registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the registration period.
     */
    public function registrationPeriod(): BelongsTo
    {
        return $this->belongsTo(RegistrationPeriod::class);
    }

    /**
     * Get the first choice major.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id');
    }

    /**
     * Get the second choice major.
     */
    public function secondMajor(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'major_id_second');
    }

    /**
     * Get the verifier (user who verified this registration).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the address for this registration.
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    /**
     * Get all parents for this registration.
     */
    public function parents(): HasMany
    {
        return $this->hasMany(ParentModel::class);
    }

    /**
     * Get the father data.
     */
    public function father(): HasOne
    {
        return $this->hasOne(ParentModel::class)->where('type', 'ayah');
    }

    /**
     * Get the mother data.
     */
    public function mother(): HasOne
    {
        return $this->hasOne(ParentModel::class)->where('type', 'ibu');
    }

    /**
     * Get the guardian data.
     */
    public function guardian(): HasOne
    {
        return $this->hasOne(ParentModel::class)->where('type', 'wali');
    }

    /**
     * Get all documents for this registration.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all payments for this registration.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the score for this registration.
     */
    public function score(): HasOne
    {
        return $this->hasOne(Score::class);
    }

    /**
     * Get the announcement for this registration.
     */
    public function announcement(): HasOne
    {
        return $this->hasOne(Announcement::class);
    }

    /**
     * Get the re-registration data.
     */
    public function reRegistration(): HasOne
    {
        return $this->hasOne(ReRegistration::class);
    }

    /**
     * Scope a query to only include registrations with specific status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft registrations.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include submitted registrations.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope a query to only include verified registrations.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope a query to only include passed registrations.
     */
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    /**
     * Check if registration is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if registration is submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if registration is verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if registration is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if registration passed selection.
     */
    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    /**
     * Check if registration failed selection.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if student has re-registered.
     */
    public function isReRegistered(): bool
    {
        return $this->status === 're_registered';
    }

    /**
     * Get student age based on birth date.
     */
    public function getAgeAttribute(): int
    {
        return $this->birth_date ? $this->birth_date->age : 0;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'yellow',
            'verified' => 'blue',
            'rejected' => 'red',
            'passed' => 'green',
            'failed' => 'red',
            're_registered' => 'purple',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            'passed' => 'Passed',
            'failed' => 'Failed',
            're_registered' => 'Re-registered',
            'cancelled' => 'Cancelled',
            default => $this->status,
        };
    }

    /**
     * Check if all required documents are uploaded.
     */
    public function hasAllDocuments(): bool
    {
        $requiredDocuments = ['foto_siswa', 'kartu_keluarga', 'akta_kelahiran', 'ijazah'];
        $uploadedTypes = $this->documents()->pluck('type')->toArray();
        
        foreach ($requiredDocuments as $required) {
            if (!in_array($required, $uploadedTypes)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $totalFields = 0;
        $filledFields = 0;

        // Personal data
        $personalFields = ['full_name', 'gender', 'birth_place', 'birth_date', 'religion'];
        $totalFields += count($personalFields);
        foreach ($personalFields as $field) {
            if ($this->$field) $filledFields++;
        }

        // Address
        if ($this->address) $filledFields += 2;
        $totalFields += 2;

        // Parents
        if ($this->father) $filledFields += 2;
        if ($this->mother) $filledFields += 2;
        $totalFields += 4;

        // Documents
        if ($this->hasAllDocuments()) $filledFields += 2;
        $totalFields += 2;

        return $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0;
    }
}
