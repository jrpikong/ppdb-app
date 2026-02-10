<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentType;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentTypes = [
            [
                'code' => '001',
                'name' => 'Biaya Pendaftaran',
                'description' => 'Biaya pendaftaran PPDB (non-refundable)',
                'amount' => 200000,
                'is_mandatory' => true,
                'is_active' => true,
                'payment_instructions' => '<h4>Cara Pembayaran:</h4>
<ol>
<li>Transfer ke rekening BNI 1234567890 a/n MTS Negeri 1 Wonogiri</li>
<li>Simpan bukti transfer</li>
<li>Upload bukti transfer pada sistem</li>
<li>Tunggu verifikasi dari panitia (maksimal 2x24 jam)</li>
</ol>
<p><strong>Catatan:</strong> Biaya pendaftaran tidak dapat dikembalikan.</p>',
                'bank_name' => 'Bank BNI',
                'account_number' => '1234567890',
                'account_holder' => 'MTS Negeri 1 Wonogiri',
            ],
            [
                'code' => '002',
                'name' => 'Seragam',
                'description' => 'Paket seragam lengkap (putih-putih, batik, olahraga, pramuka)',
                'amount' => 1000000,
                'is_mandatory' => true,
                'is_active' => true,
                'payment_instructions' => '<h4>Rincian Paket Seragam:</h4>
<ul>
<li>2 stel seragam putih-putih</li>
<li>2 stel seragam batik</li>
<li>1 stel seragam olahraga</li>
<li>1 stel seragam pramuka</li>
<li>2 peci/jilbab</li>
<li>1 ikat pinggang</li>
<li>1 dasi</li>
</ul>
<p>Transfer ke rekening BNI 1234567890 a/n MTS Negeri 1 Wonogiri</p>',
                'bank_name' => 'Bank BNI',
                'account_number' => '1234567890',
                'account_holder' => 'MTS Negeri 1 Wonogiri',
            ],
            [
                'code' => '003',
                'name' => 'Topi',
                'description' => 'Topi sekolah',
                'amount' => 20000,
                'is_mandatory' => false,
                'is_active' => true,
                'payment_instructions' => '<p>Transfer ke rekening BNI 1234567890 a/n MTS Negeri 1 Wonogiri</p>',
                'bank_name' => 'Bank BNI',
                'account_number' => '1234567890',
                'account_holder' => 'MTS Negeri 1 Wonogiri',
            ],
            [
                'code' => '004',
                'name' => 'Buku Paket',
                'description' => 'Paket buku pelajaran semester 1',
                'amount' => 500000,
                'is_mandatory' => true,
                'is_active' => true,
                'payment_instructions' => '<h4>Paket Buku Pelajaran:</h4>
<ul>
<li>Buku Al-Quran Hadist</li>
<li>Buku Akidah Akhlak</li>
<li>Buku Fiqih</li>
<li>Buku Bahasa Arab</li>
<li>Buku Matematika</li>
<li>Buku IPA</li>
<li>Buku IPS</li>
<li>Buku Bahasa Indonesia</li>
<li>Buku Bahasa Inggris</li>
</ul>
<p>Transfer ke rekening BNI 1234567890 a/n MTS Negeri 1 Wonogiri</p>',
                'bank_name' => 'Bank BNI',
                'account_number' => '1234567890',
                'account_holder' => 'MTS Negeri 1 Wonogiri',
            ],
            [
                'code' => '005',
                'name' => 'Daftar Ulang',
                'description' => 'Biaya daftar ulang siswa baru',
                'amount' => 300000,
                'is_mandatory' => true,
                'is_active' => true,
                'payment_instructions' => '<h4>Biaya Daftar Ulang Meliputi:</h4>
<ul>
<li>Biaya administrasi</li>
<li>Kartu pelajar</li>
<li>ID Card</li>
<li>Buku penghubung</li>
<li>Materai</li>
</ul>
<p>Transfer ke rekening BNI 1234567890 a/n MTS Negeri 1 Wonogiri</p>
<p><strong>Penting:</strong> Pembayaran daftar ulang dilakukan setelah pengumuman kelulusan.</p>',
                'bank_name' => 'Bank BNI',
                'account_number' => '1234567890',
                'account_holder' => 'MTS Negeri 1 Wonogiri',
            ],
        ];

        foreach ($paymentTypes as $type) {
            PaymentType::create($type);
        }

        $this->command->info('Payment Types created successfully!');
        $this->command->info('Total mandatory payment: Rp ' . number_format(
            collect($paymentTypes)->where('is_mandatory', true)->sum('amount'), 0, ',', '.'
        ));
    }
}
