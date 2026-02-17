<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔐 Creating Roles & Permissions...');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== CREATE PERMISSIONS ====================

        $this->command->info('📝 Creating permissions...');

        $permissions = [
            // Dashboard & Analytics
            'view_dashboard',
            'view_analytics',
            'view_global_reports',

            // School Management (Super Admin Only)
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

            // Shield - Role Management
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',

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
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('  ✓ ' . count($permissions) . ' permissions created');

        // ==================== CREATE ROLES ====================
        //
        // Semua role di bawah dibuat dengan school_id = 0 (GLOBAL).
        // Ini berfungsi sebagai TEMPLATE yang akan di-copy ke
        // masing-masing sekolah oleh UserSeeder saat seeder dijalankan.
        //
        // Struktur akhir di DB:
        //   school_id = 0  → template global (5 roles)
        //   school_id = N  → role per-sekolah (5 roles × 3 sekolah = 15 roles)
        //
        // ================================================================

        $this->command->info('👥 Creating global role templates (school_id = 0)...');

        // ----------------------------------------------------------------
        // 1. SUPER ADMIN — Full system access
        //    school_id = 0 : akses /superadmin panel (global)
        //    school_id = N : akses /school/s/{code} dengan full permission
        // ----------------------------------------------------------------
        $superAdmin = Role::firstOrCreate([
            'name'       => 'super_admin',
            'school_id'  => 0,
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('  ✓ super_admin: ' . $superAdmin->permissions->count() . ' permissions (ALL)');

        // ----------------------------------------------------------------
        // 2. SCHOOL ADMIN — Full school management (no global school CRUD)
        // ----------------------------------------------------------------
        $schoolAdmin = Role::firstOrCreate([
            'name'       => 'school_admin',
            'school_id'  => 0,
            'guard_name' => 'web',
        ]);
        $schoolAdmin->syncPermissions([
            // Dashboard
            'view_dashboard', 'view_analytics',

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

            // Users (scoped ke sekolah sendiri)
            'view_any_user', 'view_user', 'create_user', 'update_user',

            // Shield - Role Management
            'view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role',

            // Settings
            'view_settings', 'update_settings',

            // Reports
            'generate_reports', 'export_reports', 'view_financial_reports',

            // Activity Logs
            'view_any_activity_log', 'view_activity_log',
        ]);
        $this->command->info('  ✓ school_admin: ' . $schoolAdmin->permissions->count() . ' permissions');

        // ----------------------------------------------------------------
        // 3. ADMISSION ADMIN — Application processing
        // ----------------------------------------------------------------
        $admissionAdmin = Role::firstOrCreate([
            'name'       => 'admission_admin',
            'school_id'  => 0,
            'guard_name' => 'web',
        ]);
        $admissionAdmin->syncPermissions([
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
        $this->command->info('  ✓ admission_admin: ' . $admissionAdmin->permissions->count() . ' permissions');

        // ----------------------------------------------------------------
        // 4. FINANCE ADMIN — Payment processing
        // ----------------------------------------------------------------
        $financeAdmin = Role::firstOrCreate([
            'name'       => 'finance_admin',
            'school_id'  => 0,
            'guard_name' => 'web',
        ]);
        $financeAdmin->syncPermissions([
            'view_dashboard',

            // Applications (read only)
            'view_any_application', 'view_application',

            // Payment Types
            'view_any_payment_type', 'view_payment_type', 'create_payment_type', 'update_payment_type',

            // Payments
            'view_any_payment', 'view_payment', 'verify_payment', 'reject_payment', 'refund_payment', 'export_payments',

            // Enrollment (payment-related)
            'view_any_enrollment', 'view_enrollment', 'update_enrollment',

            // Reports
            'generate_reports', 'export_reports', 'view_financial_reports',
        ]);
        $this->command->info('  ✓ finance_admin: ' . $financeAdmin->permissions->count() . ' permissions');

        // ----------------------------------------------------------------
        // 5. PARENT — Self-service via public form (no panel access)
        // ----------------------------------------------------------------
        $parent = Role::firstOrCreate([
            'name'       => 'parent',
            'school_id'  => 0,
            'guard_name' => 'web',
        ]);
        $parent->syncPermissions([
            // Own applications
            'view_application', 'create_application', 'update_application',

            // Own documents
            'view_document', 'upload_document',

            // Own payments
            'view_payment', 'create_payment',

            // Own medical records
            'view_medical_record', 'create_medical_record', 'update_medical_record',
        ]);
        $this->command->info('  ✓ parent: ' . $parent->permissions->count() . ' permissions (self-service only)');

        // ==================== SUMMARY ====================

        $this->command->newLine();
        $this->command->info('════════════════════════════════════════════════');
        $this->command->info('✅ ROLES & PERMISSIONS SEEDING COMPLETE');
        $this->command->info('════════════════════════════════════════════════');
        $this->command->table(
            ['Role', 'Permissions', 'Panel Access', 'Catatan'],
            [
                ['super_admin',    Permission::count(),                    '/superadmin + /school', 'Copy ke tiap sekolah via UserSeeder'],
                ['school_admin',   $schoolAdmin->permissions->count(),     '/school',               'Full tenant, tanpa global CRUD'],
                ['admission_admin',$admissionAdmin->permissions->count(),  '/school',               'Aplikasi, dokumen, jadwal'],
                ['finance_admin',  $financeAdmin->permissions->count(),    '/school',               'Pembayaran & laporan keuangan'],
                ['parent',         $parent->permissions->count(),          'Tidak ada panel',       'Form publik saja'],
            ]
        );
        $this->command->newLine();
        $this->command->info('💡 Per-school roles akan dibuat otomatis oleh UserSeeder');
        $this->command->info('   dengan copy permission dari template global ini.');
        $this->command->newLine();
    }
}
