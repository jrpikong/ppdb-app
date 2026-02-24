<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Veritas Intercultural School</title>
    <meta name="description" content="Syarat dan ketentuan penggunaan Portal Admissions Veritas Intercultural School (bilingual ID/EN).">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #172033;
            --ink-soft: #4e5a72;
            --line: #dce3ee;
            --accent: #1e40af;
            --accent-soft: #eef4ff;
            --bg: #f7f9fc;
            --white: #ffffff;
            --radius: 12px;
        }

        body {
            font-family: "DM Sans", sans-serif;
            font-size: 16px;
            line-height: 1.75;
            color: var(--ink);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 20;
            background: var(--white);
            border-bottom: 1px solid var(--line);
            padding: 0 1.5rem;
        }

        .site-header-inner {
            max-width: 980px;
            margin: 0 auto;
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            text-decoration: none;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .back-link {
            text-decoration: none;
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 0.45rem 0.85rem;
            transition: all 0.15s ease;
        }

        .back-link:hover {
            background: var(--accent);
            color: var(--white);
        }

        .hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: var(--white);
            padding: 3.25rem 1.5rem 2.75rem;
        }

        .hero-inner {
            max-width: 980px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: clamp(1.9rem, 4vw, 2.6rem);
            line-height: 1.2;
            margin-bottom: 0.6rem;
        }

        .hero p {
            font-size: 0.95rem;
            opacity: 0.88;
        }

        .layout {
            max-width: 980px;
            margin: 0 auto;
            padding: 2.4rem 1.5rem 4rem;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 2.2rem;
            align-items: start;
        }

        .toc {
            position: sticky;
            top: 90px;
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1rem;
        }

        .toc h2 {
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-soft);
            margin-bottom: 0.7rem;
        }

        .toc ul {
            list-style: none;
            display: grid;
            gap: 0.4rem;
        }

        .toc a {
            text-decoration: none;
            color: var(--ink-soft);
            font-size: 0.86rem;
        }

        .toc a:hover {
            color: var(--accent);
        }

        .content {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.5rem;
        }

        .notice {
            background: var(--accent-soft);
            border: 1px solid #bfd3ff;
            border-radius: 10px;
            padding: 1rem 1.1rem;
            color: #1d3f96;
            margin-bottom: 1.4rem;
            font-size: 0.9rem;
        }

        section {
            padding: 1.1rem 0;
            border-bottom: 1px solid var(--line);
        }

        section:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        h3 {
            font-size: 1.12rem;
            margin-bottom: 0.5rem;
            line-height: 1.35;
        }

        p {
            color: var(--ink-soft);
            margin-bottom: 0.7rem;
            font-size: 0.94rem;
        }

        p:last-child {
            margin-bottom: 0;
        }

        ul, ol {
            padding-left: 1.25rem;
            margin: 0.45rem 0 0.7rem;
        }

        li {
            color: var(--ink-soft);
            font-size: 0.94rem;
            margin-bottom: 0.35rem;
        }

        .lang {
            color: var(--ink);
            font-weight: 700;
        }

        .site-footer {
            max-width: 980px;
            margin: 0 auto;
            padding: 0 1.5rem 2rem;
            color: #6c7892;
            font-size: 0.82rem;
            text-align: center;
        }

        .site-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .site-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 860px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .toc {
                position: static;
            }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="site-header-inner">
        <a href="{{ url('/') }}" class="brand">Veritas Intercultural School - Admissions</a>
        <a href="{{ route('filament.my.auth.login') }}" class="back-link">Back to Login</a>
    </div>
</header>

<div class="hero">
    <div class="hero-inner">
        <h1>Terms and Conditions</h1>
        <p>Effective January 1, 2026. Last updated {{ date('F j, Y') }}.</p>
    </div>
</div>

