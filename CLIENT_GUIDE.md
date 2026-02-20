# PPDB App - Client Feature Guide

Dokumen ini menjelaskan fitur yang tersedia pada panel `My` (parent/guardian) dan panel `School` (staff sekolah), serta simulasi login berdasarkan data seeder.

## 1. Ringkasan Panel

- Panel `My`: `/my`
  - Digunakan oleh parent/guardian untuk daftar akun, membuat application, upload dokumen, submit, cek pembayaran, dan cek jadwal.
- Panel `School`: `/school/s/{SCHOOL_CODE}`
  - Digunakan oleh staff sekolah per tenant (contoh: `VIS-BIN`, `VIS-KG`, `VIS-BALI`) untuk review aplikasi, verifikasi dokumen/pembayaran, jadwal interview/test, dan keputusan akhir.

## 2. Fitur Detail - Panel My

### 2.1 Auth dan Akun
- Register parent (`/my/register`):
  - Field: `name`, `email`, `phone`, `password`, `password_confirmation`, `terms`.
  - Email verification aktif.
  - Parent otomatis dibuat dengan role `parent` (team global).
- Login, forgot/reset password, profile account, database notifications tersedia.

### 2.2 Dashboard My
- Widget `Welcome`:
  - Greeting dinamis sesuai waktu.
  - Menampilkan kondisi akun dan shortcut ke create/list application.
- Widget `Priority Actions`:
  - Prioritas aksi otomatis:
    - lanjutkan draft application,
    - upload/ulang bukti pembayaran,
    - konfirmasi jadwal.
  - Menampilkan jumlah unread notifications.
- Widget `Application Stats`:
  - Total aplikasi, draft, in progress, accepted, enrolled.

### 2.3 Resource My Applications
- List:
  - Hanya menampilkan aplikasi milik parent login.
  - Search/filter/sort status aplikasi.
- Create (entry point cepat):
  - Wajib pilih: `school`, `admission_period`, `level`.
  - Sistem membuat draft dan redirect ke halaman edit wizard.
  - Nomor aplikasi dibuat secara aman untuk kondisi concurrent create.
- Edit (wizard bertahap):
  - Step 1: Admission Setup.
  - Step 2: Student Biodata.
  - Step 3: Address and Previous School.
  - Step 4: Parent/Guardian data.
  - Step 5: Medical Information.
  - Step 6: Documents upload.
  - Step 7: Review and Submit.
  - Auto-save saat klik Next.
- Submit Application:
  - Hanya boleh saat status `draft`.
  - Validasi minimal:
    - field inti siswa terisi,
    - minimal 1 parent/guardian,
    - minimal 1 dokumen.
  - Anti duplikasi anak aktif untuk kombinasi:
    - `student_first_name`,
    - `student_last_name`,
    - `birth_date`,
    - `school_id`,
    - `admission_period_id`.
- Setelah submit:
  - status menjadi `submitted`,
  - data jadi read-only untuk parent.

### 2.4 Resource My Payments
- List payment berdasarkan application milik parent.
- View payment:
  - Parent bisa submit payment proof saat status `pending` atau `rejected`.
  - Data bukti: tanggal bayar, metode, bank, nomor referensi, file bukti, catatan.
  - Setelah submit, status masuk alur verifikasi.

### 2.5 Resource My Schedules
- List schedule (interview/test/observation) untuk aplikasi parent.
- View schedule:
  - Action `Confirm Attendance` untuk status yang masih aktif.
  - Action `Request Reschedule` dengan preferred date/time + alasan.

### 2.6 Resource My Profile
- Parent bisa update:
  - nama, email, phone,
  - occupation, address.
- Status verifikasi email ditampilkan.

## 3. Fitur Detail - Panel School

## 3.1 Konsep Tenant
- Semua data di panel school terikat tenant sekolah.
- URL tenant:
  - `VIS-BIN`: `/school/s/VIS-BIN`
  - `VIS-KG`: `/school/s/VIS-KG`
  - `VIS-BALI`: `/school/s/VIS-BALI`
- Shield tenancy aktif (`scopeToTenant`) untuk isolasi data antar sekolah.

### 3.2 Dashboard School
- Widget KPI dan monitoring operasional:
  - stats overview,
  - status aplikasi,
  - trend aplikasi per bulan,
  - pending verification,
  - upcoming schedules,
  - enrollment progress,
  - recent applications.

