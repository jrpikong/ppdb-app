<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Letter of Acceptance – {{ $application->application_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            background: #ffffff;
        }

        /* ── HEADER ── */
        .header {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 20px 30px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            vertical-align: middle;
            color: #ffffff;
        }
        .header .logo-cell {
            width: 80px;
        }
        .header img.logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .header .school-info {
            padding-left: 16px;
        }
        .header .school-name {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header .school-full-name {
            font-size: 10pt;
            color: #c8d8e8;
            margin-top: 2px;
        }
        .header .school-contact {
            font-size: 9pt;
            color: #a0b8d0;
            margin-top: 6px;
        }

        /* ── GOLD DIVIDER ── */
        .gold-line {
            height: 4px;
            background-color: #f59e0b;
        }

        /* ── BODY ── */
        .body {
            padding: 28px 36px 20px 36px;
        }

        /* ref + date row */
        .ref-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }
        .ref-row td {
            font-size: 10pt;
            color: #4a5568;
        }
        .ref-row .ref-left {
            width: 50%;
        }
        .ref-row .ref-right {
            width: 50%;
            text-align: right;
        }
        .ref-row span.label {
            font-weight: bold;
            color: #1e3a5f;
        }

        /* badge */
        .badge-wrap {
            text-align: center;
            margin-bottom: 24px;
        }
        .badge {
            display: inline-block;
            background-color: #16a34a;
            color: #ffffff;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 8px 28px;
        }

        /* salutation + paragraphs */
        .salutation {
            font-size: 11.5pt;
            margin-bottom: 12px;
        }
        .para {
            font-size: 11pt;
            line-height: 1.65;
            margin-bottom: 14px;
            text-align: justify;
        }

        /* info table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0 22px 0;
            font-size: 10.5pt;
        }
        .info-table td {
            border: 1px solid #cbd5e0;
            padding: 8px 12px;
            vertical-align: top;
        }
        .info-table td.info-label {
            background-color: #eef4fb;
            width: 38%;
            font-weight: bold;
            color: #1e3a5f;
        }
        .info-table td.info-value {
            background-color: #ffffff;
            color: #1a1a2e;
        }

        /* next steps */
        .next-steps-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 8px;
        }
        .next-steps-list {
            margin-left: 18px;
            margin-bottom: 18px;
        }
        .next-steps-list li {
            font-size: 11pt;
            line-height: 1.7;
            color: #2d3748;
        }

        /* closing */
        .closing-para {
            font-size: 11pt;
            line-height: 1.65;
            margin-bottom: 24px;
            text-align: justify;
        }

        /* signature block */
        .signature-block {
            margin-top: 10px;
        }
        .signature-block .sign-label {
            font-size: 10.5pt;
            color: #4a5568;
            margin-bottom: 6px;
        }
        .signature-block img.signature {
            max-height: 60px;
            max-width: 180px;
            margin-bottom: 4px;
            display: block;
        }
        .signature-block .principal-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a5f;
        }
        .signature-block .principal-title {
            font-size: 10pt;
            color: #4a5568;
        }

        /* ── FOOTER ── */
        .footer {
            border-top: 1px solid #e2e8f0;
            background-color: #f7fafc;
            padding: 10px 36px;
            text-align: center;
            font-size: 9pt;
            color: #718096;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }
        .footer .footer-school {
            font-weight: bold;
            color: #1e3a5f;
        }
    </style>
</head>
<body>

    {{-- ══ HEADER LETTERHEAD ══ --}}
    <div class="header">
        <table>
            <tr>
                @if ($logoBase64)
                <td class="logo-cell">
                    <img class="logo" src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
                </td>
                @endif
                <td class="school-info">
                    <div class="school-name">{{ $school->name }}</div>
                    @if ($school->full_name && $school->full_name !== $school->name)
                    <div class="school-full-name">{{ $school->full_name }}</div>
                    @endif
                    <div class="school-contact">
                        @if ($school->address){{ $school->address }}@if ($school->city), {{ $school->city }}@endif@if ($school->country) – {{ $school->country }}@endif<br>@endif
                        @if ($school->phone)Tel: {{ $school->phone }}@if ($school->email)  |  @endif@endif
                        @if ($school->email)Email: {{ $school->email }}@if ($school->website)  |  @endif@endif
                        @if ($school->website){{ $school->website }}@endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══ GOLD DIVIDER ══ --}}
    <div class="gold-line"></div>

    {{-- ══ BODY ══ --}}
    <div class="body">

        {{-- Ref + Date --}}
        <table class="ref-row">
            <tr>
                <td class="ref-left">
                    <span class="label">Ref:</span> {{ $application->application_number }}
                </td>
                <td class="ref-right">
                    <span class="label">Date:</span> {{ \Carbon\Carbon::now()->format('d F Y') }}
                </td>
            </tr>
        </table>

        {{-- BADGE --}}
        <div class="badge-wrap">
            <span class="badge">LETTER OF ACCEPTANCE</span>
        </div>

        {{-- Salutation --}}
        <div class="salutation">Dear {{ $application->user->name }},</div>

        {{-- Opening paragraph --}}
        <div class="para">
            We are delighted to inform you that following a thorough review of the application submitted,
            <strong>{{ $application->student_full_name }}</strong> has been officially accepted to
            <strong>{{ $school->name }}</strong>
            @if ($academicYear)
            for Academic Year <strong>{{ $academicYear->name ?? $academicYear->year }}</strong>
            @endif.
            This decision reflects our confidence in {{ $application->student_display_name }}'s potential
            and our commitment to providing an exceptional educational experience.
        </div>

        {{-- Info Table --}}
        <table class="info-table">
            <tr>
                <td class="info-label">Student Name</td>
                <td class="info-value">{{ $application->student_full_name }}</td>
            </tr>
            <tr>
                <td class="info-label">Application No.</td>
                <td class="info-value">{{ $application->application_number }}</td>
            </tr>
            <tr>
                <td class="info-label">Level / Programme</td>
                <td class="info-value">{{ $level ? $level->name : '—' }}</td>
            </tr>
            <tr>
                <td class="info-label">Academic Year</td>
                <td class="info-value">
                    @if ($academicYear)
                        {{ $academicYear->name ?? $academicYear->year }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr>
                <td class="info-label">Date of Decision</td>
                <td class="info-value">
                    {{ $application->decision_made_at
                        ? \Carbon\Carbon::parse($application->decision_made_at)->format('d F Y')
                        : \Carbon\Carbon::now()->format('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="info-label">Status</td>
                <td class="info-value">
                    <strong>{{ ucfirst($application->status) }}</strong>
                </td>
            </tr>
        </table>

        {{-- Next Steps --}}
        <div class="next-steps-title">Next Steps:</div>
        <ul class="next-steps-list">
            <li>Submit all original documents to the school office within 7 working days.</li>
            <li>Complete the registration fee payment via the parent portal to secure your place.</li>
            <li>The orientation schedule and further instructions will be communicated separately.</li>
        </ul>

        {{-- Closing paragraph --}}
        <div class="closing-para">
            We look forward to warmly welcoming <strong>{{ $application->student_display_name }}</strong>
            to the {{ $school->name }} family. Should you have any questions or require further assistance,
            please do not hesitate to contact our admissions team
            @if ($school->email)
            at <strong>{{ $school->email }}</strong>
            @endif.
        </div>

        {{-- Signature Block --}}
        <div class="signature-block">
            <div class="sign-label">Sincerely,</div>
            @if ($signatureBase64)
            <img class="signature" src="data:image/png;base64,{{ $signatureBase64 }}" alt="Signature">
            @else
            <br><br>
            @endif
            <div class="principal-name">{{ $school->principal_name ?? $school->name . ' Administration' }}</div>
            <div class="principal-title">Principal, {{ $school->name }}</div>
        </div>

    </div>

    {{-- ══ FOOTER ══ --}}
    <div class="footer">
        <span class="footer-school">{{ $school->name }}</span>
        @if ($school->address) &nbsp;|&nbsp; {{ $school->address }}@if ($school->city), {{ $school->city }}@endif @endif
        @if ($school->website) &nbsp;|&nbsp; {{ $school->website }} @endif
        <br>
        Generated on {{ \Carbon\Carbon::now()->format('d F Y') }}
    </div>

</body>
</html>
