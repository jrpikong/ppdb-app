<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Schedule;
use App\Policies\ApplicationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\SchedulePolicy;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Permission;
use App\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(PermissionRegistrar::class)
            ->setPermissionClass(Permission::class)
            ->setRoleClass(Role::class);

        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);

        //
    }
}
