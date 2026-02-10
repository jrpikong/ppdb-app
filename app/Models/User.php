<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all registrations for this user (as student).
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the current/active registration for this user.
     */
    public function currentRegistration(): HasOne
    {
        return $this->hasOne(Registration::class)
            ->whereIn('status', ['draft', 'submitted', 'verified', 'passed'])
            ->latest();
    }

    /**
     * Get registrations verified by this user (as admin/panitia).
     */
    public function verifiedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'verified_by');
    }

    /**
     * Get payments verified by this user.
     */
    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    /**
     * Get documents verified by this user.
     */
    public function verifiedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'verified_by');
    }

    /**
     * Get scores inputted by this user.
     */
    public function inputtedScores(): HasMany
    {
        return $this->hasMany(Score::class, 'inputted_by');
    }

    /**
     * Get announcements published by this user.
     */
    public function publishedAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'published_by');
    }

    /**
     * Get re-registrations verified by this user.
     */
    public function verifiedReRegistrations(): HasMany
    {
        return $this->hasMany(ReRegistration::class, 'verified_by');
    }

    /**
     * Get activity logs for this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is an admin sekolah.
     */
    public function isAdminSekolah(): bool
    {
        return $this->hasRole('admin_sekolah');
    }

    /**
     * Check if user is panitia.
     */
    public function isPanitia(): bool
    {
        return $this->hasRole('panitia');
    }

    /**
     * Check if user is a student (calon siswa).
     */
    public function isStudent(): bool
    {
        return $this->hasRole('calon_siswa');
    }

    /**
     * Get avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return \Storage::url($this->avatar);
        }
        
        // Default avatar using UI Avatars
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Scope to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only include students.
     */
    public function scopeStudents($query)
    {
        return $query->role('calon_siswa');
    }

    /**
     * Scope to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->role(['super_admin', 'admin_sekolah', 'panitia']);
    }

    /**
     * Get user initials.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($this->name, 0, 2));
    }
}
