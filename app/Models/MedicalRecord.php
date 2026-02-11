<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Medical Record Model
 *
 * @property int $id
 * @property int $application_id
 * @property string|null $blood_type
 * @property float|null $height
 * @property float|null $weight
 * @property string|null $allergies
 * @property bool $has_food_allergies
 * @property string|null $food_allergies_details
 * @property bool $has_medical_conditions
 * @property string|null $medical_conditions
 * @property bool $requires_daily_medication
 * @property string|null $daily_medications
 * @property bool $has_dietary_restrictions
 * @property string|null $dietary_restrictions
 * @property bool $has_special_needs
 * @property string|null $special_needs_description
 * @property bool $requires_learning_support
 * @property string|null $learning_support_details
 * @property bool $immunizations_up_to_date
 * @property string|null $immunization_records
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_relationship
 * @property string|null $emergency_contact_phone
 * @property string|null $emergency_contact_email
 * @property string|null $doctor_name
 * @property string|null $doctor_phone
 * @property string|null $hospital_preference
 * @property string|null $health_insurance_provider
 * @property string|null $health_insurance_number
 * @property string|null $additional_notes
 * @property int|null $medical_form_document_id
 */
class MedicalRecord extends Model
{
    use SoftDeletes;

    protected $table = 'medical_records';

    protected $fillable = [
        'application_id',
        'blood_type',
        'height',
        'weight',
        'allergies',
        'has_food_allergies',
        'food_allergies_details',
        'has_medical_conditions',
        'medical_conditions',
        'requires_daily_medication',
        'daily_medications',
        'has_dietary_restrictions',
        'dietary_restrictions',
        'has_special_needs',
        'special_needs_description',
        'requires_learning_support',
        'learning_support_details',
        'immunizations_up_to_date',
        'immunization_records',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_email',
        'doctor_name',
        'doctor_phone',
        'hospital_preference',
        'health_insurance_provider',
        'health_insurance_number',
        'additional_notes',
        'medical_form_document_id',
    ];

    protected function casts(): array
    {
        return [
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'has_food_allergies' => 'boolean',
            'has_medical_conditions' => 'boolean',
            'requires_daily_medication' => 'boolean',
            'has_dietary_restrictions' => 'boolean',
            'has_special_needs' => 'boolean',
            'requires_learning_support' => 'boolean',
            'immunizations_up_to_date' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function medicalFormDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'medical_form_document_id');
    }

    // ==================== ACCESSORS ====================

    protected function bloodTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->blood_type ?? 'Unknown'
        );
    }

    protected function bmi(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->height || !$this->weight) return null;

                $heightInMeters = $this->height / 100;
                return round($this->weight / ($heightInMeters * $heightInMeters), 2);
            }
        );
    }

    protected function bmiCategory(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->bmi) return null;

                return match(true) {
                    $this->bmi < 18.5 => 'Underweight',
                    $this->bmi < 25 => 'Normal',
                    $this->bmi < 30 => 'Overweight',
                    default => 'Obese',
                };
            }
        );
    }

    protected function emergencyContactInfo(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!$this->emergency_contact_name) return null;

                $info = $this->emergency_contact_name;

                if ($this->emergency_contact_relationship) {
                    $info .= " ({$this->emergency_contact_relationship})";
                }

                if ($this->emergency_contact_phone) {
                    $info .= " - {$this->emergency_contact_phone}";
                }

                return $info;
            }
        );
    }

    // ==================== HELPER METHODS ====================

    public function hasAllergies(): bool
    {
        return !empty($this->allergies) || $this->has_food_allergies;
    }

    public function hasMedicalConditions(): bool
    {
        return $this->has_medical_conditions && !empty($this->medical_conditions);
    }

    public function hasSpecialNeeds(): bool
    {
        return $this->has_special_needs;
    }

    public function requiresLearningSupport(): bool
    {
        return $this->requires_learning_support;
    }

    public function requiresDailyMedication(): bool
    {
        return $this->requires_daily_medication;
    }

    public function hasDietaryRestrictions(): bool
    {
        return $this->has_dietary_restrictions;
    }

    public function hasEmergencyContact(): bool
    {
        return !empty($this->emergency_contact_name) && !empty($this->emergency_contact_phone);
    }

    public function hasInsurance(): bool
    {
        return !empty($this->health_insurance_provider);
    }

    public function isComplete(): bool
    {
        return !empty($this->blood_type)
            && $this->hasEmergencyContact()
            && !is_null($this->immunizations_up_to_date);
    }

    public function getCompletionPercentage(): int
    {
        $fields = [
            'blood_type' => !empty($this->blood_type),
            'height' => !empty($this->height),
            'weight' => !empty($this->weight),
            'allergies' => !is_null($this->has_food_allergies),
            'immunizations' => !is_null($this->immunizations_up_to_date),
            'emergency_contact' => $this->hasEmergencyContact(),
            'doctor' => !empty($this->doctor_name),
            'insurance' => !empty($this->health_insurance_provider),
        ];

        $completed = count(array_filter($fields));
        $total = count($fields);

        return round(($completed / $total) * 100);
    }
}
