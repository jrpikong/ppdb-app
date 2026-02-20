<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\School;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating System Settings...');

        $defaultSchoolId = School::query()->value('id');

        Setting::create([
            'app_name' => 'VIS Admission Portal',
            'multi_school_enabled' => true,
            'default_school_id' => $defaultSchoolId,
            'maintenance_mode' => false,
            'email_notifications_enabled' => true,
            'email_from_address' => 'noreply@vis.sch.id',
            'email_from_name' => 'VIS Admission System',
            'default_currency' => 'IDR',
            'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
            'max_file_size_mb' => 5,
            'extra_settings' => [
                'admin_email' => 'admin@vis.sch.id',
                'support_email' => 'support@vis.sch.id',
                'contact_phone' => '+62-21-1234-5678',
                'require_email_verification' => true,
                'default_timezone' => 'Asia/Jakarta',
                'default_language' => 'en',
                'admission' => [
                    'max_applications_per_user' => 3,
                    'application_expiry_days' => 90,
                    'auto_reject_incomplete_after_days' => 30,
                    'require_interview' => true,
                    'require_observation' => true,
                ],
            ],
        ]);

        $this->command->info('System settings created.');
    }
}
