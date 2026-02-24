<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schools = App\Models\School::query()->select(['id','code','name','is_active'])->orderBy('id')->get()->toArray();
$users = App\Models\User::query()->where('email', 'like', '%vis-bin%')->with('roles:id,name,school_id')->get(['id','name','email','school_id','is_active'])->toArray();
$roles = Spatie\Permission\Models\Role::query()
    ->whereIn('name', ['super_admin','school_admin','admission_admin','finance_admin'])
    ->orderBy('school_id')
    ->orderBy('name')
    ->get(['id','name','school_id'])
    ->toArray();

$tenantChecks = [];
$visBin = App\Models\School::query()->where('code', 'VIS-BIN')->first();
if ($visBin) {
    foreach (App\Models\User::query()->where('email', 'like', '%vis-bin%')->get() as $u) {
        $tenantChecks[] = [
            'email' => $u->email,
            'school_id' => $u->school_id,
            'roles' => $u->roles->pluck('name')->values()->all(),
            'can_access_panel_school' => $u->canAccessPanel(app(Filament\PanelRegistry::class)->get('school')),
            'can_access_tenant_vis_bin' => $u->canAccessTenant($visBin),
            'tenant_count' => $u->getTenants(app(Filament\PanelRegistry::class)->get('school'))->count(),
        ];
    }
}

echo json_encode([
    'schools' => $schools,
    'vis_bin_users' => $users,
    'roles' => $roles,
    'tenant_checks' => $tenantChecks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
