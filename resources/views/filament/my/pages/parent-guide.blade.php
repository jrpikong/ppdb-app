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
        .pg-shell {
            --pg-bg: #f3f7ff;
            --pg-card: #ffffff;
            --pg-border: #dbe4f0;
            --pg-text: #20314f;
            --pg-soft: #5e6f8f;
            --pg-brand: #0f766e;
            --pg-brand-2: #0d9488;
            --pg-accent: #f59e0b;
            color: var(--pg-text);
            background:
                radial-gradient(circle at 0% 0%, rgba(13, 148, 136, 0.16), transparent 30%),
                radial-gradient(circle at 100% 8%, rgba(245, 158, 11, 0.13), transparent 26%),
                var(--pg-bg);
            border: 1px solid var(--pg-border);
            border-radius: 20px;
            padding: 16px;
        }

        .pg-grid {
            display: grid;
            grid-template-columns: 290px 1fr;
            gap: 18px;
            align-items: start;
        }

        .pg-nav-wrap {
            position: sticky;
            top: 84px;
            border: 1px solid var(--pg-border);
            border-radius: 16px;
            background: #f9fbff;
            padding: 14px;
            box-shadow: 0 12px 30px rgba(32, 49, 79, 0.08);
        }

        .pg-nav-title {
            margin: 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: #64748b;
            font-weight: 800;
        }

        .pg-nav-subtitle {
            margin: 4px 0 0;
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .pg-nav-search {
            width: 100%;
            margin-top: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            outline: none;
        }

        .pg-nav-search:focus {
            border-color: var(--pg-brand-2);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.16);
        }

        .pg-nav {
            margin-top: 10px;
            display: grid;
            gap: 6px;
        }

        .pg-nav-link {
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 9px 11px;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            text-decoration: none;
            transition: 120ms ease;
        }

        .pg-nav-link:hover {
            background: #ffffff;
            border-color: #cfe8e5;
            color: #0f172a;
        }

        .pg-nav-link.is-active {
            background: #ffffff;
            border-color: #71c8c1;
            color: #0f172a;
            box-shadow: 0 10px 20px rgba(13, 148, 136, 0.14);
        }

        .pg-content {
            display: grid;
            gap: 14px;
        }

        .pg-card {
            border: 1px solid var(--pg-border);
            border-radius: 16px;
            background: var(--pg-card);
            padding: 18px;
            box-shadow: 0 10px 26px rgba(32, 49, 79, 0.06);
            scroll-margin-top: 95px;
        }

        .pg-hero {
            border-radius: 14px;
            background: linear-gradient(130deg, #0f766e 0%, #0d9488 70%);
            color: #ecfeff;
            padding: 18px;
        }

        .pg-hero p {
            margin: 0;
        }

        .pg-hero-mini {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .12em;
            opacity: .9;
            font-weight: 800;
        }

        .pg-hero-title {
            margin-top: 8px;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 800;
        }

        .pg-hero-desc {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.6;
            opacity: .95;
        }

        .pg-quick-grid {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .pg-quick {
            display: block;
            text-decoration: none;
            border-radius: 12px;
            border: 1px solid #dbe4f0;
            background: #ffffff;
            padding: 12px;
            transition: 120ms ease;
        }

        .pg-quick:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10);
        }

        .pg-quick-title {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }

        .pg-quick-desc {
            margin: 6px 0 0;
            font-size: 12px;
            line-height: 1.45;
            color: #64748b;
        }

        .pg-title {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .pg-sub {
            margin: 6px 0 0;
            font-size: 14px;
            color: var(--pg-soft);
            line-height: 1.55;
        }

        .pg-section-grid-2 {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .pg-section-grid-3 {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .pg-mini-card {
            border-radius: 12px;
            border: 1px solid #dbe4f0;
            padding: 12px;
            background: #fff;
        }

        .pg-mini-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .pg-mini-desc {
            margin: 6px 0 0;
            font-size: 13px;
            color: #64748b;
            line-height: 1.45;
        }

        .pg-list {
            margin: 8px 0 0;
            padding-left: 18px;
            font-size: 13px;
            line-height: 1.5;
            color: #334155;
        }

        .pg-list li + li {
            margin-top: 3px;
        }

        .pg-link {
            color: #0f766e;
            font-weight: 700;
            text-decoration: underline;
        }

        .pg-note {
            margin-top: 8px;
            font-size: 12px;
            color: #9a3412;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 8px 10px;
            font-weight: 700;
        }

        .pg-pre {
            margin-top: 10px;
            border-radius: 10px;
            background: #0f172a;
            color: #e2e8f0;
            border: 1px solid #1e293b;
            padding: 12px;
            font-size: 12px;
            line-height: 1.55;
            overflow-x: auto;
        }

        .pg-table-wrap {
            margin-top: 10px;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            overflow: auto;
            background: #fff;
        }

        .pg-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 640px;
            font-size: 13px;
        }

        .pg-table thead th {
            text-align: left;
            background: #f8fafc;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 12px;
            font-weight: 700;
        }

        .pg-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .pg-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .pg-details {
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
        }

        .pg-details summary {
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .pg-details p {
            margin: 8px 0 0;
            font-size: 13px;
            line-height: 1.5;
            color: #475569;
        }

        .pg-checklist {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            font-size: 13px;
            color: #334155;
        }

        .pg-check-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 10px;
            background: #fff;
        }

        @media (max-width: 1200px) {
            .pg-grid {
                grid-template-columns: 1fr;
            }
            .pg-nav-wrap {
                position: static;
            }
        }

        @media (max-width: 820px) {
            .pg-quick-grid,
            .pg-section-grid-2,
            .pg-section-grid-3,
            .pg-checklist {
                grid-template-columns: 1fr;
            }

            .pg-card {
                padding: 14px;
            }

            .pg-hero-title {
                font-size: 23px;
            }
        }
    </style>

    <div class="pg-shell">
        <div class="pg-grid">
            <aside>
                <div class="pg-nav-wrap">
                    <p class="pg-nav-title">Panduan Orang Tua</p>
                    <p class="pg-nav-subtitle">Navigasi Cepat</p>
                    <input id="navFilter" type="text" class="pg-nav-search" placeholder="Cari: dokumen, FAQ..." />
                    <nav id="parentGuideNav" class="pg-nav">
                        <a class="pg-nav-link" href="#ringkasan">Ringkasan</a>
                        <a class="pg-nav-link" href="#registrasi">Registrasi & Login</a>
                        <a class="pg-nav-link" href="#aplikasi">Membuat Aplikasi</a>
                        <a class="pg-nav-link" href="#wizard">Wizard 7 Langkah</a>
                        <a class="pg-nav-link" href="#dokumen">Dokumen Wajib</a>
                        <a class="pg-nav-link" href="#pembayaran">Pembayaran</a>
                        <a class="pg-nav-link" href="#jadwal">Jadwal & Notifikasi</a>
                        <a class="pg-nav-link" href="#faq">FAQ</a>
                        <a class="pg-nav-link" href="#checklist">Checklist Mandiri</a>
                    </nav>
                </div>
            </aside>

            <div class="pg-content">
                <section id="ringkasan" class="pg-card">
                    <div class="pg-hero">
                        <p class="pg-hero-mini">Portal My Admissions</p>
                        <p class="pg-hero-title">Panduan Lengkap Orang Tua / Wali</p>
                        <p class="pg-hero-desc">Panduan ini fokus ke alur pengguna orang tua: mulai registrasi akun, membuat aplikasi, mengisi wizard, upload dokumen, pembayaran, sampai pemantauan status.</p>
                    </div>
                    <div class="pg-quick-grid">
                        <a href="{{ $applicationCreateUrl }}" class="pg-quick">
                            <p class="pg-quick-title">Mulai Aplikasi Baru</p>
                            <p class="pg-quick-desc">Masuk langsung ke form pendaftaran siswa.</p>
                        </a>
                        <a href="{{ $paymentsUrl }}" class="pg-quick">
                            <p class="pg-quick-title">Cek Pembayaran</p>
                            <p class="pg-quick-desc">Pantau status verifikasi pembayaran saving seat.</p>
                        </a>
                        <a href="{{ $schedulesUrl }}" class="pg-quick">
                            <p class="pg-quick-title">Lihat Jadwal</p>
                            <p class="pg-quick-desc">Konfirmasi interview, test, dan observasi.</p>
                        </a>
                    </div>
                </section>

                <section id="registrasi" class="pg-card">
                    <h3 class="pg-title">1) Registrasi & Login</h3>
                    <p class="pg-sub">Pastikan akun sudah terverifikasi email agar bisa masuk ke portal dan membuat aplikasi.</p>
                    <div class="pg-section-grid-2">
                        <div class="pg-mini-card">
                            <p class="pg-mini-title">Registrasi Akun Baru</p>
                            <ol class="pg-list">
                                <li>Buka halaman <code>/my/register</code>.</li>
                                <li>Isi nama, email, nomor HP, password, dan konfirmasi password.</li>
                                <li>Klik Register, lalu cek email untuk verifikasi.</li>
                                <li>Login setelah email terverifikasi.</li>
                            </ol>
                        </div>
                        <div class="pg-mini-card">
                            <p class="pg-mini-title">Login & Dashboard</p>
                            <ol class="pg-list">
                                <li>Buka <code>/my/login</code>.</li>
                                <li>Masukkan email dan password.</li>
                                <li>Masuk ke dashboard untuk melihat prioritas tindakan.</li>
                            </ol>
                            <div class="pg-note">Penting: verifikasi email wajib sebelum login.</div>
                        </div>
                    </div>
                </section>

                <section id="aplikasi" class="pg-card">
                    <h3 class="pg-title">2) Membuat Aplikasi</h3>
                    <p class="pg-sub">Dari menu <a class="pg-link" href="{{ $applicationIndexUrl }}">My Applications</a>, klik <strong>Start New Application</strong>, pilih program/jenjang, lalu lanjut isi wizard.</p>
                    <pre class="pg-pre">Start New Application
