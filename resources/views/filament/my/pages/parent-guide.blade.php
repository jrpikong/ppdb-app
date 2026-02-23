@php
    $applicationIndexUrl = \App\Filament\My\Resources\Applications\ApplicationResource::getUrl(panel: 'my');
    $applicationCreateUrl = \App\Filament\My\Resources\Applications\ApplicationResource::getUrl('create', panel: 'my');
    $paymentsUrl = \App\Filament\My\Resources\Payments\PaymentResource::getUrl(panel: 'my');
    $schedulesUrl = \App\Filament\My\Resources\Schedules\ScheduleResource::getUrl(panel: 'my');
    $profileUrl = route('filament.my.auth.profile');

    $wizardSteps = [
        ['title' => 'Step 1 - Admission Setup', 'desc' => 'Periode penerimaan dan jenjang akan terisi otomatis dari aplikasi yang dibuat.'],
        ['title' => 'Step 2 - Biodata Siswa', 'desc' => 'Isi nama lengkap, gender, tempat/tanggal lahir, kewarganegaraan, kontak, bahasa, dan minat.'],
        ['title' => 'Step 3 - Alamat & Sekolah Asal', 'desc' => 'Isi alamat domisili lengkap serta data sekolah sebelumnya bila ada.'],
        ['title' => 'Step 4 - Data Orang Tua / Wali', 'desc' => 'Minimal 1 data parent/wali wajib terisi beserta email dan nomor HP aktif.'],
        ['title' => 'Step 5 - Informasi Kesehatan', 'desc' => 'Lengkapi alergi, kondisi medis khusus, imunisasi, serta kontak darurat.'],
        ['title' => 'Step 6 - Upload Dokumen', 'desc' => 'Upload semua dokumen wajib sesuai format dan ukuran maksimal.'],
        ['title' => 'Step 7 - Review & Submit', 'desc' => 'Periksa semua data, pastikan saving seat sudah terverifikasi, lalu submit aplikasi.'],
    ];

    $requiredDocuments = [
        ['Foto Siswa 3x4 (1)', 'JPG/PNG, max 5 MB'],
        ['Foto Siswa 3x4 (2)', 'JPG/PNG, max 5 MB'],
        ['Foto Ayah 3x4', 'JPG/PNG, max 5 MB'],
        ['Foto Ibu 3x4', 'JPG/PNG, max 5 MB'],
        ['KTP Ayah', 'PDF/JPG, max 5 MB'],
        ['KTP Ibu', 'PDF/JPG, max 5 MB'],
        ['Akta Kelahiran', 'PDF/JPG, max 5 MB'],
        ['Kartu Keluarga', 'PDF/JPG, max 5 MB'],
        ['Rapor Terakhir', 'PDF, max 5 MB'],
    ];

    $faqs = [
        ['Saya tidak bisa submit aplikasi, kenapa?', 'Pastikan semua field wajib diisi, minimal 1 parent/wali tersedia, 9 dokumen wajib lengkap, dan Saving Seat Payment sudah berstatus Verified.'],
        ['Kenapa tombol submit tidak aktif?', 'Tombol submit akan aktif setelah pembayaran Saving Seat diverifikasi oleh Finance Admin.'],
        ['Bisa edit data setelah submit?', 'Tidak bisa. Setelah status submitted, data utama terkunci. Hubungi admin admissions jika ada koreksi penting.'],
        ['Dokumen saya ditolak, bagaimana?', 'Silakan upload ulang dokumen yang ditolak melalui portal My sesuai catatan perbaikan dari sekolah.'],
        ['Berapa lama verifikasi pembayaran?', 'Rata-rata 1-2 hari kerja setelah bukti transfer di-upload.'],
    ];
@endphp

