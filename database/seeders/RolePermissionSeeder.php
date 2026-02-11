<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates roles and permissions for VIS multi-tenant admission system
     */
    public function run(): void
    {
        $this->command->info('🔐 Creating Roles & Permissions...');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== CREATE PERMISSIONS ====================

        $this->command->info('📝 Creating permissions...');

        $permissions = [
            // Dashboard & Analytics
            'view_dashboard',
            'view_analytics',
            'view_global_reports',

            // School Management (Super SuperAdmin Only)
            'view_any_school',
            'view_school',
            'create_school',
            'update_school',
            'delete_school',

            // Academic Year
            'view_any_academic_year',
            'view_academic_year',
            'create_academic_year',
            'update_academic_year',
            'delete_academic_year',

            // Level Management
            'view_any_level',
            'view_level',
            'create_level',
            'update_level',
            'delete_level',

            // Admission Period
            'view_any_admission_period',
            'view_admission_period',
            'create_admission_period',
            'update_admission_period',
            'delete_admission_period',

            // Application Management
            'view_any_application',
            'view_application',
            'create_application',
            'update_application',
            'delete_application',
            'review_application',
            'approve_application',
            'reject_application',
            'assign_application',
            'export_applications',

            // Document Verification
            'view_any_document',
            'view_document',
            'upload_document',
            'verify_document',
            'reject_document',
            'delete_document',

            // Payment Management
            'view_any_payment_type',
            'view_payment_type',
            'create_payment_type',
            'update_payment_type',
            'delete_payment_type',

            'view_any_payment',
            'view_payment',
            'create_payment',
            'verify_payment',
            'reject_payment',
            'refund_payment',
            'export_payments',

            // Schedule Management
            'view_any_schedule',
            'view_schedule',
            'create_schedule',
            'update_schedule',
            'delete_schedule',
            'complete_schedule',

            // Medical Records
            'view_any_medical_record',
            'view_medical_record',
            'create_medical_record',
            'update_medical_record',
            'delete_medical_record',

            // Enrollment
            'view_any_enrollment',
            'view_enrollment',
            'create_enrollment',
            'update_enrollment',
            'delete_enrollment',
            'withdraw_enrollment',

            // User Management
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'assign_roles',

            // Settings
            'view_settings',
            'update_settings',
            'update_system_settings',

            // Activity Logs
            'view_any_activity_log',
            'view_activity_log',
            'delete_activity_log',

            // Reports
            'generate_reports',
            'export_reports',
            'view_financial_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info("✓ Created {$this->count($permissions)} permissions");

        // ==================== CREATE ROLES ====================

        $this->command->info('👥 Creating roles...');

        // 1. SUPER ADMIN (Global Access - No Tenant)
        $this->command->info('  Creating: super_admin...');
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'school_id' => 0,
            'guard_name' => 'web',
        ]);
        $superAdmin->givePermissionTo(Permission::all());
        $this->command->info('  ✓ super_admin: Full system access');

        // 2. SCHOOL ADMIN (Per School - Full School Access)
        $this->command->info('  Creating: school_admin...');
        $schoolAdmin = Role::firstOrCreate([
            'name' => 'school_admin',
            'school_id' => 0,
            'guard_name' => 'web',
        ]);
        $schoolAdmin->givePermissionTo([
            // Dashboard
            'view_dashboard',
            'view_analytics',

            // Academic Management
            'view_any_academic_year', 'view_academic_year', 'create_academic_year', 'update_academic_year',
            'view_any_level', 'view_level', 'create_level', 'update_level',
            'view_any_admission_period', 'view_admission_period', 'create_admission_period', 'update_admission_period',

            // Application Management
            'view_any_application', 'view_application', 'update_application', 'review_application',
            'approve_application', 'reject_application', 'assign_application', 'export_applications',

            // Documents
            'view_any_document', 'view_document', 'verify_document', 'reject_document',

            // Payments
            'view_any_payment_type', 'view_payment_type', 'create_payment_type', 'update_payment_type',
            'view_any_payment', 'view_payment', 'verify_payment', 'export_payments',

            // Schedules
            'view_any_schedule', 'view_schedule', 'create_schedule', 'update_schedule', 'complete_schedule',

            // Medical Records
            'view_any_medical_record', 'view_medical_record',

            // Enrollment
            'view_any_enrollment', 'view_enrollment', 'create_enrollment', 'update_enrollment', 'withdraw_enrollment',

            // Users (School level)
            'view_any_user', 'view_user', 'create_user', 'update_user',

            // Settings (School level)
            'view_settings', 'update_settings',

            // Reports
            'generate_reports', 'export_reports', 'view_financial_reports',

            // Activity Logs
            'view_any_activity_log', 'view_activity_log',
        ]);
        $this->command->info('  ✓ school_admin: Full school management');

        // 3. ADMISSION ADMIN (Per School - Application Processing)
        $this->command->info('  Creating: admission_admin...');
        $admissionAdmin = Role::firstOrCreate([
            'name' => 'admission_admin',
            'school_id' => 0,
            'guard_name' => 'web',
        ]);
        $admissionAdmin->givePermissionTo([
            'view_dashboard',

            // Applications
            'view_any_application', 'view_application', 'update_application', 'review_application',
            'approve_application', 'reject_application', 'export_applications',

            // Documents
            'view_any_document', 'view_document', 'verify_document', 'reject_document',

            // Schedules
            'view_any_schedule', 'view_schedule', 'create_schedule', 'update_schedule', 'complete_schedule',

            // Medical Records
            'view_any_medical_record', 'view_medical_record', 'create_medical_record', 'update_medical_record',

            // Enrollment
            'view_any_enrollment', 'view_enrollment', 'create_enrollment',

            // Reports
            'generate_reports', 'export_reports',
        ]);
        $this->command->info('  ✓ admission_admin: Application & document management');

        // 4. FINANCE ADMIN (Per School - Payment Processing)
        $this->command->info('  Creating: finance_admin...');
        $financeAdmin = Role::firstOrCreate([
            'name' => 'finance_admin',
            'school_id' => 0,
            'guard_name' => 'web',
        ]);
        $financeAdmin->givePermissionTo([
            'view_dashboard',

            // Applications (Read only)
            'view_any_application', 'view_application',

            // Payment Types
            'view_any_payment_type', 'view_payment_type', 'create_payment_type', 'update_payment_type',

            // Payments
            'view_any_payment', 'view_payment', 'verify_payment', 'reject_payment', 'refund_payment', 'export_payments',

            // Enrollment (Payment related)
            'view_any_enrollment', 'view_enrollment', 'update_enrollment',

            // Reports
            'generate_reports', 'export_reports', 'view_financial_reports',
        ]);
        $this->command->info('  ✓ finance_admin: Payment & financial management');

        // 5. PARENT (No Panel Access - Via Public Form)
        $this->command->info('  Creating: parent...');
        $parent = Role::firstOrCreate([
            'name' => 'parent',
            'school_id' => 0,
            'guard_name' => 'web',
        ]);
        $parent->givePermissionTo([
            // Own applications only
            'view_application',
            'create_application',
            'update_application', // Only when draft

            // Own documents
            'view_document',
            'upload_document',

            // Own payments
            'view_payment',
            'create_payment',

            // Own medical records
            'view_medical_record',
            'create_medical_record',
            'update_medical_record',
        ]);
        $this->command->info('  ✓ parent: Self-service application access');

        // ==================== SUMMARY ====================

        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info('✅ ROLES & PERMISSIONS SEEDING COMPLETE');
        $this->command->info('════════════════════════════════════════');
        $this->command->table(
            ['Role', 'Permissions', 'Access Level'],
            [
                ['super_admin', Permission::count(), 'Global (All Schools)'],
                ['school_admin', $schoolAdmin->permissions->count(), 'School Level (Full)'],
                ['admission_admin', $admissionAdmin->permissions->count(), 'School Level (Admission)'],
                ['finance_admin', $financeAdmin->permissions->count(), 'School Level (Finance)'],
                ['parent', $parent->permissions->count(), 'Limited (Own Data)'],
            ]
        );
        $this->command->newLine();
    }

    private function count(array $items): int
    {
        return count($items);
    }
}