-> pilih program/jenjang
-> sistem buat draft + nomor aplikasi
-> lanjut wizard 7 langkah
-> upload dokumen
-> upload saving seat
-> submit aplikasi (status: submitted)</pre>
                </section>

                <section id="wizard" class="pg-card">
                    <h3 class="pg-title">3) Wizard Pengisian (7 Langkah)</h3>
                    <p class="pg-sub">Setiap langkah tersimpan otomatis saat klik <strong>Next</strong>.</p>
                    <div class="pg-section-grid-2">
                        @foreach ($wizardSteps as $step)
                            <div class="pg-mini-card">
                                <p class="pg-mini-title">{{ $step['title'] }}</p>
                                <p class="pg-mini-desc">{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="dokumen" class="pg-card">
                    <h3 class="pg-title">4) Dokumen Wajib</h3>
                    <p class="pg-sub">Semua dokumen wajib harus terupload sebelum submit aplikasi.</p>
                    <div class="pg-table-wrap">
                        <table class="pg-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Dokumen</th>
                                    <th>Ketentuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requiredDocuments as $index => $doc)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $doc[0] }}</strong></td>
                                        <td>{{ $doc[1] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="pembayaran" class="pg-card">
                    <h3 class="pg-title">5) Pembayaran Saving Seat</h3>
                    <p class="pg-sub">Sebelum submit aplikasi, pembayaran Saving Seat wajib diverifikasi oleh Finance Admin.</p>
                    <div class="pg-mini-card" style="margin-top: 10px;">
                        <ol class="pg-list">
                            <li>Buka <a class="pg-link" href="{{ $paymentsUrl }}">My Payments</a>.</li>
                            <li>Pilih aplikasi, lihat rekening tujuan, dan transfer sesuai nominal.</li>
                            <li>Upload bukti transfer (tanggal, metode, bank, referensi).</li>
                            <li>Tunggu status berubah dari <code>Awaiting Verification</code> ke <code>Verified</code>.</li>
                            <li>Jika ditolak, upload ulang bukti dengan data yang benar.</li>
                        </ol>
                    </div>
                </section>

                <section id="jadwal" class="pg-card">
                    <h3 class="pg-title">6) Jadwal, Notifikasi, dan Profil</h3>
                    <div class="pg-section-grid-3">
                        <div class="pg-mini-card">
                            <p class="pg-mini-title">My Schedules</p>
                            <p class="pg-mini-desc">Konfirmasi kehadiran interview/test/observasi atau ajukan reschedule.</p>
                            <p style="margin-top: 8px;"><a class="pg-link" href="{{ $schedulesUrl }}">Buka jadwal</a></p>
                        </div>
                        <div class="pg-mini-card">
                            <p class="pg-mini-title">Notifikasi</p>
                            <p class="pg-mini-desc">Pantau update status aplikasi, pembayaran, dokumen, dan jadwal terbaru.</p>
                        </div>
                        <div class="pg-mini-card">
                            <p class="pg-mini-title">My Profile</p>
                            <p class="pg-mini-desc">Perbarui nama, email, nomor HP, pekerjaan, dan alamat Anda.</p>
                            <p style="margin-top: 8px;"><a class="pg-link" href="{{ $profileUrl }}">Edit profil</a></p>
                        </div>
                    </div>
                </section>

                <section id="faq" class="pg-card">
                    <h3 class="pg-title">7) FAQ Singkat</h3>
                    <div style="display: grid; gap: 8px; margin-top: 10px;">
                        @foreach ($faqs as $faq)
                            <details class="pg-details">
                                <summary>{{ $faq[0] }}</summary>
                                <p>{{ $faq[1] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section id="checklist" class="pg-card">
                    <h3 class="pg-title">8) Checklist Mandiri Orang Tua</h3>
                    <p class="pg-sub">Gunakan daftar ini sebelum menekan tombol submit aplikasi.</p>
                    <div class="pg-checklist">
                        <div class="pg-check-item">[ ] Akun sudah terverifikasi email</div>
                        <div class="pg-check-item">[ ] Draft aplikasi sudah dibuat</div>
                        <div class="pg-check-item">[ ] Semua step wizard sudah lengkap</div>
                        <div class="pg-check-item">[ ] 9 dokumen wajib sudah upload</div>
                        <div class="pg-check-item">[ ] Saving Seat sudah verified</div>
                        <div class="pg-check-item">[ ] Aplikasi sudah submit (submitted)</div>
                        <div class="pg-check-item">[ ] Jadwal interview/test sudah dicek</div>
                        <div class="pg-check-item">[ ] Notifikasi status selalu dipantau</div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        const links = [...document.querySelectorAll('#parentGuideNav .pg-nav-link')];
        const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
        const filter = document.getElementById('navFilter');

        const activate = (id) => {
            links.forEach((link) => {
                const isActive = link.getAttribute('href') === '#' + id;
                link.classList.toggle('is-active', isActive);
            });
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activate(entry.target.id);
                }
            });
        }, { rootMargin: '-28% 0px -58% 0px', threshold: 0.01 });

        sections.forEach((section) => observer.observe(section));

        filter.addEventListener('input', (event) => {
            const value = event.target.value.trim().toLowerCase();
            links.forEach((link) => {
                const visible = link.textContent.toLowerCase().includes(value);
                link.style.display = visible ? '' : 'none';
            });
        });
    </script>
</x-filament-panels::page>
