<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class ApplicationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $this->setTeamContext($user);

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        $this->setTeamContext($user);

        return $user->hasAnyRole(['parent', 'school_admin', 'admission_admin', 'finance_admin']);
    }

    public function view(User $user, Application $application): bool
    {
        $this->setTeamContext($user);

        if ($this->isParent($user)) {
            return (int) $application->user_id === (int) $user->id;
        }

        return $this->isSchoolStaffFor($user, (int) $application->school_id);
    }

    public function update(User $user, Application $application): bool
    {
        $this->setTeamContext($user);

        if ($this->isParent($user)) {
            return (int) $application->user_id === (int) $user->id
                && (string) $application->status === 'draft';
        }

        return $this->isSchoolStaffFor($user, (int) $application->school_id);
    }

    public function transitionStatus(User $user, Application $application, string $toStatus): bool
    {
        $this->setTeamContext($user);

        if (! $application->canTransitionTo($toStatus)) {
            return false;
        }

        if ($this->isParent($user)) {
            return (int) $application->user_id === (int) $user->id
                && (string) $application->status === 'draft'
                && $toStatus === 'submitted';
        }

        return $this->isSchoolStaffFor($user, (int) $application->school_id)
            && $this->hasTenantRole($user, (int) $application->school_id, ['school_admin', 'admission_admin']);
    }

    public function viewSensitiveFiles(User $user, Application $application): bool
    {
        return $this->view($user, $application);
    }

    private function isSchoolStaffFor(User $user, int $schoolId): bool
    {
        return (int) $user->school_id > 0
            && (int) $user->school_id === $schoolId
            && (bool) $user->is_active;
    }

    /**
     * @param array<int, string> $roleNames
     */
    private function hasTenantRole(User $user, int $schoolId, array $roleNames): bool
    {
        return $user->roles()
            ->whereIn('roles.name', $roleNames)
            ->where('roles.school_id', $schoolId)
            ->exists();
    }

    private function isParent(User $user): bool
    {
        return $user->roles()
            ->where('roles.name', 'parent')
            ->where('roles.school_id', 0)
            ->exists();
    }

    private function setTeamContext(User $user): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId((int) ($user->school_id ?: 0));
    }
}