<x-filament-panels::page>
    <style>
        .parent-guide-shell {
            background:
                radial-gradient(circle at 0 0, rgba(20, 184, 166, 0.14), transparent 30%),
                radial-gradient(circle at 100% 0, rgba(245, 158, 11, 0.14), transparent 26%),
                #f8fafc;
        }
        .parent-guide-link.is-active {
            background: #ffffff;
            border-color: #14b8a6;
            color: #0f172a;
            box-shadow: 0 10px 24px rgba(20, 184, 166, .13);
        }
        .parent-guide-content section {
            scroll-margin-top: 7rem;
        }
    </style>

    <div class="parent-guide-shell rounded-2xl p-3 sm:p-5">
        <div class="grid xl:grid-cols-[280px,1fr] gap-4 sm:gap-6">
            <aside class="xl:sticky xl:top-24 h-max">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Panduan Orang Tua</p>
                    <h2 class="mt-1 text-sm font-semibold text-slate-900">Navigasi Cepat</h2>
                    <input id="navFilter" type="text" placeholder="Cari: dokumen, FAQ..." class="mt-3 w-full rounded-lg border-slate-200 text-sm" />
                    <nav id="parentGuideNav" class="mt-3 space-y-2">
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#ringkasan">Ringkasan</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#registrasi">Registrasi & Login</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#aplikasi">Membuat Aplikasi</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#wizard">Wizard 7 Langkah</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#dokumen">Dokumen Wajib</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#pembayaran">Pembayaran</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#jadwal">Jadwal & Notifikasi</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#faq">FAQ</a>
                        <a class="parent-guide-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700" href="#checklist">Checklist Mandiri</a>
                    </nav>
                </div>
            </aside>

            <div class="parent-guide-content space-y-5">
                <section id="ringkasan" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="rounded-2xl bg-gradient-to-r from-cyan-700 to-teal-600 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.14em] font-bold text-cyan-100">Portal My Admissions</p>
                        <h1 class="mt-2 text-2xl font-extrabold">Panduan Lengkap Orang Tua / Wali</h1>
                        <p class="mt-2 text-sm text-cyan-50">Panduan ini fokus ke alur pengguna orang tua: mulai registrasi akun, membuat aplikasi, mengisi wizard, upload dokumen, pembayaran, sampai pemantauan status.</p>
                    </div>
                    <div class="mt-4 grid md:grid-cols-3 gap-3 text-sm">
                        <a href="{{ $applicationCreateUrl }}" class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 block hover:border-emerald-300">
                            <p class="font-semibold text-slate-900">Mulai Aplikasi Baru</p>
                            <p class="text-slate-600 mt-1">Klik untuk langsung masuk ke form pendaftaran.</p>
                        </a>
                        <a href="{{ $paymentsUrl }}" class="rounded-xl border border-amber-100 bg-amber-50 p-4 block hover:border-amber-300">
                            <p class="font-semibold text-slate-900">Cek Pembayaran</p>
                            <p class="text-slate-600 mt-1">Pantau status verifikasi pembayaran.</p>
                        </a>
                        <a href="{{ $schedulesUrl }}" class="rounded-xl border border-blue-100 bg-blue-50 p-4 block hover:border-blue-300">
                            <p class="font-semibold text-slate-900">Lihat Jadwal</p>
                            <p class="text-slate-600 mt-1">Konfirmasi interview/test/observasi.</p>
                        </a>
                    </div>
                </section>

                <section id="registrasi" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">1) Registrasi & Login</h3>
                    <div class="mt-3 grid lg:grid-cols-2 gap-4 text-sm">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="font-semibold text-slate-900">Registrasi Akun Baru</p>
                            <ol class="mt-2 list-decimal pl-5 text-slate-700 space-y-1">
                                <li>Buka halaman <code>/my/register</code>.</li>
                                <li>Isi nama, email, nomor HP, password, dan konfirmasi password.</li>
                                <li>Klik Register, lalu cek email untuk verifikasi.</li>
                                <li>Login setelah email terverifikasi.</li>
                            </ol>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="font-semibold text-slate-900">Login & Dashboard</p>
                            <ol class="mt-2 list-decimal pl-5 text-slate-700 space-y-1">
                                <li>Buka <code>/my/login</code>.</li>
                                <li>Masukkan email dan password.</li>
                                <li>Masuk ke dashboard untuk melihat prioritas tindakan.</li>
                            </ol>
                            <p class="mt-2 text-xs text-amber-700 font-semibold">Penting: verifikasi email wajib sebelum login.</p>
                        </div>
                    </div>
                </section>

                <section id="aplikasi" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">2) Membuat Aplikasi</h3>
                    <p class="mt-2 text-sm text-slate-700">Dari menu <a href="{{ $applicationIndexUrl }}" class="text-primary-600 underline">My Applications</a>, klik <strong>Start New Application</strong>, pilih program/jenjang, lalu lanjut isi wizard.</p>
                    <pre class="mt-3 rounded-xl bg-slate-950 text-slate-100 p-4 text-xs overflow-x-auto">Start New Application
