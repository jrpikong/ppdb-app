<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view_dashboard',
            'view_statistics',
            
            // Academic Year
            'view_academic_years',
            'create_academic_years',
            'edit_academic_years',
            'delete_academic_years',
            
            // Registration Period
            'view_registration_periods',
            'create_registration_periods',
            'edit_registration_periods',
            'delete_registration_periods',
            
            // Major
            'view_majors',
            'create_majors',
            'edit_majors',
            'delete_majors',
            
            // Registration
            'view_registrations',
            'create_registrations',
            'edit_registrations',
            'delete_registrations',
            'verify_registrations',
            'export_registrations',
            
            // Document
            'view_documents',
            'upload_documents',
            'verify_documents',
            'delete_documents',
            
            // Payment Type
            'view_payment_types',
            'create_payment_types',
            'edit_payment_types',
            'delete_payment_types',
            
            // Payment
            'view_payments',
            'create_payments',
            'verify_payments',
            'delete_payments',
            'export_payments',
            
            // Score
            'view_scores',
            'input_scores',
            'edit_scores',
            'delete_scores',
            'update_rankings',
            
            // Announcement
            'view_announcements',
            'create_announcements',
            'publish_announcements',
            'delete_announcements',
            
            // Re-registration
            'view_re_registrations',
            'verify_re_registrations',
            'delete_re_registrations',
            
            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Settings
            'view_settings',
            'edit_settings',
            
            // Activity Log
            'view_activity_logs',
            'delete_activity_logs',
            
            // Reports
            'view_reports',
            'export_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Super Admin - Full access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin Sekolah - Almost full access except user management
        $adminSekolah = Role::firstOrCreate(['name' => 'admin_sekolah']);
        $adminSekolah->givePermissionTo([
            'view_dashboard',
            'view_statistics',
            'view_academic_years',
            'create_academic_years',
            'edit_academic_years',
            'view_registration_periods',
            'create_registration_periods',
            'edit_registration_periods',
            'view_majors',
            'create_majors',
            'edit_majors',
            'view_registrations',
            'edit_registrations',
            'verify_registrations',
            'export_registrations',
            'view_documents',
            'verify_documents',
            'view_payment_types',
            'create_payment_types',
            'edit_payment_types',
            'view_payments',
            'verify_payments',
            'export_payments',
            'view_scores',
            'input_scores',
            'edit_scores',
            'update_rankings',
            'view_announcements',
            'create_announcements',
            'publish_announcements',
            'view_re_registrations',
            'verify_re_registrations',
            'view_settings',
            'edit_settings',
            'view_activity_logs',
            'view_reports',
            'export_reports',
        ]);

        // 3. Panitia PPDB - Verification and scoring
        $panitia = Role::firstOrCreate(['name' => 'panitia']);
        $panitia->givePermissionTo([
            'view_dashboard',
            'view_statistics',
            'view_academic_years',
            'view_registration_periods',
            'view_majors',
            'view_registrations',
            'verify_registrations',
            'export_registrations',
            'view_documents',
            'verify_documents',
            'view_payments',
            'verify_payments',
            'view_scores',
            'input_scores',
            'edit_scores',
            'view_announcements',
            'view_re_registrations',
            'verify_re_registrations',
            'view_reports',
        ]);

        // 4. Calon Siswa - Limited to own data
        $calonSiswa = Role::firstOrCreate(['name' => 'calon_siswa']);
        $calonSiswa->givePermissionTo([
            'view_registrations', // Own only
            'create_registrations',
            'edit_registrations', // Own only, when draft
            'upload_documents',
            'view_documents', // Own only
            'view_payments', // Own only
            'create_payments',
            'view_announcements', // Own only
            'view_re_registrations', // Own only
        ]);

        $this->command->info('Roles and Permissions created successfully!');
    }
}
