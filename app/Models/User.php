<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

/**
 * User Model
 *
 * Multi-role user model for VIS Admission System
 *
 * @property int $id
 * @property int|null $school_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $avatar
 * @property string|null $employee_id
 * @property string|null $department
 * @property bool $is_active
 * @property string|null $remember_token
 */
class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'employee_id',
        'department',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'interviewer_id');
    }

    public function verifiedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'verified_by');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function reviewedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'reviewed_by');
    }

    public function assignedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'assigned_to');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'enrolled_by');
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereNotNull('school_id')
                     ->whereHas('roles', fn($q) =>
                         $q->whereIn('name', ['super_admin', 'admin', 'admission_admin', 'finance_admin'])
                     );
    }

    public function scopeParents(Builder $query): Builder
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', 'parent'));
    }

    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    // ==================== ACCESSORS ====================

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function() {
                if ($this->avatar) {
                    return \Storage::url($this->avatar);
                }

                return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
            }
        );
    }

    protected function initials(): Attribute
    {
        return Attribute::make(
            get: function() {
                $words = explode(' ', $this->name);

                if (count($words) >= 2) {
                    return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                }

                return strtoupper(substr($this->name, 0, 2));
            }
        );
    }

    // ==================== ROLE CHECK METHODS ====================

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isAdmissionAdmin(): bool
    {
        return $this->hasRole('admission_admin');
    }

    public function isFinanceAdmin(): bool
    {
        return $this->hasRole('finance_admin');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function isStaff(): bool
    {
        return $this->school_id !== null && !$this->isParent();
    }

    // ==================== FILAMENT PANEL ACCESS ====================

    protected static function booted(): void
    {
        // Auto-set team context when user is loaded
        static::retrieved(function ($user) {
            if ($user->school_id) {
                app(\Spatie\Permission\PermissionRegistrar::class)
                    ->setPermissionsTeamId($user->school_id);
            } else {
                app(\Spatie\Permission\PermissionRegistrar::class)
                    ->setPermissionsTeamId(0);
            }
        });
    }
    public function canAccessPanel(Panel $panel): bool
    {
        \Log::info('canAccessPanel called', [
            'user_email' => $this->email,
            'panel_id' => $panel->getId(),
            'roles' => $this->getRoleNames()->toArray(),
            'school_id' => $this->school_id,
            'is_active' => $this->is_active,
            'has_super_admin_role' => $this->hasRole('super_admin'),
        ]);

        if (! $this->is_active) {
            return false;
        }
        if ($panel->getId() === 'superadmin') {
            return $this->hasRole('super_admin');
        }

        if ($panel->getId() === 'my') {
            return $this->hasRole('parent');
        }

        // School panel - only staff with school_id
        if ($panel->getId() === 'school') {
            return $this->school_id !== null
                && $this->school_id !== 0
                && $this->hasAnyRole([
                    'super_admin',       // ✅ FIX: per-school super admin
                    'school_admin',
                    'admission_admin',
                    'finance_admin',
                ]);
        }

        return false;
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    // ============================================
    // TENANCY IMPLEMENTATION
    // ============================================

    /**
     * Get all tenants (schools) the user can access
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(School::class);
    }
    public function getTenants(Panel $panel): Collection
    {
        // Super admin has no tenants (global access)
        if ($this->hasRole('super_admin')) {
            return collect();
        }

        // Staff users: return their assigned school
        if ($this->school_id) {
            return School::where('id', $this->school_id)->get();
        }

        return collect();
    }

    /**
     * Check if user can access specific tenant
     */
    public function canAccessTenant(Model $tenant): bool
    {
        // Super admin can access all tenants
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Staff can only access their assigned school
        return $this->school_id === $tenant->id;
    }

    /**
     * Get tenant menu items (for tenant switcher)
     */
    public function getTenantMenuItem(Model $tenant): ?string
    {
        return $tenant->name;
    }

    // ==================== HELPER METHODS ====================

    public function hasActiveApplications(): bool
    {
        return $this->applications()
            ->whereIn('status', ['draft', 'submitted', 'under_review'])
            ->exists();
    }

    public function getTotalApplications(): int
    {
        return $this->applications()->count();
    }

    public function getAcceptedApplications(): int
    {
        return $this->applications()->where('status', 'accepted')->count();
    }

    public function canCreateApplication(): bool
    {
        return $this->isParent() && Setting::allowsRegistration();
    }

    public function assignToSchool(int $schoolId): bool
    {
        $this->school_id = $schoolId;
        return $this->save();
    }

    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    // ==================== ACTIVITY LOGGING ====================

    public function logActivity(string $description, ?string $event = null): ActivityLog
    {
        return ActivityLog::logActivity(
            description: $description,
            logName: 'user',
            event: $event,
            userId: $this->id
        );
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->latestTeam;
    }

    public function latestTeam(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
