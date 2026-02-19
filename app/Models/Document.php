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
 * Document Model
 *
 * @property int $id
 * @property int $application_id
 * @property string $type
 * @property string $name
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size
 * @property string|null $description
 * @property string $status
 * @property string|null $rejection_reason
 * @property string|null $verification_notes
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 */
class Document extends Model
{
    use SoftDeletes;

    public const DOCUMENT_TYPES = [
        'student_photo_1' => 'Student Photo 1',
        'student_photo_2' => 'Student Photo 2',
        'father_photo' => 'Father Photo',
        'mother_photo' => 'Mother Photo',
        'guardian_photo' => 'Guardian Photo',
        'father_id_card' => 'Father ID Card',
        'mother_id_card' => 'Mother ID Card',
        'guardian_id_card' => 'Guardian ID Card',
        'birth_certificate' => 'Birth Certificate',
        'family_card' => 'Family Card',
        'passport' => 'Passport',
        'latest_report_book' => 'Latest Report Book',
        'previous_report_books' => 'Previous Report Books',
        'recommendation_letter' => 'Recommendation Letter',
        'transcript' => 'Academic Transcript',
        'medical_history' => 'Medical History',
        'special_needs_form' => 'Special Needs Form',
        'immunization_record' => 'Immunization Record',
        'other' => 'Other Document',
    ];

    protected $table = 'documents';

    protected $fillable = [
        'application_id',
        'type',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'status',
        'rejection_reason',
        'verification_notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeNeedsResubmission(Builder $query): Builder
    {
        return $query->where('status', 'resubmit');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ==================== ACCESSORS ====================

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => self::DOCUMENT_TYPES[$this->type] ?? ucwords(str_replace('_', ' ', $this->type)),
        );
    }

    public static function documentTypeOptions(): array
    {
        return self::DOCUMENT_TYPES;
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => Storage::url($this->file_path)
        );
    }

    protected function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function() {
                $bytes = $this->file_size;

                if ($bytes >= 1073741824) {
                    return number_format($bytes / 1073741824, 2) . ' GB';
                } elseif ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                }
                return $bytes . ' bytes';
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'resubmit' => 'Needs Resubmission',
                default => ucfirst($this->status),
            }
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->status) {
                'pending' => 'yellow',
                'approved' => 'green',
                'rejected' => 'red',
                'resubmit' => 'orange',
                default => 'gray',
            }
        );
    }

    // ==================== HELPER METHODS ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function needsResubmission(): bool
    {
        return $this->status === 'resubmit';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    public function approve(int $userId, ?string $notes = null): bool
    {
        $this->status = 'approved';
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->verification_notes = $notes;

        return $this->save();
    }

    public function reject(int $userId, string $reason, ?string $notes = null): bool
    {
        $this->status = 'rejected';
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->rejection_reason = $reason;
        $this->verification_notes = $notes;

        return $this->save();
    }

    public function requestResubmission(int $userId, string $reason): bool
    {
        $this->status = 'resubmit';
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->rejection_reason = $reason;

        return $this->save();
    }

    // ==================== BOOT METHOD ====================

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Document $document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}
