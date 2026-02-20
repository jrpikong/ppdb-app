<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class PaymentPolicy
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

    public function view(User $user, Payment $payment): bool
    {
        $this->setTeamContext($user);

        $application = $payment->application;

        if (! $application) {
            return false;
        }

        if ($this->isParent($user)) {
            return (int) $application->user_id === (int) $user->id;
        }

        return $this->isSchoolStaffFor($user, (int) $application->school_id);
    }

    public function submitProof(User $user, Payment $payment): bool
    {
        $this->setTeamContext($user);

        $application = $payment->application;

        return $application !== null
            && $this->isParent($user)
            && (int) $application->user_id === (int) $user->id
            && in_array((string) $payment->status, ['pending', 'rejected'], true);
    }

    public function transitionStatus(User $user, Payment $payment, string $toStatus): bool
    {
        $this->setTeamContext($user);

        if (! $payment->canTransitionTo($toStatus)) {
            return false;
        }

        $application = $payment->application;

        if (! $application) {
            return false;
        }

        if ($this->isParent($user)) {
            return (int) $application->user_id === (int) $user->id
                && $toStatus === 'submitted'
                && in_array((string) $payment->status, ['pending', 'rejected'], true);
        }

        if (! $this->isSchoolStaffFor($user, (int) $application->school_id)) {
            return false;
        }

        if ($this->hasTenantRole($user, (int) $application->school_id, ['school_admin'])) {
            return true;
        }

        if ($this->hasTenantRole($user, (int) $application->school_id, ['finance_admin'])) {
            return in_array($toStatus, ['submitted', 'verified', 'rejected', 'refunded'], true);
        }

        return false;
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