<div class="layout">
    <aside class="toc">
        <h2>Contents</h2>
        <ul>
            <li><a href="#scope">1. Scope</a></li>
            <li><a href="#acceptance">2. Acceptance</a></li>
            <li><a href="#account">3. Account Security</a></li>
            <li><a href="#documents">4. Data and Documents</a></li>
            <li><a href="#payment">5. Fees and Payment</a></li>
            <li><a href="#selection">6. Admission Decision</a></li>
            <li><a href="#user-obligations">7. User Obligations</a></li>
            <li><a href="#school-rights">8. School Rights</a></li>
            <li><a href="#ip">9. Intellectual Property</a></li>
            <li><a href="#liability">10. Limitation of Liability</a></li>
            <li><a href="#law">11. Governing Law</a></li>
            <li><a href="#updates">12. Updates</a></li>
            <li><a href="#contact">13. Contact</a></li>
        </ul>
    </aside>

    <main class="content">
        <div class="notice">
            <span class="lang">ID:</span> Dokumen ini mengatur penggunaan Portal Admissions VIS untuk proses pendaftaran siswa baru.<br>
            <span class="lang">EN:</span> This document governs the use of the VIS Admissions Portal for student enrollment.
        </div>

        <section id="scope">
            <h3>1. Ruang Lingkup / Scope</h3>
            <p><span class="lang">ID:</span> Ketentuan ini berlaku untuk seluruh pengguna Portal Admissions VIS, termasuk orang tua/wali, calon peserta didik, dan pihak yang diberi kuasa.</p>
            <p><span class="lang">EN:</span> These terms apply to all VIS Admissions Portal users, including parents/guardians, prospective students, and authorized representatives.</p>
        </section>

        <section id="acceptance">
            <h3>2. Penerimaan Syarat / Acceptance</h3>
            <p><span class="lang">ID:</span> Dengan membuat akun, mencentang persetujuan, atau menggunakan portal, Anda menyetujui Terms and Conditions ini serta Privacy Policy VIS.</p>
            <p><span class="lang">EN:</span> By creating an account, checking acceptance, or using the portal, you agree to these Terms and Conditions and the VIS Privacy Policy.</p>
        </section>

        <section id="account">
            <h3>3. Akun dan Keamanan / Account Security</h3>
            <ul>
                <li><span class="lang">ID:</span> Anda wajib memberikan informasi akun yang benar dan aktif. <span class="lang">EN:</span> You must provide accurate and active account information.</li>
                <li><span class="lang">ID:</span> Anda bertanggung jawab atas kerahasiaan password. <span class="lang">EN:</span> You are responsible for keeping your password confidential.</li>
                <li><span class="lang">ID:</span> Lapor segera jika ada akses tanpa izin. <span class="lang">EN:</span> Report unauthorized access immediately.</li>
            </ul>
        </section>

        <section id="documents">
            <h3>4. Data dan Dokumen / Data and Documents</h3>
            <p><span class="lang">ID:</span> Semua data dan dokumen yang diunggah harus sah, akurat, dan tidak menyesatkan.</p>
            <p><span class="lang">EN:</span> All submitted data and documents must be lawful, accurate, and not misleading.</p>
            <p><span class="lang">ID:</span> Pemalsuan data dapat menyebabkan pembatalan proses admissions.</p>
            <p><span class="lang">EN:</span> Falsified information may result in cancellation of the admissions process.</p>
        </section>

        <section id="payment">
            <h3>5. Biaya dan Pembayaran / Fees and Payment</h3>
            <ul>
                <li><span class="lang">ID:</span> Biaya ditentukan sekolah dan dapat berubah sesuai kebijakan resmi. <span class="lang">EN:</span> Fees are determined by the school and may change under official policy.</li>
                <li><span class="lang">ID:</span> Pembayaran mengikuti instruksi resmi portal. <span class="lang">EN:</span> Payments must follow official portal instructions.</li>
                <li><span class="lang">ID:</span> Biaya yang telah dibayar pada umumnya tidak dapat dikembalikan kecuali dinyatakan lain secara tertulis. <span class="lang">EN:</span> Paid fees are generally non-refundable unless otherwise stated in writing.</li>
            </ul>
        </section>

        <section id="selection">
            <h3>6. Keputusan Penerimaan / Admission Decision</h3>
            <p><span class="lang">ID:</span> Pengajuan aplikasi tidak otomatis berarti diterima. Keputusan akhir berada pada sekolah dan bersifat final.</p>
            <p><span class="lang">EN:</span> Submitting an application does not guarantee acceptance. Final admission decisions are made by the school and are final.</p>
        </section>

        <section id="user-obligations">
            <h3>7. Kewajiban Pengguna / User Obligations</h3>
            <ul>
                <li><span class="lang">ID:</span> Tidak melakukan akses tanpa hak atau upaya mengganggu sistem. <span class="lang">EN:</span> Do not perform unauthorized access or attempt to disrupt the system.</li>
                <li><span class="lang">ID:</span> Tidak menyebarkan malware atau konten berbahaya. <span class="lang">EN:</span> Do not distribute malware or harmful content.</li>
                <li><span class="lang">ID:</span> Tidak menyalahgunakan identitas pihak lain. <span class="lang">EN:</span> Do not misuse another person's identity.</li>
            </ul>
        </section>

        <section id="school-rights">
            <h3>8. Hak Sekolah / School Rights</h3>
            <p><span class="lang">ID:</span> Sekolah berhak meminta verifikasi tambahan, menolak, menunda, atau membatalkan aplikasi jika ditemukan pelanggaran atau ketidaksesuaian data.</p>
            <p><span class="lang">EN:</span> The school may request additional verification, reject, delay, or cancel applications when violations or data inconsistencies are identified.</p>
        </section>

        <section id="ip">
            <h3>9. Kekayaan Intelektual / Intellectual Property</h3>
            <p><span class="lang">ID:</span> Seluruh konten portal merupakan milik VIS atau pemberi lisensinya dan tidak boleh digunakan ulang tanpa izin tertulis.</p>
            <p><span class="lang">EN:</span> All portal content belongs to VIS or its licensors and may not be reused without written permission.</p>
        </section>

        <section id="liability">
            <h3>10. Batas Tanggung Jawab / Limitation of Liability</h3>
            <p><span class="lang">ID:</span> Portal disediakan sebagaimana adanya. VIS tidak bertanggung jawab atas kerugian tidak langsung akibat gangguan teknis, force majeure, atau kesalahan input oleh pengguna.</p>
            <p><span class="lang">EN:</span> The portal is provided as-is. VIS is not liable for indirect losses caused by technical failures, force majeure events, or user input errors.</p>
        </section>

        <section id="law">
            <h3>11. Hukum yang Berlaku / Governing Law</h3>
            <p><span class="lang">ID:</span> Ketentuan ini diatur oleh hukum Republik Indonesia. Sengketa diselesaikan terlebih dahulu secara musyawarah sebelum menempuh jalur hukum.</p>
            <p><span class="lang">EN:</span> These terms are governed by the laws of the Republic of Indonesia. Disputes should first be addressed amicably before legal proceedings.</p>
        </section>

        <section id="updates">
            <h3>12. Perubahan Ketentuan / Updates</h3>
            <p><span class="lang">ID:</span> VIS dapat memperbarui ketentuan ini dan akan memberi pemberitahuan yang wajar melalui portal atau email terdaftar.</p>
            <p><span class="lang">EN:</span> VIS may update these terms and will provide reasonable notice via the portal or registered email.</p>
        </section>

        <section id="contact">
            <h3>13. Kontak / Contact</h3>
            <ul>
                <li><span class="lang">ID:</span> Admissions: <a href="mailto:admissions@vis.sch.id">admissions@vis.sch.id</a> <span class="lang">EN:</span> Admissions inquiries.</li>
                <li><span class="lang">ID:</span> Privacy/Legal: <a href="mailto:privacy@vis.sch.id">privacy@vis.sch.id</a> <span class="lang">EN:</span> Privacy and legal requests.</li>
            </ul>
        </section>
    </main>
</div>

<footer class="site-footer">
    <p>Copyright {{ date('Y') }} Veritas Intercultural School. All rights reserved.</p>
    <p>
        <a href="{{ route('privacy') }}">Privacy Policy</a>
        |
        <a href="{{ route('terms') }}">Terms and Conditions</a>
        |
        <a href="{{ route('filament.my.auth.login') }}">Login Portal</a>
    </p>
</footer>
</body>
</html>