### 3.3 Master Data Akademik
- Academic Years:
  - manage tahun ajaran aktif per sekolah.
- Admission Periods:
  - periode penerimaan, open/close, decision date, deadline.
- Levels:
  - level/grade, quota, umur minimum-maksimum, status accepting applications.
- Payment Types:
  - jenis biaya per tahap (`pre_submission`, `post_acceptance`, `enrollment`), mandatory/refundable, instruksi bank.

### 3.4 Admissions - Applications (core operasional)
- List applications:
  - filter, status badge, score, assigned reviewer.
- Action per application:
  - assign reviewer,
  - update status (state transition terkontrol),
  - bulk actions (assign/status update).
- Detail application:
  - relation manager:
    - Parent Guardians,
    - Documents,
    - Payments,
    - Schedules.

### 3.5 Documents Verification
- Upload/download dokumen applicant.
- Verify / reject dokumen (dengan catatan verifikasi).
- Bulk verify untuk efisiensi operasional.

### 3.6 Payments Verification
- Monitoring pembayaran per aplikasi.
- Action:
  - mark submitted,
  - verify,
  - reject,
  - refund.
- Parent notification dikirim saat status payment berubah.

### 3.7 Schedules Management
- Buat dan kelola schedule:
  - observation,
  - test,
  - interview.
- Tracking status schedule:
  - scheduled, confirmed, completed, rescheduled, cancelled, no_show.

### 3.8 Medical Records dan Enrollments
- Medical records:
  - data kesehatan kandidat.
- Enrollments:
  - finalisasi kandidat diterima menjadi enrolled.

### 3.9 Users dan Settings
- Users:
  - CRUD staff per tenant sekolah.
- Settings:
  - pengaturan operasional tenant sekolah.

## 4. Simulasi Login Berdasarkan Seeder

Semua akun seeder menggunakan password:

- `password`

### 4.1 Parent (Panel My)
- URL: `/my`
- Contoh akun:
  - `william.thompson@email.com`
  - `jennifer.martinez@email.com`
  - `alexander.brown@email.com`
  - `sophia.anderson@email.com`
  - `benjamin.davis@email.com`
  - `olivia.wilson@email.com`
  - `daniel.garcia@email.com`
  - `emma.rodriguez@email.com`
  - `matthew.lee@email.com`
  - `isabella.kim@email.com`

Simulasi:
1. Login ke `/my`.
2. Klik `Start New Application` (resource create).
3. Pilih school, period, level.
4. Lengkapi wizard sampai submit.
5. Cek `My Payments` dan `My Schedules`.

### 4.2 School - VIS Bintaro (`/school/s/VIS-BIN`)
- `sarah.johnson@vis-bin.sch.id` (super_admin tenant)
- `michael.chen@vis-bin.sch.id` (school_admin)
- `lisa.wong@vis-bin.sch.id` (admission_admin)
- `robert.bintaro@vis-bin.sch.id` (finance_admin)

Simulasi:
1. Admission admin review application dan update status.
2. Finance admin verifikasi payment proof.
3. School admin monitor dashboard dan assignment.

### 4.3 School - VIS Kelapa Gading (`/school/s/VIS-KG`)
- `david.kumar@vis-kg.sch.id` (super_admin tenant)
- `emma.wilson@vis-kg.sch.id` (school_admin)
- `robert.lee@vis-kg.sch.id` (admission_admin)
- `cynthia.park@vis-kg.sch.id` (finance_admin)

### 4.4 School - VIS Bali (`/school/s/VIS-BALI`)
- `amanda.martinez@vis-bali.sch.id` (super_admin tenant)
- `james.taylor@vis-bali.sch.id` (school_admin)
- `michelle.tan@vis-bali.sch.id` (admission_admin)
- `kevin.sanjaya@vis-bali.sch.id` (finance_admin)

### 4.5 Global Super Admin (opsional internal)
- URL: `/superadmin`
- Akun:
  - `superadmin@vis.sch.id`
- Akses:
  - global seluruh tenant/sekolah.

## 5. Checklist UAT Singkat untuk Client

1. Parent register, verify email, login panel `My`.
2. Parent buat application draft, isi wizard, submit.
3. Staff school lihat application masuk, assign reviewer, ubah status.
4. Parent menerima notifikasi, upload payment proof.
5. Finance verify payment.
6. Staff buat schedule interview/test.
7. Parent confirm schedule / request reschedule.
8. Staff set final status `accepted` atau `rejected`.

