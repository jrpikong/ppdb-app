# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

# 🎓 VIS ADMISSION SYSTEM - PROJECT SPECIFICATION

## 📋 PROJECT OVERVIEW

Sistem Penerimaan Peserta Didik Baru (PPDB) untuk Veritas Intercultural School (VIS) dengan arsitektur multi-tenant yang mendukung 3 kampus: VIS Bintaro, VIS Kelapa Gading, dan VIS Bali.

**Status: PRODUCTION READY ✅ - Full stack complete (backend + frontend/UI)**

---

## 🛠️ TECH STACK

### Core Framework
- **Laravel:** 12.x (PHP 8.4.16)
- **Database:** MySQL 8.0+
- **Node.js:** 20.x LTS

### UI Framework
- **Filament:** v5.x (Admin Panel Builder)
- **Tailwind CSS:** 4.x (via Vite)
- **Alpine.js:** 3.x (included with Filament)
- **Livewire:** 3.x (included with Filament)

### Key Packages
- **spatie/laravel-permission:** ^6.0 (Role & Permission Management with Teams)
- **bezhansalleh/filament-shield:** ^4.1 (Shield Plugin for Filament v5)
- **filament/filament:** ^5.0

---

## 🏗️ ARCHITECTURE

### Multi-Tenancy Setup
- **Tenant Model:** `School`
- **Tenant Key:** `code` (URL-friendly, e.g., VIS-BIN)
- **Team Context:** Spatie Permission with `school_id` as team foreign key
- **Panel Structure:**
    - `/superadmin` - Global management (no tenant)
    - `/school/s/{code}` - Tenant-scoped management per school
    - `/my` - Parent/student portal (public registration + application management)

### Authentication & Authorization
- **Guard:** `web` (default Laravel guard)
- **Multi-role System:**
    - Super Admin (school_id = 0, global access)
    - School Admin (per tenant, full school management)
    - Admission Admin (per tenant, application processing)
    - Finance Admin (per tenant, payment management)
    - Parent/panel_user (portal `/my` access, application management only)

---

## 📊 DATABASE SCHEMA

### Core Tables (25 migrations total)

1. **schools** - Multi-campus management
    - id, code (unique), name, full_name, type, email, phone, website
    - city, country, address, postal_code, timezone
    - logo, banner, description
    - principal_name, principal_email, principal_signature
    - is_active, allow_online_admission, settings (JSON)
    - timestamps, soft_deletes

2. **users** - Multi-role users with tenant scope
    - id, school_id (tenant foreign key)
    - name, email, password, phone, avatar, employee_id, department
    - parent profile fields (address, city, province, etc.)
    - is_active, email_verified_at
    - timestamps, soft_deletes

3. **academic_years** - School year management
4. **levels** - Education levels (Early Years, Primary, Middle Years)
5. **admission_periods** - Admission waves/batches
6. **payment_types** - Fee structure per level
7. **applications** - Student applications (core model)
    - Status flow: draft → submitted → under_review → interviewed → accepted/rejected/waitlisted → enrolled/withdrawn
8. **parent_guardians** - Guardian/parent information (separate table)
9. **documents** - Uploaded files per application
10. **payments** - Application fee tracking
11. **schedules** - Interview/test scheduling
12. **medical_records** - Student health data
13. **enrollments** - Confirmed enrollments
14. **activity_logs** - Audit trail
15. **settings** - System configuration
16. **notifications** - Laravel notifications table
17. **roles & permissions** (Spatie Permission tables with team support)

---

## 👥 USER ROLES & PERMISSIONS

### Permission Categories (92 total)

1. **Dashboard & Analytics** (3) - view_dashboard, view_analytics, view_global_reports
2. **School Management** (5) - Super Admin only
3. **Academic Year** (5)
4. **Level Management** (5)
5. **Admission Period** (5)
6. **Application Management** (10) - includes review/approve/reject/assign/export
7. **Document Verification** (6)
8. **Payment Management** (12) - includes payment types + payment verification
9. **Schedule Management** (6)
10. **Medical Records** (5)
11. **Enrollment** (6)
12. **User Management** (6)
13. **Shield - Role Management** (5)
14. **Settings** (3)
15. **Activity Logs** (3)
16. **Reports** (3)

### Role Assignments

| Role | Permissions | Access |
|------|-------------|--------|
| Super Admin | 83 (all) | Global, no tenant restriction |
| School Admin | 67 | Per tenant, full school management |
| Admission Admin | 26 | Applications, documents, schedules, medical |
| Finance Admin | 19 | Payments, financial reports, view applications |
| Parent/panel_user | 8 | Own applications, documents, payments via `/my` portal |

---

## 🎨 FILAMENT PANELS (ALL COMPLETE)

### 1. SuperAdmin Panel (`/superadmin`)
- `app/Providers/Filament/SuperAdminPanelProvider.php`
- FilamentShield plugin for role & permission management
- **Resources:** Shield RoleResource, AcademicYearResource

