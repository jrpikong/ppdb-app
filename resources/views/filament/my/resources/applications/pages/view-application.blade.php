@php
    /** @var \App\Filament\My\Resources\Applications\Pages\ViewApplication $this */
    $record = $this->getRecordForView();
    $statusUi = $this->getStatusBadgeUi((string) $record->status);
    $uploadedDocumentsCount = $record->documents->count();
    $readinessErrors = $this->getReadinessErrors();
    $timeline = $this->getStatusTimeline();
    $showPostAcceptanceProgress = $this->shouldShowPostAcceptanceProgress();
    $postAcceptanceProgress = $showPostAcceptanceProgress ? $this->getPostAcceptanceProgress() : null;

    $completion = (int) $record->getCompletionPercentage();
    $isDraft = $record->status === 'draft';
    $isAcceptedOrEnrolled = in_array($record->status, ['accepted', 'enrolled'], true);
    $isTerminal = in_array($record->status, ['rejected', 'enrolled', 'withdrawn'], true);

    $editUrl = \App\Filament\My\Resources\Applications\ApplicationResource::getUrl('edit', ['record' => $record], panel: 'my');
    $listUrl = \App\Filament\My\Resources\Applications\ApplicationResource::getUrl(panel: 'my');
    $paymentsUrl = \App\Filament\My\Resources\Payments\PaymentResource::getUrl(panel: 'my');
    $schedulesUrl = \App\Filament\My\Resources\Schedules\ScheduleResource::getUrl(panel: 'my');

    $statusIndexMap = collect($timeline)->pluck('label', 'status')->keys()->flip()->toArray();
    $currentTimelineIndex = $statusIndexMap[$record->status] ?? -1;

    $documentStatusMap = [
        'pending' => ['label' => 'Pending', 'bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa'],
        'submitted' => ['label' => 'Submitted', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
        'approved' => ['label' => 'Approved', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
        'verified' => ['label' => 'Verified', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
        'rejected' => ['label' => 'Rejected', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
        'resubmit' => ['label' => 'Need Resubmit', 'bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
    ];

    $paymentStatusMap = [
        'pending' => ['label' => 'Pending', 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'],
        'submitted' => ['label' => 'Awaiting Verification', 'bg' => '#fffbeb', 'text' => '#b45309', 'border' => '#fde68a'],
        'verified' => ['label' => 'Verified', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
        'rejected' => ['label' => 'Rejected', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
        'refunded' => ['label' => 'Refunded', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
    ];

    $scheduleStatusMap = [
        'scheduled' => ['label' => 'Scheduled', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
        'confirmed' => ['label' => 'Confirmed', 'bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
        'completed' => ['label' => 'Completed', 'bg' => '#ecfeff', 'text' => '#0f766e', 'border' => '#99f6e4'],
        'cancelled' => ['label' => 'Cancelled', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
        'rescheduled' => ['label' => 'Rescheduled', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
        'no_show' => ['label' => 'No Show', 'bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
    ];
@endphp

<x-filament-panels::page>
    <style>
        .av-shell {
            --av-bg: #f2f7ff;
            --av-card: #ffffff;
            --av-border: #dbe4f0;
            --av-text: #20314f;
            --av-muted: #607193;
            --av-brand: #0f766e;
            background:
                radial-gradient(circle at 0% 0%, rgba(13, 148, 136, .14), transparent 30%),
                radial-gradient(circle at 100% 4%, rgba(245, 158, 11, .14), transparent 25%),
                var(--av-bg);
            border: 1px solid var(--av-border);
            border-radius: 20px;
            padding: 16px;
            color: var(--av-text);
        }

        .av-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 16px;
            align-items: start;
        }

        .av-side {
            position: sticky;
            top: 84px;
            border: 1px solid var(--av-border);
            border-radius: 14px;
            background: #f9fbff;
            padding: 14px;
            box-shadow: 0 12px 28px rgba(32, 49, 79, .08);
        }

        .av-side h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .av-side p {
            margin: 5px 0 0;
            font-size: 12px;
            color: var(--av-muted);
        }

        .av-side-grid {
            margin-top: 12px;
            display: grid;
            gap: 8px;
        }

        .av-kpi {
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            background: #fff;
            padding: 10px;
        }

        .av-kpi-label {
            margin: 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            font-weight: 700;
        }

        .av-kpi-value {
            margin: 4px 0 0;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .av-links {
            margin-top: 12px;
            display: grid;
            gap: 7px;
        }

        .av-link {
            display: block;
            text-decoration: none;
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            background: #fff;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: 700;
            color: #0f766e;
        }

        .av-main {
            display: grid;
            gap: 12px;
        }

        .av-card {
            border: 1px solid var(--av-border);
            border-radius: 14px;
            background: var(--av-card);
            padding: 16px;
            box-shadow: 0 10px 24px rgba(32, 49, 79, .06);
        }

        .av-hero {
            border-radius: 12px;
            background: linear-gradient(128deg, #0f766e 0%, #0d9488 74%);
            color: #f0fdfa;
            padding: 16px;
        }

        .av-hero-mini {
            margin: 0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .12em;
            opacity: .9;
            font-weight: 800;
        }

        .av-hero-title {
            margin: 6px 0 0;
            font-size: 30px;
            font-weight: 900;
            line-height: 1.2;
            color: #ffffff;
        }

        .av-hero-sub {
            margin: 7px 0 0;
            font-size: 13px;
            line-height: 1.55;
            opacity: .95;
        }

        .av-badge {
            display: inline-block;
            border-radius: 999px;
            padding: 6px 10px;
            border: 1px solid transparent;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }

        .av-grid-2 {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .av-grid-3 {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .av-box {
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            padding: 11px;
            background: #fff;
        }

        .av-box-title {
            margin: 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #64748b;
            font-weight: 700;
        }

        .av-box-value {
            margin: 5px 0 0;
            font-size: 15px;
            color: #0f172a;
            font-weight: 800;
        }

        .av-title {
            margin: 0;
            font-size: 21px;
            color: #0f172a;
            font-weight: 900;
            line-height: 1.25;
        }

        .av-sub {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .av-list {
            margin: 10px 0 0;
            padding-left: 18px;
            font-size: 13px;
            line-height: 1.5;
            color: #334155;
        }

        .av-list li + li {
            margin-top: 4px;
        }

        .av-warning {
            margin-top: 10px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            border-radius: 10px;
            padding: 10px 11px;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 700;
        }

        .av-good {
            margin-top: 10px;
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: #065f46;
            border-radius: 10px;
            padding: 10px 11px;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 700;
        }

        .av-readiness-shell {
            border: 1px solid #bae6fd;
            border-radius: 12px;
            background: linear-gradient(135deg, #ecfeff 0%, #f8fafc 100%);
            padding: 12px;
        }

        .av-readiness-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .av-readiness-score {
            border: 1px solid #93c5fd;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 800;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .av-readiness-grid {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .av-progress-card {
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: #ffffff;
            padding: 11px;
        }

        .av-progress-label {
            margin: 0;
            font-size: 12px;
            color: #0f172a;
            font-weight: 800;
        }

        .av-progress-meta {
            margin: 4px 0 0;
            font-size: 12px;
            color: #475569;
        }

        .av-progress-track {
            margin-top: 8px;
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .av-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0284c7 0%, #0f766e 100%);
        }

        .av-chip-ok,
        .av-chip-warn {
            display: inline-block;
            margin-top: 8px;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
        }

        .av-chip-ok {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .av-chip-warn {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
        }

        .av-missing-list {
            margin: 8px 0 0;
            padding-left: 18px;
            color: #334155;
            font-size: 12px;
            line-height: 1.45;
        }

        .av-cta-row {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .av-btn-main,
        .av-btn-soft {
            display: inline-block;
            text-decoration: none;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .av-btn-main {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
        }

        .av-btn-soft {
            background: #ffffff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .av-table-wrap {
            margin-top: 10px;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            overflow: auto;
        }

        .av-table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
            font-size: 13px;
        }

        .av-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            text-align: left;
            font-weight: 800;
            padding: 10px 12px;
        }

        .av-table tbody td {
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            padding: 10px 12px;
            vertical-align: top;
        }

        .av-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .av-pill {
            display: inline-block;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
        }

        .av-timeline {
            margin-top: 10px;
            display: grid;
            gap: 8px;
        }

        .av-time-item {
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            background: #fff;
            padding: 9px 11px;
            font-size: 13px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .av-dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
            background: #cbd5e1;
            flex-shrink: 0;
        }

        .av-dot.done {
            background: #0d9488;
        }

        .av-dot.current {
            background: #f59e0b;
        }

        .av-small {
            font-size: 12px;
            color: #64748b;
        }

        .av-anchor {
            scroll-margin-top: 92px;
        }

        @media (max-width: 1260px) {
            .av-layout {
                grid-template-columns: 1fr;
            }
            .av-side {
                position: static;
            }
        }

        @media (max-width: 820px) {
            .av-grid-2,
            .av-grid-3,
            .av-readiness-grid {
                grid-template-columns: 1fr;
            }
            .av-card {
                padding: 13px;
            }
            .av-hero-title {
                font-size: 24px;
            }
        }
    </style>

    <div class="av-shell">
        <div class="av-layout">
            <aside class="av-side">
                <h3>Application Snapshot</h3>
                <p>{{ $record->application_number }} - {{ $record->student_full_name ?: '-' }}</p>

                <div class="av-side-grid">
                    <div class="av-kpi">
                        <p class="av-kpi-label">Status</p>
                        <p class="av-kpi-value">{{ $statusUi['label'] }}</p>
                    </div>
                    <div class="av-kpi">
                        <p class="av-kpi-label">Completion</p>
                        <p class="av-kpi-value">{{ $completion }}%</p>
                    </div>
                    <div class="av-kpi">
                        <p class="av-kpi-label">Uploaded Docs</p>
                        <p class="av-kpi-value">{{ $uploadedDocumentsCount }}</p>
                    </div>
                    <div class="av-kpi">
                        <p class="av-kpi-label">Created</p>
                        <p class="av-kpi-value">{{ $record->created_at?->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>

                <div class="av-links">
                    @if ($isDraft || $isAcceptedOrEnrolled)
                        <a href="{{ $editUrl }}" class="av-link">Edit Application</a>
                    @endif
                    @if ($showPostAcceptanceProgress)
                        <a href="#enrollment-readiness" class="av-link">Enrollment Readiness</a>
                    @endif
                    <a href="{{ $listUrl }}" class="av-link">Back to Applications</a>
                    <a href="{{ $paymentsUrl }}" class="av-link">Open My Payments</a>
                    <a href="{{ $schedulesUrl }}" class="av-link">Open My Schedules</a>
                </div>
            </aside>

            <div class="av-main">
                @if ($showPostAcceptanceProgress && $postAcceptanceProgress)
                    <section class="av-card av-anchor" id="enrollment-readiness">
                        <div class="av-readiness-shell">
                            <div class="av-readiness-head">
                                <div>
                                    <h3 class="av-title" style="font-size: 20px;">Enrollment Readiness</h3>
                                    <p class="av-sub">
                                        Once accepted, complete medical data and supporting documents to speed up enrollment processing.
                                    </p>
                                </div>
                                <span class="av-readiness-score">{{ $postAcceptanceProgress['overall_percentage'] }}% Ready</span>
                            </div>

                            <div class="av-readiness-grid">
                                <article class="av-progress-card">
                                    <p class="av-progress-label">Medical Information</p>
                                    <p class="av-progress-meta">
                                        {{ $postAcceptanceProgress['medical']['completed'] }}/{{ $postAcceptanceProgress['medical']['required'] }} data wajib terisi
                                    </p>
                                    <div class="av-progress-track">
                                        <div class="av-progress-fill" style="width: {{ $postAcceptanceProgress['medical']['percentage'] }}%;"></div>
                                    </div>

                                    @if ($postAcceptanceProgress['medical']['is_complete'])
                                        <span class="av-chip-ok">Medical Complete</span>
                                    @else
                                        <span class="av-chip-warn">Medical Incomplete</span>
                                        <ul class="av-missing-list">
                                            @foreach (array_slice($postAcceptanceProgress['medical']['missing'], 0, 3) as $missingField)
                                                <li>{{ $missingField }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </article>

                                <article class="av-progress-card">
                                    <p class="av-progress-label">Documents</p>
                                    <p class="av-progress-meta">
                                        {{ $postAcceptanceProgress['documents']['uploaded'] }}/{{ $postAcceptanceProgress['documents']['required'] }} required documents uploaded
                                    </p>
                                    <div class="av-progress-track">
                                        <div class="av-progress-fill" style="width: {{ $postAcceptanceProgress['documents']['percentage'] }}%;"></div>
                                    </div>

                                    @if ($postAcceptanceProgress['documents']['is_complete'])
                                        <span class="av-chip-ok">Documents Complete</span>
                                    @else
                                        <span class="av-chip-warn">Documents Incomplete</span>
                                        <ul class="av-missing-list">
                                            @foreach (array_slice($postAcceptanceProgress['documents']['missing'], 0, 3) as $missingDocument)
                                                <li>{{ $missingDocument }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </article>
                            </div>

                            <div class="av-cta-row">
                                <a href="{{ $editUrl }}" class="av-btn-main">{{ $postAcceptanceProgress['next_action_label'] }}</a>
                                <a href="#documents" class="av-btn-soft">Lihat Dokumen Saat Ini</a>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="av-card av-anchor" id="overview">
                    <div class="av-hero">
                        <p class="av-hero-mini">My Application Detail</p>
                        <p class="av-hero-title">{{ $record->student_full_name ?: 'Student Name Not Set' }}</p>
                        <p class="av-hero-sub">Application #: <strong>{{ $record->application_number }}</strong></p>
                        <div style="margin-top: 10px;">
                            <span class="av-badge" style="background: {{ $statusUi['bg'] }}; color: {{ $statusUi['text'] }}; border-color: {{ $statusUi['border'] }};">
                                {{ $statusUi['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="av-grid-3">
                        <div class="av-box">
                            <p class="av-box-title">School</p>
                            <p class="av-box-value">{{ $record->school?->name ?? '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Applying Level</p>
                            <p class="av-box-value">{{ $record->level?->name ?? '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Admission Period</p>
                            <p class="av-box-value">{{ $record->admissionPeriod?->name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="av-grid-2">
                        <div class="av-box">
                            <p class="av-box-title">Submitted At</p>
                            <p class="av-box-value">{{ $record->submitted_at?->format('d M Y H:i') ?? 'Not submitted yet' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Status Note</p>
                            <p class="av-box-value" style="font-size: 14px; line-height: 1.4;">{{ $record->status_notes ?: '-' }}</p>
                        </div>
                    </div>

                    @if ($isDraft)
                        @if (count($readinessErrors) > 0)
                            <div class="av-warning">
                                The application is not ready for submission yet. Please complete the following:
                                <ul class="av-list">
                                    @foreach ($readinessErrors as $error)
                                        <li>{{ ltrim($error, '- ') }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="av-good">This application has met all submission validation requirements.</div>
                        @endif
                    @else
                        <div class="av-good">
                            This application is already in the school process.
                            @if ($isAcceptedOrEnrolled)
                                You can still complete Medical Information and Documents from the edit page.
                            @else
                                Core application data is read-only.
                            @endif
                        </div>
                    @endif
                </section>

                <section class="av-card av-anchor" id="student">
                    <h3 class="av-title">Student Information</h3>
                    <p class="av-sub">Summary of student biodata submitted in this application.</p>
                    <div class="av-grid-3">
                        <div class="av-box">
                            <p class="av-box-title">Full Name</p>
                            <p class="av-box-value">{{ $record->student_full_name ?: '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Preferred Name</p>
                            <p class="av-box-value">{{ $record->student_preferred_name ?: '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Gender</p>
                            <p class="av-box-value">{{ $record->gender ? ucfirst($record->gender) : '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Birth Place</p>
                            <p class="av-box-value">{{ $record->birth_place ?: '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Birth Date</p>
                            <p class="av-box-value">{{ $record->birth_date?->format('d M Y') ?: '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Nationality</p>
                            <p class="av-box-value">{{ $record->nationality ?: '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Passport/NIK</p>
                            <p class="av-box-value">{{ $record->passport_number ?: '-' }}</p>
                        </div>
                    </div>
                </section>

                <section class="av-card av-anchor" id="address-parent">
                    <h3 class="av-title">Address & Parent Contacts</h3>
                    <p class="av-sub">Residential address and parent/guardian contacts in this application.</p>

                    <div class="av-grid-2">
                        <div class="av-box">
                            <p class="av-box-title">Current Address</p>
                            <p class="av-box-value" style="font-size: 14px; line-height: 1.45;">{{ $record->full_address ?: '-' }}</p>
                        </div>
                        <div class="av-box">
                            <p class="av-box-title">Previous School</p>
                            <p class="av-box-value" style="font-size: 14px; line-height: 1.45;">
                                {{ $record->previous_school_name ?: '-' }}
                                @if ($record->current_grade_level)
                                    <span class="av-small">- Grade {{ $record->current_grade_level }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="av-grid-2">
                        @forelse($record->parentGuardians as $parent)
                            <div class="av-box">
                                <p class="av-box-title">{{ $parent->type_label }}</p>
                                <p class="av-box-value">{{ $parent->full_name }}</p>
                                <p class="av-small">{{ $parent->email ?: '-' }} - {{ $parent->mobile ?: ($parent->phone ?: '-') }}</p>
                                <p class="av-small">{{ $parent->occupation ?: '-' }} @if($parent->company_name) - {{ $parent->company_name }} @endif</p>
                                <p class="av-small">
                                    @if($parent->is_primary_contact) Primary Contact @endif
                                    @if($parent->is_primary_contact && $parent->is_emergency_contact) - @endif
                                    @if($parent->is_emergency_contact) Emergency Contact @endif
                                </p>
                            </div>
                        @empty
                            <div class="av-box">
                                <p class="av-box-value">No parent/guardian data yet.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="av-card av-anchor" id="medical">
                    <h3 class="av-title">Medical Information</h3>
                    <p class="av-sub">Student health details completed in the form.</p>
                    @if ($record->medicalRecord)
                        @php $med = $record->medicalRecord; @endphp
                        <div class="av-grid-3">
                            <div class="av-box">
                                <p class="av-box-title">Blood Type</p>
                                <p class="av-box-value">{{ $med->blood_type ?: 'Unknown' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Height</p>
                                <p class="av-box-value">{{ $med->height ? $med->height . ' cm' : '-' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Weight</p>
                                <p class="av-box-value">{{ $med->weight ? $med->weight . ' kg' : '-' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Food Allergies</p>
                                <p class="av-box-value">{{ $med->has_food_allergies ? 'Yes' : 'No' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Medical Conditions</p>
                                <p class="av-box-value">{{ $med->has_medical_conditions ? 'Yes' : 'No' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Immunization</p>
                                <p class="av-box-value">{{ $med->immunizations_up_to_date ? 'Up to date' : 'Not up to date' }}</p>
                            </div>
                        </div>
                        <div class="av-grid-2">
                            <div class="av-box">
                                <p class="av-box-title">Emergency Contact</p>
                                <p class="av-box-value">{{ $med->emergency_contact_name ?: '-' }}</p>
                                <p class="av-small">{{ $med->emergency_contact_phone ?: '-' }} @if($med->emergency_contact_relationship) - {{ $med->emergency_contact_relationship }} @endif</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Additional Notes</p>
                                <p class="av-box-value" style="font-size: 14px; line-height: 1.45;">{{ $med->additional_notes ?: '-' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="av-box" style="margin-top: 10px;">
                            <p class="av-box-value">No medical record data yet.</p>
                        </div>
                    @endif
                </section>

                <section class="av-card av-anchor" id="documents">
                    <h3 class="av-title">Uploaded Documents</h3>
                    <p class="av-sub">Uploaded documents and their verification status.</p>
                    <div class="av-table-wrap">
                        <table class="av-table">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($record->documents as $doc)
                                @php
                                    $docUi = $documentStatusMap[$doc->status] ?? ['label' => ucfirst((string)$doc->status), 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'];
                                @endphp
                                <tr>
                                    <td>{{ $this->getDocumentTypeLabel((string) $doc->type) }}</td>
                                    <td>
                                        <strong>{{ $doc->name ?: '-' }}</strong>
                                        @if ($doc->file_size)
                                            <div class="av-small">{{ $doc->formatted_size }}</div>
                                        @endif
                                        @if ($doc->rejection_reason)
                                            <div class="av-small" style="color:#b91c1c;">Reason: {{ $doc->rejection_reason }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="av-pill" style="background: {{ $docUi['bg'] }}; color: {{ $docUi['text'] }}; border-color: {{ $docUi['border'] }};">
                                            {{ $docUi['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($doc->file_path)
                                            <a href="{{ route('secure-files.documents.download', ['document' => $doc->id]) }}" target="_blank" style="color:#0f766e;font-weight:700;text-decoration:underline;">View File</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No documents uploaded yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="av-card av-anchor" id="payments">
                    <h3 class="av-title">Payments</h3>
                    <p class="av-sub">Payment history related to this application.</p>
                    <div class="av-table-wrap">
                        <table class="av-table">
                            <thead>
                            <tr>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Paid Date</th>
                                <th>Proof</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($record->payments as $payment)
                                @php
                                    $payUi = $paymentStatusMap[$payment->status] ?? ['label' => ucfirst((string)$payment->status), 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'];
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $payment->paymentType?->name ?? '-' }}</strong>
                                        <div class="av-small">{{ $payment->transaction_code }}</div>
                                        @if ($payment->rejection_reason)
                                            <div class="av-small" style="color:#b91c1c;">Reason: {{ $payment->rejection_reason }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $payment->formatted_amount }}</td>
                                    <td>
                                        <span class="av-pill" style="background: {{ $payUi['bg'] }}; color: {{ $payUi['text'] }}; border-color: {{ $payUi['border'] }};">
                                            {{ $payUi['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->payment_date?->format('d M Y') ?: '-' }}</td>
                                    <td>
                                        @if ($payment->proof_file)
                                            <a href="{{ route('secure-files.payments.proof', ['payment' => $payment->id]) }}" target="_blank" style="color:#0f766e;font-weight:700;text-decoration:underline;">View Proof</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No payment records yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="av-card av-anchor" id="schedules">
                    <h3 class="av-title">Schedules</h3>
                    <p class="av-sub">Interview/test/observation schedules created for this application.</p>
                    <div class="av-table-wrap">
                        <table class="av-table">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>Date & Time</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Interviewer</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($record->schedules as $schedule)
                                @php
                                    $schUi = $scheduleStatusMap[$schedule->status] ?? ['label' => ucfirst((string)$schedule->status), 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'];
                                @endphp
                                <tr>
                                    <td>{{ $schedule->type_label }}</td>
                                    <td>
                                        <strong>{{ $schedule->scheduled_date?->format('d M Y') ?: '-' }}</strong>
                                        <div class="av-small">{{ $schedule->scheduled_time ? \Illuminate\Support\Carbon::parse($schedule->scheduled_time)->format('H:i') : '-' }} - {{ $schedule->duration_minutes ?: 0 }} mins</div>
                                    </td>
                                    <td>
                                        @if ($schedule->is_online)
                                            Online
                                            @if ($schedule->online_meeting_link)
                                                <div class="av-small"><a href="{{ $schedule->online_meeting_link }}" target="_blank" style="text-decoration:underline;color:#0f766e;">Meeting link</a></div>
                                            @endif
                                        @else
                                            Offline
                                            <div class="av-small">{{ $schedule->location ?: '-' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="av-pill" style="background: {{ $schUi['bg'] }}; color: {{ $schUi['text'] }}; border-color: {{ $schUi['border'] }};">
                                            {{ $schUi['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $schedule->interviewer?->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No schedules yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="av-card av-anchor" id="enrollment">
                    <h3 class="av-title">Enrollment</h3>
                    <p class="av-sub">Enrollment details appear after the application is accepted and processed by the school.</p>
                    @if ($record->enrollment)
                        @php $enrollment = $record->enrollment; @endphp
                        <div class="av-grid-3">
                            <div class="av-box">
                                <p class="av-box-title">Student ID</p>
                                <p class="av-box-value">{{ $enrollment->student_id }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Enrollment Number</p>
                                <p class="av-box-value">{{ $enrollment->enrollment_number }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Enrollment Date</p>
                                <p class="av-box-value">{{ $enrollment->enrollment_date?->format('d M Y') ?: '-' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Class</p>
                                <p class="av-box-value">{{ $enrollment->class_name ?: '-' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Homeroom Teacher</p>
                                <p class="av-box-value">{{ $enrollment->homeroom_teacher ?: '-' }}</p>
                            </div>
                            <div class="av-box">
                                <p class="av-box-title">Status</p>
                                <p class="av-box-value">{{ $enrollment->status_label }}</p>
                            </div>
                        </div>
                    @else
                        <div class="av-box" style="margin-top: 10px;">
                            <p class="av-box-value">Enrollment is not available for this application yet.</p>
                        </div>
                    @endif
                </section>

                <section class="av-card av-anchor" id="journey">
                    <h3 class="av-title">Application Journey</h3>
                    <p class="av-sub">Track your application position throughout the VIS Bintaro admissions journey.</p>
                    <div class="av-timeline">
                        @foreach($timeline as $index => $step)
                            @php
                                $isDone = $currentTimelineIndex >= 0 && $index < $currentTimelineIndex;
                                $isCurrent = $step['status'] === $record->status;
                            @endphp
                            <div class="av-time-item">
                                <span class="av-dot {{ $isDone ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}"></span>
                                <span style="font-weight:{{ $isCurrent ? '800' : '700' }}; color:{{ $isCurrent ? '#0f172a' : '#334155' }};">{{ $step['label'] }}</span>
                                @if($isCurrent)
                                    <span class="av-small">- current</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($isTerminal)
                        <div class="av-good">
                            This application is currently at a final status: <strong>{{ $statusUi['label'] }}</strong>.
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
