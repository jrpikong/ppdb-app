@php
    /** @var \Filament\Schemas\Components\Utilities\Get $get */
    /** @var \App\Models\Application|null $record */

    $dash = static fn ($value): string => filled($value) ? (string) $value : '-';

    $formatDate = static function ($value, string $format = 'd M Y'): string {
        if (blank($value)) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    };

    $typeLabelMap = [
        'father' => 'Father',
        'mother' => 'Mother',
        'guardian' => 'Guardian',
        'other' => 'Other',
    ];

    $genderLabelMap = [
        'male' => 'Male',
        'female' => 'Female',
    ];

    $idTypeLabelMap = [
        'ktp' => 'KTP',
        'passport' => 'Passport',
        'kitas' => 'KITAS',
        'other' => 'Other',
    ];

    $schoolId = (int) ($get('school_id') ?: 0);
    $admissionPeriodId = (int) ($get('admission_period_id') ?: 0);
    $levelId = (int) ($get('level_id') ?: 0);

    $schoolName = $record?->school?->name;
    if (! $schoolName && $schoolId > 0) {
        $schoolName = \App\Models\School::query()->whereKey($schoolId)->value('name');
    }

    $admissionPeriodName = $record?->admissionPeriod?->name;
    if (! $admissionPeriodName && $admissionPeriodId > 0) {
        $admissionPeriodName = \App\Models\AdmissionPeriod::query()->whereKey($admissionPeriodId)->value('name');
    }

    $levelName = $record?->level?->name;
    if (! $levelName && $levelId > 0) {
        $levelName = \App\Models\Level::query()->whereKey($levelId)->value('name');
    }

    $studentFullName = trim(implode(' ', array_filter([
        $get('student_first_name'),
        $get('student_middle_name'),
        $get('student_last_name'),
    ])));

    $currentAddress = implode(', ', array_filter([
        $get('current_address'),
        $get('current_city'),
        $get('current_postal_code'),
        $get('current_country'),
    ]));

    $parentGuardians = collect($get('parentGuardians') ?? []);

    $completion = (int) ($record?->getCompletionPercentage() ?? 0);
@endphp