### 2. School Tenant Panel (`/school/s/{code}`)
- `app/Providers/Filament/SchoolPanelProvider.php`
- Tenant: `School::class` by field `code`
- FilamentShield with `scopeToTenant(true)`

**Resources (10):**
- `AcademicYearResource` - Academic year management
- `AdmissionPeriodResource` - Admission waves with quotas
- `ApplicationResource` - Full application lifecycle (with ViewApplication page, 4 RelationManagers)
- `EnrollmentResource` - Student enrollment management
- `LevelResource` - Education level management
- `MedicalRecordResource` - Student health records
- `PaymentResource` - Payment verification
- `PaymentTypeResource` - Fee structure per level
- `ScheduleResource` - Interview/test scheduling
- `SettingResource` - School configuration
- `UserResource` - Staff user management

**Widgets (9):**
- `ApplicationsByStatusChart` - Donut chart by status
- `ApplicationsPerMonthChart` - Line/bar chart monthly
- `DashboardOverviewWidget` - Key stats overview
- `EnrollmentProgressWidget` - Enrollment progress per level
- `FeaturesOverviewWidget` - System features summary
- `PendingVerificationsWidget` - Pending doc/payment verifications
- `RecentApplicationsWidget` - Latest applications table
- `StatsOverviewWidget` - Total numbers stats
- `UpcomingSchedulesWidget` - Upcoming interview schedules

### 3. Parent Portal (`/my`)
- `app/Providers/Filament/MyPanelProvider.php`
- Custom registration: `app/Filament/My/Auth/Register.php`

**Resources (4):**
- `Applications/ApplicationResource` - Create/manage own applications
- `Payments/PaymentResource` - View payment status
- `Profiles/ProfileResource` - Edit parent profile
- `Schedules/ScheduleResource` - View interview schedules

**Widgets (3):**
- `MyApplicationStatsWidget` - Own application statistics
- `MyPriorityActionsWidget` - Required actions (pending documents, etc.)
- `MyWelcomeWidget` - Welcome banner

---

## 📦 MODELS (17 total)

| Model | Description |
|-------|-------------|
| `School` | Tenant model, implements Filament tenant interfaces |
| `User` | Multi-role + tenant, implements FilamentUser/HasTenants |
| `AcademicYear` | School year management |
| `Level` | Education levels (EY, PY, MY) |
| `AdmissionPeriod` | Admission waves/batches |
| `PaymentType` | Fee structure per level |
| `Application` | Core model - student applications |
| `ParentGuardian` | Guardian/parent information |
| `Document` | Uploaded files per application |
| `Payment` | Application fee tracking |
| `Schedule` | Interview/test scheduling |
| `MedicalRecord` | Student health data |
| `Enrollment` | Confirmed enrollments |
| `ActivityLog` | Audit trail |
| `Setting` | System configuration |
| `Role` | Extended Spatie Role with team support |
| `Permission` | Spatie Permission model |

---

## 🔄 APPLICATION WORKFLOW

```
draft → submitted → under_review → interviewed →
  ├─> accepted → enrolled
  ├─> rejected
  ├─> waitlisted → (accepted/rejected)
  └─> withdrawn
```

1. Parent registers at `/my/register` → auto-assigned `panel_user` role
2. Creates application in `/my` portal → status: `draft`
3. Uploads required documents (photo, birth cert, family card, report)
4. Submits application → status: `submitted`, application number generated
5. Admission Admin verifies documents → status: `under_review`
6. Admission Admin schedules interview, assigns interviewer
7. Interviewer completes interview + adds feedback → status: `interviewed`
8. School Admin reviews all data → decision: `accepted/rejected/waitlisted`
9. If accepted: Parent uploads payment proof
10. Finance Admin verifies payment
11. School Admin creates enrollment record → status: `enrolled`

---

## 🎯 SEEDER STRUCTURE

### Seeding Order
```php
1. SchoolSeeder              // 3 schools (VIS-BIN, VIS-KG, VIS-BALI)
2. RolePermissionSeeder      // 5 roles, 92 permissions
3. UserSeeder                // 1 super admin + 9 staff + 10 parents
4. AcademicYearSeeder        // 3 academic years per school
5. LevelSeeder               // 3 levels per school (EY, PY, MY)
6. AdmissionPeriodSeeder     // 2 periods per school per year
7. PaymentTypeSeeder         // 6 payment types per level
8. ApplicationSeeder         // 50 sample applications
9. SettingSeeder             // Default system settings
```

### Login Credentials (All passwords: `password`)
```
Super Admin:     superadmin@vis.sch.id

VIS Bintaro:
  Admin:       sarah.johnson@vis-bin.sch.id
  Admission:   michael.chen@vis-bin.sch.id
  Finance:     lisa.wong@vis-bin.sch.id

VIS Kelapa Gading:
  Admin:       david.kumar@vis-kg.sch.id
  Admission:   emma.wilson@vis-kg.sch.id
  Finance:     robert.lee@vis-kg.sch.id

VIS Bali:
  Admin:       amanda.martinez@vis-bali.sch.id
  Admission:   james.taylor@vis-bali.sch.id
  Finance:     michelle.tan@vis-bali.sch.id

Parents (10 total):
  william.thompson@email.com
  jennifer.martinez@email.com
  (+ 8 more)
```