-> pilih program/jenjang
-> sistem buat draft + nomor aplikasi
-> lanjut wizard 7 langkah
-> upload dokumen
-> upload saving seat
-> submit aplikasi (status: submitted)</pre>
                </section>

                <section id="wizard" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">3) Wizard Pengisian (7 Langkah)</h3>
                    <p class="mt-2 text-sm text-slate-700">Setiap langkah tersimpan otomatis saat klik <strong>Next</strong>.</p>
                    <div class="mt-3 grid md:grid-cols-2 gap-3">
                        @foreach ($wizardSteps as $step)
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="font-semibold text-slate-900">{{ $step['title'] }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="dokumen" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">4) Dokumen Wajib</h3>
                    <p class="mt-2 text-sm text-slate-700">Semua dokumen wajib harus terupload sebelum submit aplikasi.</p>
                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">No</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Dokumen</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Ketentuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($requiredDocuments as $index => $doc)
                                    <tr>
                                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $doc[0] }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $doc[1] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="pembayaran" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">5) Pembayaran Saving Seat</h3>
                    <p class="mt-2 text-sm text-slate-700">Sebelum submit aplikasi, pembayaran Saving Seat wajib diverifikasi oleh Finance Admin.</p>
                    <div class="mt-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Buka <a href="{{ $paymentsUrl }}" class="underline">My Payments</a>.</li>
                            <li>Pilih aplikasi, lihat rekening tujuan, dan transfer sesuai nominal.</li>
                            <li>Upload bukti transfer (tanggal, metode, bank, referensi).</li>
                            <li>Tunggu status berubah dari <code>Awaiting Verification</code> ke <code>Verified</code>.</li>
                            <li>Jika ditolak, upload ulang bukti dengan data yang benar.</li>
                        </ol>
                    </div>
                </section>

                <section id="jadwal" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">6) Jadwal, Notifikasi, dan Profil</h3>
                    <div class="mt-3 grid md:grid-cols-3 gap-3 text-sm">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="font-semibold text-slate-900">My Schedules</p>
                            <p class="mt-1 text-slate-600">Konfirmasi kehadiran interview/test/observasi atau ajukan reschedule.</p>
                            <a href="{{ $schedulesUrl }}" class="inline-block mt-2 underline">Buka jadwal</a>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="font-semibold text-slate-900">Notifikasi</p>
                            <p class="mt-1 text-slate-600">Pantau update status aplikasi, pembayaran, dokumen, dan jadwal terbaru.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="font-semibold text-slate-900">My Profile</p>
                            <p class="mt-1 text-slate-600">Perbarui nama, email, nomor HP, pekerjaan, dan alamat Anda.</p>
                            <a href="{{ $profileUrl }}" class="inline-block mt-2 underline">Edit profil</a>
                        </div>
                    </div>
                </section>

                <section id="faq" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">7) FAQ Singkat</h3>
                    <div class="mt-3 space-y-2">
                        @foreach ($faqs as $faq)
                            <details class="rounded-xl border border-slate-200 p-4">
                                <summary class="font-semibold text-slate-900 cursor-pointer">{{ $faq[0] }}</summary>
                                <p class="mt-2 text-sm text-slate-700">{{ $faq[1] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section id="checklist" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-extrabold text-slate-900">8) Checklist Mandiri Orang Tua</h3>
                    <ul class="mt-3 text-sm text-slate-700 grid md:grid-cols-2 gap-2">
                        <li>[ ] Akun sudah terverifikasi email</li>
                        <li>[ ] Draft aplikasi sudah dibuat</li>
                        <li>[ ] Semua step wizard sudah lengkap</li>
                        <li>[ ] 9 dokumen wajib sudah upload</li>
                        <li>[ ] Saving Seat sudah verified</li>
                        <li>[ ] Aplikasi sudah submit (submitted)</li>
                        <li>[ ] Jadwal interview/test sudah dicek</li>
                        <li>[ ] Notifikasi status selalu dipantau</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>

    <script>
        const links = [...document.querySelectorAll('#parentGuideNav .parent-guide-link')];
        const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
        const filter = document.getElementById('navFilter');

        const activate = (id) => links.forEach((link) => link.classList.toggle('is-active', link.getAttribute('href') === '#' + id));

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => { if (entry.isIntersecting) activate(entry.target.id); });
        }, {rootMargin: '-28% 0px -58% 0px', threshold: 0.01});

        sections.forEach((section) => observer.observe(section));

        filter.addEventListener('input', (event) => {
            const value = event.target.value.trim().toLowerCase();
            links.forEach((link) => {
                const visible = link.textContent.toLowerCase().includes(value);
                link.classList.toggle('hidden', !visible);
            });
        });
    </script>
</x-filament-panels::page>
