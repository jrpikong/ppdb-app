<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            // School Information
            'school_name' => 'MTS NEGERI 1 WONOGIRI',
            'school_nsm' => '121133120001',
            'school_npsn' => '20363813',
            'school_level' => 'SMP/MTS',
            'school_status' => 'negeri',
            
            // Contact
            'school_phone' => '0273-321234',
            'school_email' => 'info@mtsn1wonogiri.sch.id',
            'school_website' => 'https://mtsn1wonogiri.sch.id',
            
            // Address
            'school_address' => 'Jl. Raya Wonogiri - Solo KM 5',
            'school_province' => 'Jawa Tengah',
            'school_regency' => 'Wonogiri',
            'school_district' => 'Wonogiri',
            'school_village' => 'Wonogiri',
            'school_postal_code' => '57611',
            
            // Description
            'school_description' => 'MTS Negeri 1 Wonogiri adalah lembaga pendidikan Islam tingkat menengah pertama yang berada di bawah naungan Kementerian Agama. Sekolah ini berkomitmen untuk mencetak generasi yang berakhlak mulia, cerdas, dan berprestasi.',
            
            'school_vision' => 'Terwujudnya Madrasah yang Unggul dalam Prestasi, Berkarakter Islami, dan Berwawasan Lingkungan',
            
            'school_mission' => '1. Menyelenggarakan pendidikan yang berkualitas sesuai dengan standar nasional pendidikan
2. Mengembangkan pembelajaran yang inovatif dan berbasis teknologi
3. Membentuk karakter siswa yang berakhlakul karimah
4. Meningkatkan prestasi akademik dan non-akademik
5. Menciptakan lingkungan sekolah yang bersih, sehat, dan nyaman',
            
            // Principal
            'principal_name' => 'Drs. H. Ahmad Suryanto, M.Pd.I',
            'principal_nip' => '196512151994031003',
            
            // PPDB Settings
            'registration_open' => true,
            'registration_info' => '<h3>Informasi Pendaftaran PPDB MTS Negeri 1 Wonogiri</h3>
<p>Selamat datang di portal Penerimaan Peserta Didik Baru (PPDB) MTS Negeri 1 Wonogiri Tahun Ajaran 2024/2025.</p>
<h4>Syarat Pendaftaran:</h4>
<ul>
<li>Lulusan SD/MI atau sederajat</li>
<li>Memiliki ijazah atau surat keterangan lulus</li>
<li>Usia maksimal 15 tahun pada tanggal 1 Juli 2024</li>
<li>Sehat jasmani dan rohani</li>
</ul>
<h4>Dokumen yang Harus Disiapkan:</h4>
<ul>
<li>Foto ukuran 3x4 (berwarna)</li>
<li>Fotocopy Kartu Keluarga</li>
<li>Fotocopy Akta Kelahiran</li>
<li>Fotocopy Ijazah atau Surat Keterangan Lulus</li>
<li>Fotocopy Rapor Semester 1-5</li>
<li>Fotocopy KIP (jika ada)</li>
</ul>',
            'min_age' => 12,
            'max_age' => 15,
            
            // Email Settings
            'email_notification_enabled' => true,
            'email_from_address' => 'noreply@mtsn1wonogiri.sch.id',
            'email_from_name' => 'PPDB MTS Negeri 1 Wonogiri',
            
            // Extra Settings
            'extra_settings' => [
                'facebook' => 'https://facebook.com/mtsn1wonogiri',
                'instagram' => '@mtsn1wonogiri',
                'youtube' => 'MTS Negeri 1 Wonogiri',
                'contact_person' => 'Bapak Mulyono (0812-3456-7890)',
                'bank_name' => 'Bank BNI',
                'bank_account' => '1234567890',
                'bank_account_holder' => 'MTS Negeri 1 Wonogiri',
            ],
        ]);

        $this->command->info('Settings created successfully!');
    }
}
