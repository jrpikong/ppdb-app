<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Application Model
 *
 * Core model for student admission applications
 *
 * @property int $id
 * @property int $user_id
 * @property int $school_id
 * @property int $academic_year_id
 * @property int $admission_period_id
 * @property int $level_id
 * @property string $application_number
 * @property string $student_first_name
 * @property string|null $student_middle_name
 * @property string $student_last_name
 * @property string|null $student_preferred_name
 * @property string|null $gender
 * @property string|null $birth_place
 * @property Carbon $birth_date
 * @property string $nationality
 * @property string|null $passport_number
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $current_address
 * @property string|null $current_city
 * @property string|null $current_country
 * @property string|null $current_postal_code
 * @property string|null $previous_school_name
 * @property string|null $previous_school_country
 * @property string|null $current_grade_level
 * @property Carbon|null $previous_school_start_date
 * @property Carbon|null $previous_school_end_date
 * @property string|null $special_needs
 * @property string|null $learning_support_required
 * @property string|null $languages_spoken
 * @property string|null $interests_hobbies
 * @property string $status
 * @property string|null $status_notes
 * @property Carbon|null $submitted_at
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $decision_made_at
 * @property Carbon|null $enrolled_at
 * @property string|null $decision_letter
 * @property string|null $decision_letter_file
 * @property int|null $assigned_to
 * @property int|null $reviewed_by
 * @property bool $requires_observation
 * @property bool $requires_test
 * @property bool $requires_interview
 */