---

## 🗺️ ROUTES

- `GET /` → redirect to `/my/login`
- `GET /privacy` → privacy policy page
- `GET /secure-files/documents/{document}` → secure document download (auth required)
- `GET /secure-files/payments/{payment}/proof` → secure payment proof download (auth required)

---

## 🔒 POLICIES & AUTHORIZATION

Policies in `app/Policies/`:
- `ApplicationPolicy` - Application CRUD + workflow actions
- `DocumentPolicy` - Document upload/verify/reject
- `PaymentPolicy` - Payment create/verify/reject/refund
- `RolePolicy` - Role management (Shield)
- `SchedulePolicy` - Schedule CRUD + complete action

---

## 🔔 NOTIFICATIONS

- `app/Notifications/ParentInAppNotification.php` - In-app notifications for parents
- `app/Support/ParentNotifier.php` - Helper to send notifications to parents

---

## 📝 CODING STANDARDS

### PHP 8.4 + Laravel 12

```php
// ✅ Use casts() method (not $casts property)
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];
}

// ✅ Use Attribute class for accessors
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn() => "{$this->first_name} {$this->last_name}",
    );
}

// ✅ Enums for fixed values
enum ApplicationStatus: string {
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
}

// ✅ Match expressions
$icon = match($status) {
    'draft' => 'heroicon-o-pencil',
    'submitted' => 'heroicon-o-paper-airplane',
    default => 'heroicon-o-question-mark-circle',
};

// ✅ Strict types
declare(strict_types=1);
```

### Filament v5 Patterns

```php
// ✅ Scope queries by tenant
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->where('school_id', Filament::getTenant()->id);
}

// ✅ Use Shield trait for authorization
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

// ✅ Schema-based form/infolist separation
// Forms -> app/Filament/School/Resources/*/Schemas/*Form.php
// Infolists -> app/Filament/School/Resources/*/Schemas/*Infolist.php
// Tables -> app/Filament/School/Resources/*/Tables/*Table.php
```

---

## 🏁 PROJECT STATUS: COMPLETE

### Completed (100%)
- ✅ Database schema (25 migrations)
- ✅ All models with relationships (17 models)
- ✅ Role & permission system with tenancy (92 permissions, 5 roles)
- ✅ Multi-panel setup (SuperAdmin + School + My/Parent)
- ✅ Shield integration with tenant scoping
- ✅ User authentication with multi-role
- ✅ Seeders with production-ready data (9 seeders)
- ✅ All Filament Resources implemented (10 School + 4 My + Shield)
- ✅ Dashboard widgets (9 School + 3 My)
- ✅ Parent registration portal (`/my/register`)
- ✅ Application workflow (full lifecycle)
- ✅ Document management with secure file serving
- ✅ Payment management with proof upload
- ✅ Interview scheduling system
- ✅ Medical records management
- ✅ Enrollment management
- ✅ Activity logging
- ✅ Authorization policies (5 policies)
- ✅ In-app notifications for parents
- ✅ Privacy policy page
- ✅ Secure file download routes

### Known Areas for Future Enhancement
- 🔜 Email notifications (queue-based)
- 🔜 PDF generation (acceptance letters, enrollment cards)
- 🔜 Excel export (applications, payments)
- 🔜 Rate limiting on registration
- 🔜 Two-factor authentication (optional)
- 🔜 CDN for static files (production deployment)

---

## 📁 KEY FILE LOCATIONS

```
app/
├── Filament/
│   ├── SuperAdmin/
│   │   ├── Pages/Dashboard.php
│   │   └── Widgets/AccountWidget.php
│   ├── School/
│   │   ├── Pages/Dashboard.php
│   │   ├── Resources/         (11 resources, each with Pages/, Schemas/, Tables/)
│   │   └── Widgets/           (9 widgets)
│   └── My/
│       ├── Auth/Register.php
│       ├── Pages/Dashboard.php
│       ├── Resources/         (4 resources)
│       └── Widgets/           (3 widgets)
├── Http/
│   ├── Controllers/SecureFileController.php
│   └── Middleware/ApplyTenantScope.php
├── Models/                    (17 models)
├── Notifications/ParentInAppNotification.php
├── Policies/                  (5 policies)
├── Providers/Filament/        (3 panel providers)
└── Support/ParentNotifier.php

database/
├── migrations/                (25 files)
└── seeders/                   (9 files + DatabaseSeeder)

resources/views/
├── filament/                  (panel-specific blade views)
└── legal/privacy.blade.php

public/
└── logo/logo.webp
```
