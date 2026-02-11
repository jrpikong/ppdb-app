<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

/**
 * Parent Guardian Model
 *
 * @property int $id
 * @property int $application_id
 * @property string $type
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $relationship
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $id_type
 * @property string|null $id_number
 * @property string|null $nationality
 * @property string|null $occupation
 * @property string|null $company_name
 * @property string|null $company_address
 * @property string|null $work_phone
 * @property string|null $address
 * @property string|null $city
 * @property string|null $country
 * @property string|null $postal_code
 * @property bool $is_emergency_contact
 * @property bool $is_primary_contact
 * @property string|null $notes
 */
class ParentGuardian extends Model
{
    use SoftDeletes;

    protected $table = 'parent_guardians';

    protected $fillable = [
        'application_id',
        'type',
        'first_name',
        'middle_name',
        'last_name',
        'relationship',
        'email',
        'phone',
        'mobile',
        'id_type',
        'id_number',
        'nationality',
        'occupation',
        'company_name',
        'company_address',
        'work_phone',
        'address',
        'city',
        'country',
        'postal_code',
        'is_emergency_contact',
        'is_primary_contact',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_emergency_contact' => 'boolean',
            'is_primary_contact' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ==================== SCOPES ====================

    public function scopeFathers(Builder $query): Builder
    {
        return $query->where('type', 'father');
    }

    public function scopeMothers(Builder $query): Builder
    {
        return $query->where('type', 'mother');
    }

    public function scopeGuardians(Builder $query): Builder
    {
        return $query->where('type', 'guardian');
    }

    public function scopeEmergencyContacts(Builder $query): Builder
    {
        return $query->where('is_emergency_contact', true);
    }

    public function scopePrimaryContacts(Builder $query): Builder
    {
        return $query->where('is_primary_contact', true);
    }

    // ==================== ACCESSORS ====================

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim(implode(' ', array_filter([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])))
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->type) {
                'father' => 'Father',
                'mother' => 'Mother',
                'guardian' => 'Guardian',
                default => ucfirst($this->type),
            }
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function() {
                $parts = array_filter([
                    $this->address,
                    $this->city,
                    $this->postal_code,
                    $this->country,
                ]);
                return implode(', ', $parts) ?: null;
            }
        );
    }

    protected function preferredContact(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mobile ?: $this->phone ?: $this->email
        );
    }

    // ==================== HELPER METHODS ====================

    public function isFather(): bool
    {
        return $this->type === 'father';
    }

    public function isMother(): bool
    {
        return $this->type === 'mother';
    }

    public function isGuardian(): bool
    {
        return $this->type === 'guardian';
    }

    public function canBeContactedViaPhone(): bool
    {
        return !empty($this->phone) || !empty($this->mobile);
    }

    public function canBeContactedViaEmail(): bool
    {
        return !empty($this->email);
    }
}