class Application extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'draft',
        'submitted',
        'under_review',
        'documents_verified',
        'interview_scheduled',
        'interview_completed',
        'payment_pending',
        'payment_verified',
        'accepted',
        'rejected',
        'waitlisted',
        'enrolled',
        'withdrawn',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'documents_verified' => 'Documents Verified',
        'interview_scheduled' => 'Interview Scheduled',
        'interview_completed' => 'Interview Completed',
        'payment_pending' => 'Payment Pending',
        'payment_verified' => 'Payment Verified',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'waitlisted' => 'Waitlisted',
        'enrolled' => 'Enrolled',
        'withdrawn' => 'Withdrawn',
    ];

    public const ACTIVE_STATUSES = [
        'draft',
        'submitted',
        'under_review',
        'documents_verified',
        'interview_scheduled',
        'interview_completed',
        'payment_pending',
        'payment_verified',
        'accepted',
        'waitlisted',
        'enrolled',
    ];

    public const STATUS_TRANSITIONS = [
        'draft' => ['submitted', 'withdrawn'],
        'submitted' => ['under_review', 'withdrawn'],
        'under_review' => ['documents_verified', 'rejected', 'waitlisted', 'withdrawn'],
        'documents_verified' => ['interview_scheduled', 'rejected', 'waitlisted', 'withdrawn'],
        'interview_scheduled' => ['interview_completed', 'withdrawn'],
        'interview_completed' => ['payment_pending', 'accepted', 'rejected', 'waitlisted', 'withdrawn'],
        'payment_pending' => ['payment_verified', 'rejected', 'withdrawn'],
        'payment_verified' => ['accepted', 'rejected', 'withdrawn'],
        'accepted' => ['enrolled', 'withdrawn'],
        'waitlisted' => ['accepted', 'rejected', 'withdrawn'],
        'rejected' => [],
        'enrolled' => [],
        'withdrawn' => [],
    ];

    protected $table = 'applications';

    protected $fillable = [
        'user_id',
        'school_id',
        'academic_year_id',
        'admission_period_id',
        'level_id',
        'application_number',
        'student_first_name',
        'student_middle_name',
        'student_last_name',
        'student_preferred_name',
        'gender',
        'birth_place',
        'birth_date',
        'nationality',
        'passport_number',
        'email',
        'phone',
        'current_address',
        'current_city',
        'current_country',
        'current_postal_code',
        'previous_school_name',
        'previous_school_country',
        'current_grade_level',
        'previous_school_start_date',
        'previous_school_end_date',
        'special_needs',
        'learning_support_required',
        'languages_spoken',
        'interests_hobbies',
        'status',
        'status_notes',
        'submitted_at',
        'reviewed_at',
        'decision_made_at',
        'enrolled_at',
        'decision_letter',
        'decision_letter_file',
        'assigned_to',
        'reviewed_by',
        'requires_observation',
        'requires_test',
        'requires_interview',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'previous_school_start_date' => 'date',
            'previous_school_end_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'decision_made_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'requires_observation' => 'boolean',
            'requires_test' => 'boolean',
            'requires_interview' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function admissionPeriod(): BelongsTo
    {
        return $this->belongsTo(AdmissionPeriod::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function parentGuardians(): HasMany
    {
        return $this->hasMany(ParentGuardian::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==================== SCOPES ====================

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', 'under_review');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeEnrolled(Builder $query): Builder
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForAcademicYear(Builder $query, int $yearId): Builder
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopeForLevel(Builder $query, int $levelId): Builder
    {
        return $query->where('level_id', $levelId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function($q) use ($term) {
            $q->where('application_number', 'like', "%{$term}%")
              ->orWhere('student_first_name', 'like', "%{$term}%")
              ->orWhere('student_last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    // ==================== ACCESSORS ====================

    protected function studentFullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim(implode(' ', array_filter([
                $this->student_first_name,
                $this->student_middle_name,
                $this->student_last_name,
            ])))
        );
    }

    protected function studentDisplayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->student_preferred_name ?? $this->student_first_name
        );
    }

    protected function studentAge(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->birth_date ? Carbon::parse($this->birth_date)->age : null
        );
    }

    protected function studentAgeInYears(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->birth_date) return 0;

                $years = Carbon::parse($this->birth_date)->diffInYears(now());
                $months = Carbon::parse($this->birth_date)->diffInMonths(now()) % 12;

                return $years + ($months / 12);
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => self::STATUS_LABELS[$this->status] ?? ucfirst($this->status),
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'draft' => 'gray',
                'submitted' => 'blue',
                'under_review', 'documents_verified' => 'yellow',
                'interview_scheduled', 'interview_completed' => 'purple',
                'payment_pending', 'payment_verified' => 'indigo',
                'accepted' => 'green',
                'rejected' => 'red',
                'waitlisted' => 'amber',
                'enrolled' => 'emerald',
                'withdrawn' => 'gray',
                default => 'gray',
            }
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $parts = array_filter([
                    $this->current_address,
                    $this->current_city,
                    $this->current_postal_code,
                    $this->current_country,
                ]);
                return implode(', ', $parts);
            }
        );
    }

    // ==================== STATUS CHECK METHODS ====================

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isEnrolled(): bool
    {
        return $this->status === 'enrolled';
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function statusLabelFor(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst($status);
    }

    public static function activeStatuses(): array
    {
        return self::ACTIVE_STATUSES;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        if ($newStatus === $this->status) {
            return true;
        }

        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? [], true);
    }

    public function availableStatusOptions(): array
    {
        $allowed = self::STATUS_TRANSITIONS[$this->status] ?? [];
        $available = array_unique([$this->status, ...$allowed]);

        return collect(self::STATUS_LABELS)
            ->only($available)
            ->toArray();
    }

    public function canBeSubmitted(): bool
    {
        // Check if saving seat payment is verified
        $savingSeatPaid = $this->payments()
            ->whereHas('paymentType', fn($q) => $q->where('payment_stage', 'pre_submission'))
            ->where('status', 'verified')
            ->exists();

        return $this->isDraft() && $savingSeatPaid && $this->hasAllRequiredDocuments();
    }

    public function hasAllRequiredDocuments(): bool
    {
        $requiredTypes = [
            'student_photo_1',
            'student_photo_2',
            'father_photo',
            'mother_photo',
            'father_id_card',
            'mother_id_card',
            'birth_certificate',
            'family_card',
            'latest_report_book',
        ];

        $uploadedTypes = $this->documents()->pluck('type')->toArray();

        foreach ($requiredTypes as $type) {
            if (!in_array($type, $uploadedTypes)) {
                return false;
            }
        }

        return true;
    }

    public function getCompletionPercentage(): int
    {
        $steps = [
            'personal_info' => !empty($this->student_first_name) && !empty($this->student_last_name) && !empty($this->birth_date),
            'contact_info' => !empty($this->email) && !empty($this->phone),
            'address' => !empty($this->current_address),
            'parents' => $this->parentGuardians()->count() >= 2,
            'documents' => $this->hasAllRequiredDocuments(),
            'payment' => $this->payments()->where('status', 'verified')->exists(),
        ];

        $completed = count(array_filter($steps));
        $total = count($steps);

        return round(($completed / $total) * 100);
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Application $application) {
            if (empty($application->application_number)) {
                $application->application_number = $application->generateApplicationNumber();
            }
        });
    }

    public function generateApplicationNumber(): string
    {
        $school = $this->school ?? School::find($this->school_id);
        $schoolCode = $school?->code ?? 'VIS';
        $year = now()->year;

        $lastApplication = static::where('school_id', $this->school_id)
            ->whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $lastApplication
            ? ((int) substr($lastApplication->application_number, -4)) + 1
            : 1;

        return "{$schoolCode}-{$year}-" . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
