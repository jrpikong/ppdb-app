<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Veritas Intercultural School</title>
    <meta name="description" content="Kebijakan privasi Portal Admissions Veritas Intercultural School (bilingual ID/EN).">
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
        <h1>Privacy Policy</h1>
        <p>Effective January 1, 2026. Last updated {{ date('F j, Y') }}.</p>
    </div>
</div>

<div class="layout">
    <aside class="toc">
        <h2>Contents</h2>
        <ul>
            <li><a href="#scope">1. Scope</a></li>
            <li><a href="#data-types">2. Data Types</a></li>
            <li><a href="#purposes">3. Processing Purposes</a></li>
            <li><a href="#legal-basis">4. Legal Basis</a></li>
            <li><a href="#sharing">5. Data Sharing</a></li>
            <li><a href="#retention">6. Retention</a></li>
            <li><a href="#security">7. Security</a></li>
            <li><a href="#rights">8. Data Subject Rights</a></li>
            <li><a href="#cookies">9. Cookies</a></li>
            <li><a href="#children">10. Children Data</a></li>
            <li><a href="#cross-border">11. Cross-Border Transfer</a></li>
            <li><a href="#updates">12. Policy Updates</a></li>
            <li><a href="#contact">13. Contact</a></li>
        </ul>
    </aside>

    <main class="content">
        <div class="notice">
            <span class="lang">ID:</span> VIS memproses data pribadi hanya untuk tujuan admissions yang sah, aman, dan terukur.<br>
            <span class="lang">EN:</span> VIS processes personal data solely for legitimate, secure, and accountable admissions purposes.
        </div>

        <section id="scope">
            <h3>1. Ruang Lingkup / Scope</h3>
            <p><span class="lang">ID:</span> Kebijakan ini berlaku untuk seluruh data pribadi yang diproses melalui Portal Admissions VIS.</p>
            <p><span class="lang">EN:</span> This policy applies to all personal data processed through the VIS Admissions Portal.</p>
        </section>

        <section id="data-types">
            <h3>2. Jenis Data / Data Types</h3>
            <ul>
                <li><span class="lang">ID:</span> Data akun, identitas, dan kontak. <span class="lang">EN:</span> Account, identity, and contact data.</li>
                <li><span class="lang">ID:</span> Data calon peserta didik dan orang tua/wali. <span class="lang">EN:</span> Prospective student and parent/guardian data.</li>
                <li><span class="lang">ID:</span> Dokumen pendukung admissions dan data transaksi. <span class="lang">EN:</span> Admissions supporting documents and transaction data.</li>
                <li><span class="lang">ID:</span> Data teknis seperti IP, perangkat, dan log sistem. <span class="lang">EN:</span> Technical data such as IP, device, and system logs.</li>
            </ul>
        </section>

        <section id="purposes">
            <h3>3. Tujuan Pemrosesan / Processing Purposes</h3>
            <ol>
                <li><span class="lang">ID:</span> Verifikasi identitas dan akun. <span class="lang">EN:</span> Identity and account verification.</li>
                <li><span class="lang">ID:</span> Evaluasi aplikasi admissions. <span class="lang">EN:</span> Admissions application assessment.</li>
                <li><span class="lang">ID:</span> Komunikasi proses admissions. <span class="lang">EN:</span> Admissions process communication.</li>
                <li><span class="lang">ID:</span> Validasi pembayaran dan administrasi. <span class="lang">EN:</span> Payment validation and administration.</li>
                <li><span class="lang">ID:</span> Keamanan sistem dan kepatuhan hukum. <span class="lang">EN:</span> System security and legal compliance.</li>
            </ol>
        </section>

        <section id="legal-basis">
            <h3>4. Dasar Pemrosesan / Legal Basis</h3>
            <p><span class="lang">ID:</span> Pemrosesan dilakukan berdasarkan persetujuan, kebutuhan operasional admissions, kewajiban hukum, dan kepentingan sah sekolah.</p>
            <p><span class="lang">EN:</span> Processing is based on consent, admissions operational necessity, legal obligations, and the school's legitimate interests.</p>
        </section>

        <section id="sharing">
            <h3>5. Berbagi Data / Data Sharing</h3>
            <p><span class="lang">ID:</span> Data dapat dibagikan secara terbatas kepada tim internal terkait, penyedia layanan resmi, atau otoritas berwenang sesuai hukum.</p>
            <p><span class="lang">EN:</span> Data may be shared on a limited basis with relevant internal teams, authorized service providers, or competent authorities as required by law.</p>
            <p><span class="lang">ID:</span> VIS tidak menjual data pribadi. <span class="lang">EN:</span> VIS does not sell personal data.</p>
        </section>

        <section id="retention">
            <h3>6. Retensi Data / Retention</h3>
            <p><span class="lang">ID:</span> Data disimpan selama diperlukan untuk proses admissions dan kewajiban hukum, lalu dihapus atau dianonimkan sesuai kebijakan.</p>
            <p><span class="lang">EN:</span> Data is retained as long as necessary for admissions and legal obligations, then deleted or anonymized under policy.</p>
        </section>

        <section id="security">
            <h3>7. Keamanan Data / Security</h3>
            <ul>
                <li><span class="lang">ID:</span> Enkripsi koneksi (HTTPS/TLS). <span class="lang">EN:</span> Encrypted connections (HTTPS/TLS).</li>
                <li><span class="lang">ID:</span> Kontrol akses berbasis peran. <span class="lang">EN:</span> Role-based access controls.</li>
                <li><span class="lang">ID:</span> Logging aktivitas penting. <span class="lang">EN:</span> Logging of critical activities.</li>
            </ul>
        </section>

        <section id="rights">
            <h3>8. Hak Subjek Data / Data Subject Rights</h3>
            <p><span class="lang">ID:</span> Anda dapat meminta akses, koreksi, penghapusan (dengan batas hukum), pembatasan pemrosesan, atau penarikan persetujuan.</p>
            <p><span class="lang">EN:</span> You may request access, correction, deletion (subject to legal limits), processing restriction, or consent withdrawal.</p>
        </section>

        <section id="cookies">
            <h3>9. Cookies</h3>
            <p><span class="lang">ID:</span> Portal menggunakan cookies esensial untuk autentikasi sesi, keamanan, dan preferensi antarmuka.</p>
            <p><span class="lang">EN:</span> The portal uses essential cookies for session authentication, security, and interface preferences.</p>
        </section>

        <section id="children">
            <h3>10. Data Anak / Children Data</h3>
            <p><span class="lang">ID:</span> Data anak diproses atas nama orang tua/wali yang sah untuk keperluan admissions.</p>
            <p><span class="lang">EN:</span> Children's data is processed on behalf of lawful parents/guardians for admissions purposes.</p>
        </section>

        <section id="cross-border">
            <h3>11. Transfer Lintas Negara / Cross-Border Transfer</h3>
            <p><span class="lang">ID:</span> Jika diperlukan transfer lintas negara, VIS menerapkan perlindungan kontraktual dan kontrol keamanan yang memadai.</p>
            <p><span class="lang">EN:</span> Where cross-border transfer is necessary, VIS applies adequate contractual safeguards and security controls.</p>
        </section>

        <section id="updates">
            <h3>12. Perubahan Kebijakan / Policy Updates</h3>
            <p><span class="lang">ID:</span> Kebijakan ini dapat diperbarui sewaktu-waktu dan perubahan material akan diinformasikan melalui portal atau email.</p>
            <p><span class="lang">EN:</span> This policy may be updated from time to time, and material changes will be notified via the portal or email.</p>
        </section>

        <section id="contact">
            <h3>13. Kontak / Contact</h3>
            <ul>
                <li><span class="lang">ID:</span> Privacy: <a href="mailto:privacy@vis.sch.id">privacy@vis.sch.id</a> <span class="lang">EN:</span> Privacy requests.</li>
                <li><span class="lang">ID:</span> Admissions: <a href="mailto:admissions@vis.sch.id">admissions@vis.sch.id</a> <span class="lang">EN:</span> Admissions inquiries.</li>
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
