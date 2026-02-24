@php
    /** @var \Filament\Schemas\Components\Utilities\Get $get */
    /** @var \App\Models\Application|null $record */

    $dash = static fn ($value): string => filled($value) ? (string) $value : '-';
    $yesNo = static fn ($value): string => $value ? 'Yes' : 'No';

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

    $formatBytes = static function (int $bytes): string {
        if ($bytes <= 0) {
            return '-';
        }

        if ($bytes >= 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024 * 1024), 2).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
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

    $documentStatusUi = [
        'pending' => ['label' => 'Pending', 'bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa'],
        'approved' => ['label' => 'Approved', 'bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0'],
        'rejected' => ['label' => 'Rejected', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
        'resubmit' => ['label' => 'Need Resubmit', 'bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
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
    $medical = (array) ($get('medicalRecord') ?? []);

    $recordDocumentsById = collect($record?->documents ?? [])->keyBy(fn ($doc) => (int) $doc->id);
    $documentTypeLabels = \App\Models\Document::documentTypeOptions();

    $documentsState = collect($get('documents') ?? []);
    if ($documentsState->isEmpty() && $record) {
        $documentsState = collect($record->documents)->map(function ($doc): array {
            return [
                'id' => $doc->id,
                'type' => $doc->type,
                'name' => $doc->name,
                'file_path' => $doc->file_path,
                'file_type' => $doc->file_type,
                'file_size' => $doc->file_size,
                'description' => $doc->description,
                'status' => $doc->status,
            ];
        });
    }

    $documents = $documentsState
        ->map(function ($item) use ($recordDocumentsById, $documentTypeLabels, $documentStatusUi, $formatBytes) {
            $item = is_array($item) ? $item : [];

            $id = isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : null;
            $recordDoc = $id ? $recordDocumentsById->get($id) : null;

            if (! $recordDoc && filled($item['file_path'])) {
                $recordDoc = $recordDocumentsById->first(
                    fn ($doc) => $doc->file_path === $item['file_path']
                );
                $id = $recordDoc?->id;
            }

            $type = $item['type'] ?? $recordDoc?->type;
            $status = $item['status'] ?? $recordDoc?->status ?? 'pending';
            $fileType = $item['file_type'] ?? $recordDoc?->file_type;
            $filePath = $item['file_path'] ?? $recordDoc?->file_path;
            $size = (int) ($item['file_size'] ?? $recordDoc?->file_size ?? 0);
            $name = $item['name'] ?? $recordDoc?->name ?? (filled($filePath) ? basename((string) $filePath) : 'Document');

            $isImage = is_string($fileType) && \Illuminate\Support\Str::startsWith($fileType, 'image/');
            if (! $isImage && filled($filePath)) {
                $extension = strtolower(pathinfo((string) $filePath, PATHINFO_EXTENSION));
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
            }

            $statusUi = $documentStatusUi[$status] ?? ['label' => ucfirst((string) $status), 'bg' => '#f8fafc', 'text' => '#334155', 'border' => '#cbd5e1'];

            return [
                'id' => $id,
                'type_label' => $documentTypeLabels[$type ?? ''] ?? ($type ?: 'Document'),
                'name' => $name,
                'description' => $item['description'] ?? $recordDoc?->description,
                'status_label' => $statusUi['label'],
                'status_bg' => $statusUi['bg'],
                'status_text' => $statusUi['text'],
                'status_border' => $statusUi['border'],
                'is_image' => $isImage,
                'size_label' => $formatBytes($size),
                'link' => $id ? route('secure-files.documents.download', ['document' => $id]) : null,
            ];
        })
        ->values();

    $requiredDocumentTypes = \App\Models\Application::getRequiredDocumentTypesForSchool(
        $schoolId > 0
            ? $schoolId
            : (($record?->school_id ?? 0) > 0 ? (int) $record->school_id : null)
    );
    $uploadedTypes = $documentsState
        ->filter(static function ($item): bool {
            if (! is_array($item)) {
                return false;
            }

            return filled($item['id'] ?? null) || filled($item['file_path'] ?? null);
        })
        ->pluck('type')
        ->filter(static fn ($type) => filled($type))
        ->all();
    $requiredUploadedCount = count(array_unique(array_intersect($requiredDocumentTypes, $uploadedTypes)));
    $uploadedDocumentsCount = $documents
        ->filter(static fn (array $document): bool => filled($document['link'] ?? null))
        ->count();

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
            <div class="rv-kpi">
                <p class="rv-kpi-label">Documents Uploaded</p>
                <p class="rv-kpi-value">{{ $uploadedDocumentsCount }}</p>
            </div>
            <div class="rv-kpi">
                <p class="rv-kpi-label">Required Docs</p>
                <p class="rv-kpi-value">{{ $requiredUploadedCount }}/{{ count($requiredDocumentTypes) }}</p>
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
            <div class="rv-field">
                <p class="rv-field-label">Student Email</p>
                <p class="rv-field-value">{{ $dash($get('email')) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Student Phone</p>
                <p class="rv-field-value">{{ $dash($get('phone')) }}</p>
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
                <p class="rv-field-label">Previous School Start</p>
                <p class="rv-field-value">{{ $formatDate($get('previous_school_start_date'), 'M Y') }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Previous School End</p>
                <p class="rv-field-value">{{ $formatDate($get('previous_school_end_date'), 'M Y') }}</p>
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

    <section class="rv-card">
        <h3 class="rv-title">Medical Information</h3>
        <p class="rv-sub">Health profile and emergency contact details.</p>

        <div class="rv-grid-3" style="margin-top: 10px;">
            <div class="rv-field">
                <p class="rv-field-label">Blood Type</p>
                <p class="rv-field-value">{{ $dash($medical['blood_type'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Height</p>
                <p class="rv-field-value">{{ filled($medical['height'] ?? null) ? $medical['height'].' cm' : '-' }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Weight</p>
                <p class="rv-field-value">{{ filled($medical['weight'] ?? null) ? $medical['weight'].' kg' : '-' }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Food Allergies</p>
                <p class="rv-field-value">{{ $yesNo($medical['has_food_allergies'] ?? false) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Medical Conditions</p>
                <p class="rv-field-value">{{ $yesNo($medical['has_medical_conditions'] ?? false) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Daily Medication</p>
                <p class="rv-field-value">{{ $yesNo($medical['requires_daily_medication'] ?? false) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Dietary Restrictions</p>
                <p class="rv-field-value">{{ $yesNo($medical['has_dietary_restrictions'] ?? false) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Special Needs</p>
                <p class="rv-field-value">{{ $yesNo($medical['has_special_needs'] ?? false) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Immunization Up To Date</p>
                <p class="rv-field-value">{{ $yesNo($medical['immunizations_up_to_date'] ?? false) }}</p>
            </div>
        </div>

        <div class="rv-grid-2" style="margin-top: 10px;">
            <div class="rv-field">
                <p class="rv-field-label">Food Allergy Details</p>
                <p class="rv-field-value">{{ $dash($medical['food_allergies_details'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Medical Condition Details</p>
                <p class="rv-field-value">{{ $dash($medical['medical_conditions'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Daily Medication Details</p>
                <p class="rv-field-value">{{ $dash($medical['daily_medications'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Dietary Details</p>
                <p class="rv-field-value">{{ $dash($medical['dietary_restrictions'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Special Needs Details</p>
                <p class="rv-field-value">{{ $dash($medical['special_needs_description'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Additional Medical Notes</p>
                <p class="rv-field-value">{{ $dash($medical['additional_notes'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Emergency Contact</p>
                <p class="rv-field-value">{{ $dash($medical['emergency_contact_name'] ?? null) }}</p>
                <p class="rv-sub" style="margin-top: 3px;">{{ $dash($medical['emergency_contact_phone'] ?? null) }} / {{ $dash($medical['emergency_contact_relationship'] ?? null) }}</p>
            </div>
            <div class="rv-field">
                <p class="rv-field-label">Doctor / Hospital</p>
                <p class="rv-field-value">{{ $dash($medical['doctor_name'] ?? null) }}</p>
                <p class="rv-sub" style="margin-top: 3px;">{{ $dash($medical['doctor_phone'] ?? null) }} / {{ $dash($medical['hospital_preference'] ?? null) }}</p>
            </div>
        </div>
    </section>

    <section class="rv-card">
        <h3 class="rv-title">Uploaded Documents</h3>
        <p class="rv-sub">Image documents show inline preview. PDF and other files provide open/download actions.</p>

        <div class="rv-doc-grid">
            @forelse ($documents as $document)
                <article class="rv-doc-card">
                    <div class="rv-doc-head">
                        <p class="rv-doc-title">{{ $document['type_label'] }}</p>
                        <p class="rv-doc-meta">
                            {{ $document['name'] }} | {{ $document['size_label'] }}
                            <span class="rv-pill" style="margin-left: 6px; background: {{ $document['status_bg'] }}; color: {{ $document['status_text'] }}; border-color: {{ $document['status_border'] }};">
                                {{ $document['status_label'] }}
                            </span>
                        </p>
                    </div>

                    <div class="rv-doc-body">
                        @if ($document['is_image'] && $document['link'])
                            <img src="{{ $document['link'] }}" alt="{{ $document['name'] }}" class="rv-doc-image">
                        @else
                            <div class="rv-doc-file">
                                {{ $document['description'] ?: 'Document preview is not available for this file type.' }}
                            </div>
                        @endif

                        <div class="rv-doc-actions">
                            @if ($document['link'])
                                <a href="{{ $document['link'] }}" target="_blank" rel="noopener noreferrer" class="rv-btn rv-btn-primary">Open File</a>
                                <a href="{{ $document['link'] }}" download class="rv-btn rv-btn-secondary">Download</a>
                            @else
                                <span class="rv-sub">File link available after save.</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rv-field">
                    <p class="rv-field-value">No documents uploaded.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
