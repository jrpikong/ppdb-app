<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — Veritas Intercultural School</title>
    <meta name="description" content="Privacy Policy for Veritas Intercultural School Admission System">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:        #1a1a2e;
            --ink-muted:  #4a4a6a;
            --ink-light:  #8888aa;
            --accent:     #1e40af;
            --accent-soft:#eff6ff;
            --rule:       #e2e8f0;
            --bg:         #fafaf9;
            --white:      #ffffff;
            --radius:     10px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            font-size: 16px;
            line-height: 1.8;
            color: var(--ink);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        /* ── HEADER ─────────────────────────────────────────────────── */
        .site-header {
            background: var(--white);
            border-bottom: 1px solid var(--rule);
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .site-header-inner {
            max-width: 900px;
            margin: 0 auto;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--ink);
        }
        .logo-badge {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Serif Display', serif;
            font-size: 1rem;
            color: white;
            letter-spacing: -0.5px;
        }
        .logo-text {
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.2;
        }
        .logo-sub {
            font-size: 0.72rem;
            font-weight: 400;
            color: var(--ink-muted);
        }
        .back-link {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--accent);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            border: 1px solid var(--accent);
            transition: background 0.15s, color 0.15s;
        }
        .back-link:hover { background: var(--accent); color: white; }

        /* ── HERO ──────────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            padding: 4rem 2rem 3.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 16px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .hero-icon svg { width: 32px; height: 32px; color: white; }
        .hero h1 {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(2rem, 5vw, 2.75rem);
            color: white;
            margin-bottom: 0.75rem;
            position: relative;
        }
        .hero-meta {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
            position: relative;
        }
        .hero-meta span {
            display: inline-block;
            margin: 0 0.5rem;
        }

        /* ── LAYOUT ────────────────────────────────────────────────── */
        .layout {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem 6rem;
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 4rem;
            align-items: start;
        }
        @media (max-width: 720px) {
            .layout { grid-template-columns: 1fr; gap: 2rem; padding: 2rem 1.25rem 4rem; }
            .toc { display: none; }
        }

        /* ── TABLE OF CONTENTS ─────────────────────────────────────── */
        .toc {
            position: sticky;
            top: 88px;
        }
        .toc-title {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-light);
            margin-bottom: 0.875rem;
        }
        .toc-list {
            list-style: none;
            border-left: 2px solid var(--rule);
        }
        .toc-list li a {
            display: block;
            padding: 0.3rem 0 0.3rem 1rem;
            font-size: 0.82rem;
            color: var(--ink-muted);
            text-decoration: none;
            border-left: 2px solid transparent;
            margin-left: -2px;
            transition: color 0.15s, border-color 0.15s;
            line-height: 1.4;
        }
        .toc-list li a:hover {
            color: var(--accent);
            border-left-color: var(--accent);
        }

        /* ── CONTENT ───────────────────────────────────────────────── */
        .content { min-width: 0; }

        .intro-box {
            background: var(--accent-soft);
            border: 1px solid #bfdbfe;
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2.5rem;
            font-size: 0.9rem;
            color: #1e40af;
            line-height: 1.7;
        }
        .intro-box strong { font-weight: 600; }

        .section {
            margin-bottom: 2.75rem;
            padding-bottom: 2.75rem;
            border-bottom: 1px solid var(--rule);
        }
        .section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
        }

        .section-number {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-soft);
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            margin-bottom: 0.75rem;
        }

        h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.5rem;
            color: var(--ink);
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        p {
            color: var(--ink-muted);
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        p:last-child { margin-bottom: 0; }

        ul, ol {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        ul li, ol li {
            color: var(--ink-muted);
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
            line-height: 1.7;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.25rem 0;
            font-size: 0.875rem;
        }
        .data-table th {
            text-align: left;
            padding: 0.6rem 1rem;
            background: var(--accent-soft);
            color: var(--accent);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 2px solid #bfdbfe;
        }
        .data-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--rule);
            color: var(--ink-muted);
            vertical-align: top;
        }
        .data-table tr:last-child td { border-bottom: none; }

        .highlight-box {
            background: var(--white);
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            margin: 1.25rem 0;
        }
        .highlight-box p { margin-bottom: 0; }

        /* ── CONTACT CARD ──────────────────────────────────────────── */
        .contact-card {
            background: var(--white);
            border: 1px solid var(--rule);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .contact-card h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.75rem;
        }
        .contact-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.6rem;
            font-size: 0.88rem;
            color: var(--ink-muted);
        }
        .contact-row:last-child { margin-bottom: 0; }
        .contact-row .icon {
            width: 18px;
            flex-shrink: 0;
            margin-top: 2px;
            color: var(--accent);
        }

        /* ── FOOTER ─────────────────────────────────────────────────── */
        .site-footer {
            background: var(--white);
            border-top: 1px solid var(--rule);
            padding: 2rem;
            text-align: center;
        }
        .site-footer p {
            font-size: 0.82rem;
            color: var(--ink-light);
            margin-bottom: 0.35rem;
        }
        .site-footer a {
            color: var(--accent);
            text-decoration: none;
        }
        .site-footer a:hover { text-decoration: underline; }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 0.75rem;
        }
    </style>