<style>
    .rv-shell {
        --rv-bg: #eef6ff;
        --rv-card: #ffffff;
        --rv-border: #d9e5f5;
        --rv-text: #1f2d46;
        --rv-muted: #617191;
        --rv-brand: #0f766e;
        background:
            radial-gradient(circle at 0 0, rgba(14, 165, 233, .13), transparent 34%),
            radial-gradient(circle at 100% 0, rgba(34, 197, 94, .14), transparent 30%),
            var(--rv-bg);
        border: 1px solid var(--rv-border);
        border-radius: 18px;
        padding: 16px;
        color: var(--rv-text);
    }

    .rv-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .rv-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .rv-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .rv-card {
        border: 1px solid var(--rv-border);
        border-radius: 12px;
        background: var(--rv-card);
        padding: 13px;
        box-shadow: 0 10px 22px rgba(31, 45, 70, .06);
    }

    .rv-card + .rv-card {
        margin-top: 12px;
    }

    .rv-title {
        margin: 0;
        color: #0f172a;
        font-size: 18px;
        font-weight: 900;
        line-height: 1.2;
    }

    .rv-sub {
        margin: 5px 0 0;
        color: var(--rv-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .rv-kpi {
        border: 1px solid var(--rv-border);
        border-radius: 10px;
        background: #f8fbff;
        padding: 10px;
    }

    .rv-kpi-label {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .rv-kpi-value {
        margin: 5px 0 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 900;
    }

    .rv-field {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
        background: #ffffff;
    }

    .rv-field-label {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 700;
    }

    .rv-field-value {
        margin: 4px 0 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.4;
    }

    .rv-pill {
        display: inline-block;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 4px 8px;
        font-size: 11px;
        line-height: 1;
        font-weight: 800;
        color: #334155;
        background: #f8fafc;
    }

    .rv-parent-card {
        border: 1px solid #dbe4f0;
        border-radius: 11px;
        padding: 11px;
        background: #fff;
    }

    .rv-parent-name {
        margin: 0;
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
    }

    .rv-parent-meta {
        margin: 3px 0 0;
        color: #475569;
        font-size: 12px;
        line-height: 1.45;
    }

    .rv-doc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .rv-doc-card {
        border: 1px solid #dbe4f0;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }

    .rv-doc-head {
        padding: 10px 11px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .rv-doc-title {
        margin: 0;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .rv-doc-meta {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 11px;
    }

    .rv-doc-body {
        padding: 10px;
    }

    .rv-doc-image {
        width: 100%;
        max-height: 250px;
        object-fit: contain;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .rv-doc-file {
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 14px;
        color: #475569;
        font-size: 13px;
        background: #f8fafc;
    }

    .rv-doc-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .rv-btn {
        display: inline-block;
        text-decoration: none;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .rv-btn-primary {
        color: #ffffff;
        background: var(--rv-brand);
        border-color: #0f766e;
    }

    .rv-btn-secondary {
        color: #0f766e;
        background: #f0fdfa;
        border-color: #99f6e4;
    }

    @media (max-width: 1100px) {
        .rv-grid-4,
        .rv-grid-3,
        .rv-grid-2,
        .rv-doc-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="rv-shell">
    <section class="rv-card">
        <h3 class="rv-title">Application Preview</h3>
        <p class="rv-sub">Final review of all data entered by parent before submission.</p>

        <div class="rv-grid-4" style="margin-top: 10px;">
            <div class="rv-kpi">
                <p class="rv-kpi-label">Completion</p>
                <p class="rv-kpi-value">{{ $completion }}%</p>
            </div>
            <div class="rv-kpi">
                <p class="rv-kpi-label">Parent/Guardian</p>
                <p class="rv-kpi-value">{{ $parentGuardians->count() }}</p>
            </div>
        </div>
    </section>

    <section class="rv-card">
        <h3 class="rv-title">Admission Setup</h3>
        <p class="rv-sub">School, period, and level selected for this application.</p>

        <div class="rv-grid-3" style="margin-top: 10px;">
            <div class="rv-field">
                <p class="rv-field-label">School</p>
                <p class="rv-field-value">{{ $dash($schoolName) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Admission Period</p>
                <p class="rv-field-value">{{ $dash($admissionPeriodName) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Level</p>
                <p class="rv-field-value">{{ $dash($levelName) }}</p>
            </div>
        </div>
    </section>

    <section class="rv-card">
        <h3 class="rv-title">Student Biodata</h3>
        <p class="rv-sub">Student identity and contact details.</p>

        <div class="rv-grid-3" style="margin-top: 10px;">
            <div class="rv-field">
                <p class="rv-field-label">Full Name</p>
                <p class="rv-field-value">{{ $dash($studentFullName) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Preferred Name</p>
                <p class="rv-field-value">{{ $dash($get('student_preferred_name')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Gender</p>
                <p class="rv-field-value">{{ $genderLabelMap[$get('gender')] ?? $dash($get('gender')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Birth Place</p>
                <p class="rv-field-value">{{ $dash($get('birth_place')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Birth Date</p>
                <p class="rv-field-value">{{ $formatDate($get('birth_date')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Nationality</p>
                <p class="rv-field-value">{{ $dash($get('nationality')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Passport / NIK</p>
                <p class="rv-field-value">{{ $dash($get('passport_number')) }}</p>
            </div>
        </div>
    </section>

    <section class="rv-card">
        <h3 class="rv-title">Address & Previous School</h3>
        <p class="rv-sub">Residence, school history, and additional profile notes.</p>

        <div class="rv-grid-2" style="margin-top: 10px;">
            <div class="rv-field">
                <p class="rv-field-label">Current Address</p>
                <p class="rv-field-value">{{ $dash($currentAddress) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Previous School</p>
                <p class="rv-field-value">{{ $dash($get('previous_school_name')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Previous School Country</p>
                <p class="rv-field-value">{{ $dash($get('previous_school_country')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Current Grade Level</p>
                <p class="rv-field-value">{{ $dash($get('current_grade_level')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Languages Spoken</p>
                <p class="rv-field-value">{{ $dash($get('languages_spoken')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Interests & Hobbies</p>
                <p class="rv-field-value">{{ $dash($get('interests_hobbies')) }}</p>
            </div>
        </div>
    </section>

    <section class="rv-card">
        <h3 class="rv-title">Parent / Guardian</h3>
        <p class="rv-sub">All parent and guardian contacts entered in the form.</p>

        <div class="rv-grid-2" style="margin-top: 10px;">
            @forelse ($parentGuardians as $parent)
                @php
                    $parent = is_array($parent) ? $parent : [];
                    $parentName = trim(implode(' ', array_filter([
                        $parent['first_name'] ?? null,
                        $parent['middle_name'] ?? null,
                        $parent['last_name'] ?? null,
                    ])));
                    $parentType = $typeLabelMap[$parent['type'] ?? ''] ?? ucfirst((string) ($parent['type'] ?? 'Other'));
                    $parentAddress = implode(', ', array_filter([
                        $parent['address'] ?? null,
                        $parent['city'] ?? null,
                        $parent['postal_code'] ?? null,
                        $parent['country'] ?? null,
                    ]));
                @endphp
                <div class="rv-parent-card">
                    <p class="rv-parent-name">{{ $dash($parentName) }}</p>
                    <p class="rv-parent-meta">{{ $parentType }}</p>
                    <p class="rv-parent-meta">Email: {{ $dash($parent['email'] ?? null) }}</p>
                    <p class="rv-parent-meta">Mobile: {{ $dash($parent['mobile'] ?? null) }}</p>
                    <p class="rv-parent-meta">Phone: {{ $dash($parent['phone'] ?? null) }}</p>
                    <p class="rv-parent-meta">
                        ID: {{ $idTypeLabelMap[$parent['id_type'] ?? ''] ?? $dash($parent['id_type'] ?? null) }}
                        / {{ $dash($parent['id_number'] ?? null) }}
                    </p>
                    <p class="rv-parent-meta">Occupation: {{ $dash($parent['occupation'] ?? null) }}</p>
                    <p class="rv-parent-meta">Company: {{ $dash($parent['company_name'] ?? null) }}</p>
                    <p class="rv-parent-meta">Address: {{ $dash($parentAddress) }}</p>
                    <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                        @if (! empty($parent['is_primary_contact']))
                            <span class="rv-pill">Primary Contact</span>
                        @endif
                        @if (! empty($parent['is_emergency_contact']))
                            <span class="rv-pill">Emergency Contact</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rv-field">
                    <p class="rv-field-value">No parent/guardian data.</p>
                </div>
            @endforelse
        </div>
    </section>

</div>