</head>
<body>

{{-- ── HEADER ─────────────────────────────────────────────────────── --}}
<header class="site-header">
    <div class="site-header-inner">
        <a href="{{ url('/') }}" class="logo">
            <div class="logo-badge">V</div>
            <div>
                <div class="logo-text">Veritas Intercultural School</div>
                <div class="logo-sub">Admissions Portal</div>
            </div>
        </a>
        <a href="{{ route('filament.my.auth.login') }}" class="back-link">
            ← Back to Login
        </a>
    </div>
</header>

{{-- ── HERO ─────────────────────────────────────────────────────────── --}}
<div class="hero">
    <div class="hero-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
        </svg>
    </div>
    <h1>Privacy Policy</h1>
    <p class="hero-meta">
        <span>Veritas Intercultural School</span>
        <span>·</span>
        <span>Effective: January 1, 2025</span>
        <span>·</span>
        <span>Last updated: {{ date('F j, Y') }}</span>
    </p>
</div>

{{-- ── MAIN CONTENT ─────────────────────────────────────────────────── --}}
<div class="layout">

    {{-- Table of Contents --}}
    <aside class="toc">
        <div class="toc-title">On this page</div>
        <ul class="toc-list">
            <li><a href="#introduction">Introduction</a></li>
            <li><a href="#data-collected">Data We Collect</a></li>
            <li><a href="#how-we-use">How We Use It</a></li>
            <li><a href="#data-sharing">Data Sharing</a></li>
            <li><a href="#data-retention">Data Retention</a></li>
            <li><a href="#security">Security</a></li>
            <li><a href="#your-rights">Your Rights</a></li>
            <li><a href="#children">Children's Privacy</a></li>
            <li><a href="#cookies">Cookies</a></li>
            <li><a href="#changes">Policy Changes</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>
    </aside>

    {{-- Content --}}
    <main class="content">

        <div class="intro-box">
            <strong>Summary:</strong> Veritas Intercultural School collects personal data solely to process student admissions.
            We do not sell your data. You may request access, correction, or deletion of your information at any time.
            This policy applies to all users of the VIS Admissions Portal.
        </div>

        {{-- 1 --}}
        <div class="section" id="introduction">
            <span class="section-number">01</span>
            <h2>Introduction</h2>
            <p>
                Veritas Intercultural School ("VIS", "we", "us", or "our") operates an online admissions portal
                ("the Service") to facilitate the student enrollment process across our campuses in
                Bintaro, Kelapa Gading, and Bali.
            </p>
            <p>
                This Privacy Policy explains how we collect, use, disclose, and protect personal information
                when you use the Service. By registering and using the Service, you agree to the collection
                and use of information in accordance with this policy.
            </p>
            <p>
                If you have questions about this policy or our data practices, please contact us at
                <a href="mailto:privacy@vis.sch.id" style="color: var(--accent);">privacy@vis.sch.id</a>.
            </p>
        </div>

        {{-- 2 --}}
        <div class="section" id="data-collected">
            <span class="section-number">02</span>
            <h2>Data We Collect</h2>
            <p>We collect the following categories of information through the admissions process:</p>

            <table class="data-table">
                <thead>
                <tr>
                    <th>Category</th>
                    <th>Examples</th>
                    <th>Source</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong>Account Data</strong></td>
                    <td>Name, email address, phone number, password (hashed)</td>
                    <td>Provided by you at registration</td>
                </tr>
                <tr>
                    <td><strong>Student Data</strong></td>
                    <td>Full name, date of birth, nationality, gender, previous school</td>
                    <td>Provided by parent/guardian</td>
                </tr>
                <tr>
                    <td><strong>Parent/Guardian Data</strong></td>
                    <td>Name, relationship, contact details, occupation, ID number</td>
                    <td>Provided by parent/guardian</td>
                </tr>
                <tr>
                    <td><strong>Medical Data</strong></td>
                    <td>Blood type, allergies, medical conditions, emergency contacts</td>
                    <td>Provided by parent/guardian</td>
                </tr>
                <tr>
                    <td><strong>Supporting Documents</strong></td>
                    <td>Birth certificate, family card, school reports, photos</td>
                    <td>Uploaded by parent/guardian</td>
                </tr>
                <tr>
                    <td><strong>Payment Data</strong></td>
                    <td>Payment proof, transaction amount, payment method</td>
                    <td>Provided by parent/guardian</td>
                </tr>
                <tr>
                    <td><strong>Usage Data</strong></td>
                    <td>IP address, browser type, pages visited, timestamps</td>
                    <td>Collected automatically</td>
                </tr>
                </tbody>
            </table>

            <p>
                We collect only the minimum data necessary to assess and process admission applications.
                Sensitive data (medical information, ID numbers) is protected with additional safeguards.
            </p>
        </div>

        {{-- 3 --}}
        <div class="section" id="how-we-use">
            <span class="section-number">03</span>
            <h2>How We Use Your Data</h2>
            <p>We use the information collected for the following purposes:</p>
            <ul>
                <li><strong>Process your admission application</strong> — review, assess, and communicate decisions</li>
                <li><strong>Verify identity and documents</strong> — confirm the accuracy of submitted information</li>
                <li><strong>Schedule assessments and interviews</strong> — coordinate observation days, tests, and parent interviews</li>
                <li><strong>Process payments</strong> — verify registration fee payments and maintain financial records</li>
                <li><strong>Communicate with you</strong> — send status updates, notifications, and important announcements</li>
                <li><strong>Enroll accepted students</strong> — generate student IDs and set up school records upon acceptance</li>
                <li><strong>Improve the Service</strong> — analyze usage patterns to enhance the portal experience</li>
                <li><strong>Legal compliance</strong> — meet our obligations under Indonesian education regulations</li>
            </ul>
        </div>

        {{-- 4 --}}
        <div class="section" id="data-sharing">
            <span class="section-number">04</span>
            <h2>Data Sharing</h2>
            <p>We do <strong>not sell</strong> your personal data to third parties. We may share your data only in these limited circumstances:</p>

            <ul>
                <li>
                    <strong>Within VIS campuses:</strong> Admission data may be shared between VIS Bintaro, VIS Kelapa Gading,
                    and VIS Bali as operationally necessary (e.g., if you apply to multiple campuses).
                </li>
                <li>
                    <strong>Service providers:</strong> We use trusted third-party services (hosting, email delivery)
                    that process data on our behalf under strict data processing agreements.
                </li>
                <li>
                    <strong>Legal requirements:</strong> We may disclose information when required by Indonesian law,
                    court order, or government authority.
                </li>
                <li>
                    <strong>With your consent:</strong> Any other sharing will only occur with your explicit written consent.
                </li>
            </ul>

            <div class="highlight-box">
                <p>
                    <strong>International transfers:</strong> Our servers are located in Indonesia.
                    If data is transferred internationally for service delivery, we ensure adequate
                    protection through contractual safeguards.
                </p>
            </div>
        </div>

        {{-- 5 --}}
        <div class="section" id="data-retention">
            <span class="section-number">05</span>
            <h2>Data Retention</h2>
            <p>We retain your personal data for the following periods:</p>
            <ul>
                <li><strong>Rejected / withdrawn applications:</strong> 2 years from the decision date</li>
                <li><strong>Enrolled students:</strong> Duration of enrollment + 5 years after graduation or withdrawal</li>
                <li><strong>Account data (no application submitted):</strong> 1 year from last login, then deleted</li>
                <li><strong>Uploaded documents:</strong> Same period as the associated application</li>
                <li><strong>Payment records:</strong> 7 years (Indonesian tax and financial record requirements)</li>
                <li><strong>Activity logs:</strong> 12 months, then automatically purged</li>
            </ul>
            <p>
                After the retention period, data is securely deleted or anonymized.
                You may request earlier deletion — see <a href="#your-rights" style="color: var(--accent);">Your Rights</a> below.
            </p>
        </div>

        {{-- 6 --}}
        <div class="section" id="security">
            <span class="section-number">06</span>
            <h2>Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your data:</p>
            <ul>
                <li>All data transmitted via the portal is encrypted using HTTPS/TLS</li>
                <li>Passwords are hashed using bcrypt — we cannot recover your plaintext password</li>
                <li>Access to personal data is restricted by role (school staff only see their own campus data)</li>
                <li>All data access and changes are recorded in an audit log</li>
                <li>Uploaded files are stored in isolated, access-controlled storage</li>
                <li>Regular security reviews and vulnerability assessments</li>
            </ul>
            <p>
                While we take security seriously, no method of transmission over the Internet is 100% secure.
                If you believe your account has been compromised, contact us immediately at
                <a href="mailto:security@vis.sch.id" style="color: var(--accent);">security@vis.sch.id</a>.
            </p>
        </div>

        {{-- 7 --}}
        <div class="section" id="your-rights">
            <span class="section-number">07</span>
            <h2>Your Rights</h2>
            <p>Under applicable Indonesian privacy regulations, you have the following rights regarding your personal data:</p>

            <table class="data-table">
                <thead>
                <tr>
                    <th>Right</th>
                    <th>What it means</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong>Access</strong></td>
                    <td>Request a copy of all personal data we hold about you</td>
                </tr>
                <tr>
                    <td><strong>Correction</strong></td>
                    <td>Request correction of inaccurate or incomplete data</td>
                </tr>
                <tr>
                    <td><strong>Deletion</strong></td>
                    <td>Request deletion of your data (subject to legal retention requirements)</td>
                </tr>
                <tr>
                    <td><strong>Portability</strong></td>
                    <td>Receive your data in a structured, machine-readable format</td>
                </tr>
                <tr>
                    <td><strong>Withdraw Consent</strong></td>
                    <td>Withdraw consent for data processing at any time (may affect application status)</td>
                </tr>
                <tr>
                    <td><strong>Objection</strong></td>
                    <td>Object to processing of your data for specific purposes</td>
                </tr>
                </tbody>
            </table>

            <p>
                To exercise any of these rights, email us at
                <a href="mailto:privacy@vis.sch.id" style="color: var(--accent);">privacy@vis.sch.id</a>
                with your full name, email address, and a description of your request.
                We will respond within <strong>14 business days</strong>.
            </p>
        </div>

        {{-- 8 --}}
        <div class="section" id="children">
            <span class="section-number">08</span>
            <h2>Children's Privacy</h2>
            <p>
                Our admissions portal is used by parents and guardians on behalf of children.
                We do not knowingly collect personal information <strong>directly from children</strong> under 13 years of age.
                All application data for minor applicants must be submitted by a parent or legal guardian.
            </p>
            <p>
                If you believe a child has provided personal information to us directly without parental consent,
                please contact us immediately so we can remove that information.
            </p>
        </div>

        {{-- 9 --}}
        <div class="section" id="cookies">
            <span class="section-number">09</span>
            <h2>Cookies</h2>
            <p>We use cookies and similar technologies for the following purposes:</p>
            <ul>
                <li><strong>Session cookie:</strong> Required to keep you logged in during your session (essential, cannot be disabled)</li>
                <li><strong>CSRF token:</strong> Security token to prevent cross-site request forgery attacks (essential)</li>
                <li><strong>Preferences:</strong> Remember your theme and display preferences</li>
            </ul>
            <p>
                We do not use advertising cookies or third-party tracking cookies.
                You can configure your browser to refuse cookies, but this will prevent you from logging in.
            </p>
        </div>

        {{-- 10 --}}
        <div class="section" id="changes">
            <span class="section-number">10</span>
            <h2>Policy Changes</h2>
            <p>
                We may update this Privacy Policy from time to time to reflect changes in our practices
                or legal requirements. When we make significant changes, we will:
            </p>
            <ul>
                <li>Update the "Last updated" date at the top of this page</li>
                <li>Send a notification to your registered email address</li>
                <li>Display a notice on the admissions portal login page</li>
            </ul>
            <p>
                Your continued use of the Service after changes are posted constitutes your acceptance
                of the updated policy. We encourage you to review this page periodically.
            </p>
        </div>

        {{-- 11 --}}
        <div class="section" id="contact">
            <span class="section-number">11</span>
            <h2>Contact Us</h2>
            <p>
                If you have questions, concerns, or requests regarding this Privacy Policy or
                our data practices, please contact our Data Protection team:
            </p>

            <div class="contact-card">
                <h3>VIS Data Protection Officer</h3>

                <div class="contact-row">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span><a href="mailto:privacy@vis.sch.id" style="color: var(--accent);">privacy@vis.sch.id</a></span>
                </div>

                <div class="contact-row">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 8v-3a1 1 0 011-1h2a1 1 0 011 1v3"/>
                    </svg>
                    <span>Veritas Intercultural School — Bintaro, Kelapa Gading &amp; Bali</span>
                </div>

                <div class="contact-row">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Response time: within 14 business days</span>
                </div>
            </div>
        </div>

    </main>
</div>

{{-- ── FOOTER ──────────────────────────────────────────────────────────── --}}
<footer class="site-footer">
    <p>© {{ date('Y') }} Veritas Intercultural School. All rights reserved.</p>
    <p>This Privacy Policy is governed by the laws of the Republic of Indonesia.</p>
    <div class="footer-links">
        <a href="/terms">Terms &amp; Conditions</a>
        <a href="/privacy">Privacy Policy</a>
        <a href="mailto:privacy@vis.sch.id">Contact</a>
    </div>
</footer>

</body>
</html>
