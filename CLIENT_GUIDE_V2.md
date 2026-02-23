# PANDUAN PENGGUNAAN SISTEM PPDB
## Veritas Intercultural School (VIS) Bintaro
### Sistem Penerimaan Peserta Didik Baru — Panduan Lengkap

---

## DAFTAR ISI

1. [Tentang Sistem](#1-tentang-sistem)
2. [Alur Proses Pendaftaran](#2-alur-proses-pendaftaran)
3. [Panduan Orang Tua / Wali (Portal `/my`)](#3-panduan-orang-tua--wali-portal-my)
4. [Panduan Staff Sekolah (Panel Admin)](#4-panduan-staff-sekolah-panel-admin)
5. [Flowchart Lengkap](#5-flowchart-lengkap)
6. [Akun Login untuk Testing](#6-akun-login-untuk-testing)
7. [Checklist UAT (User Acceptance Testing)](#7-checklist-uat-user-acceptance-testing)
8. [FAQ & Troubleshooting](#8-faq--troubleshooting)

---

## 1. TENTANG SISTEM

### Apa itu Sistem PPDB VIS Bintaro?

Sistem Penerimaan Peserta Didik Baru (PPDB) VIS Bintaro adalah platform digital berbasis web yang memudahkan seluruh proses penerimaan siswa baru — dari pendaftaran awal oleh orang tua hingga keputusan penerimaan dan enrollment oleh pihak sekolah.

### Dua Portal Utama

| Portal | URL | Pengguna |
|--------|-----|----------|
| **Portal Orang Tua** | `/my` | Orang tua / wali murid — daftar, isi formulir, upload dokumen, cek status |
| **Panel Admin Sekolah** | `/school/s/VIS-BIN` | Staff sekolah — review aplikasi, verifikasi, penjadwalan, keputusan |

### Jenjang Program yang Dibuka

| Kode | Program | Usia |
|------|---------|------|
| EP | Early Preschool | 1,5 – 2,5 tahun |
| PS | Preschool | 2,5 – 4 tahun |
| PK | Pre-Kindy | 4 – 5 tahun |
| G1 | Grade 1 | 6 – 7 tahun |
| G2 | Grade 2 | 7 – 8 tahun |
| G3 | Grade 3 | 8 – 9 tahun |
| G4 | Grade 4 | 9 – 10 tahun |
| G5 | Grade 5 | 10 – 11 tahun |
| G6 | Grade 6 | 11 – 12 tahun |
| G7 | Grade 7 | 12 – 13 tahun |
| G8 | Grade 8 | 13 – 14 tahun |
| G9 | Grade 9 | 14 – 15 tahun |

---

## 2. ALUR PROSES PENDAFTARAN

### Gambaran Besar (Dari Daftar Hingga Enrolled)

```
 ORANG TUA                              STAFF SEKOLAH
 ──────────                             ──────────────
 [Daftar Akun]
       │
       ▼
 [Buat Aplikasi]
       │
       ▼
 [Isi Wizard 7 Langkah]
   Step 1: Setup Admission
   Step 2: Biodata Siswa
   Step 3: Alamat & Sekolah Asal
   Step 4: Data Orang Tua/Wali
   Step 5: Informasi Kesehatan
   Step 6: Upload Dokumen
   Step 7: Review & Submit
       │
       ▼
 [Bayar Saving Seat]
       │
       ▼
 [Submit Aplikasi] ─────────────────►  [Aplikasi Masuk: "Submitted"]
                                                │
                                                ▼
                                        [Review Dokumen]
                                                │
                                   ┌────────────┴────────────┐
                                   ▼                         ▼
                           [Dokumen OK]              [Dokumen Kurang]
                           "documents_verified"       → Notif ke Ortu
                                   │
                                   ▼
                           [Jadwalkan Interview/Test/Observasi]
                                   │
 [Konfirmasi Jadwal] ◄─────────────┘
       │
       ▼
 [Hadiri Sesi]  ─────────────────────►  [Input Hasil Interview]
                                                │
                                                ▼
                                        [Keputusan Akhir]
                                      ┌────────┬─────────┐
                                      ▼        ▼         ▼
                                  [Accepted] [Rejected] [Waitlisted]
                                      │
 [Bayar Biaya]  ◄─────────────────────┘
       │
       ▼
 [Upload Bukti Bayar] ─────────────►  [Verifikasi Pembayaran]
                                                │
                                                ▼
                                        [Buat Enrollment]
                                                │
 [Notif: Enrolled] ◄───────────────────────────┘
```

### Status Aplikasi

```
draft ──► submitted ──► under_review ──► documents_verified
                                                 │
                                         interview_scheduled
                                                 │
                                         interview_completed
                                                 │
                                    ┌────────────┼────────────┐
                                    ▼            ▼            ▼
                            payment_pending  rejected    waitlisted
                                    │
                            payment_verified
                                    │
                                 accepted
                                    │
                                 enrolled
```

> **Terminal states** (tidak bisa berubah): `rejected`, `enrolled`, `withdrawn`

---

## 3. PANDUAN ORANG TUA / WALI (Portal `/my`)

### 3.1 Registrasi Akun Baru

**URL:** `/my/register`

```
LANGKAH REGISTRASI
──────────────────
1. Buka URL: /my/register
2. Isi form:
   ├── Nama Lengkap
   ├── Email (akan digunakan untuk login)
   ├── Nomor HP
   ├── Password (min. 8 karakter)
   ├── Konfirmasi Password
   └── Centang Syarat & Ketentuan
3. Klik [Register]
4. Cek email → klik link verifikasi
5. Login ke /my
```

> ⚠️ **Penting:** Verifikasi email wajib dilakukan sebelum bisa login.

---

### 3.2 Login

**URL:** `/my/login`

```
1. Masukkan Email
2. Masukkan Password
3. Klik [Login]
4. Akan diarahkan ke Dashboard
```

---

### 3.3 Dashboard Orang Tua

Setelah login, orang tua akan melihat 3 widget:

| Widget | Isi |
|--------|-----|
| **Welcome** | Sapaan dinamis, shortcut buat/lihat aplikasi |
| **Priority Actions** | Aksi yang perlu segera dilakukan (upload dokumen, konfirmasi jadwal, dll.) |
| **Application Stats** | Ringkasan jumlah aplikasi per status |

---

### 3.4 Membuat Aplikasi Baru

**Navigasi:** `My Applications` → `[+ New Application]`

```
FLOWCHART MEMBUAT APLIKASI
───────────────────────────
Klik [+ New Application]
          │
          ▼
   Pilih Program/Jenjang
   (contoh: Grade 2 / Preschool)
          │
          ▼
   Sistem buat Draft otomatis
   + Nomor Aplikasi digenerate
          │
          ▼
   Diarahkan ke Wizard Edit
   (7 langkah)
```

---

### 3.5 Wizard Pengisian Aplikasi (7 Langkah)

> **Auto-save** aktif di setiap langkah. Data tersimpan saat klik `Next`.

#### Step 1 — Admission Setup

```
Field yang diisi:
├── Periode Penerimaan (sudah terisi otomatis)
└── Jenjang / Grade (sudah terisi otomatis)
```

#### Step 2 — Biodata Siswa

```
Field yang diisi:
├── Nama Depan *
├── Nama Tengah
├── Nama Belakang *
├── Nama Panggilan
├── Jenis Kelamin *
├── Tempat Lahir *
├── Tanggal Lahir *
├── Kewarganegaraan *
├── Nomor Paspor (jika ada)
├── Email Siswa
├── Nomor HP
├── Bahasa yang Dikuasai
└── Minat / Hobi
```

#### Step 3 — Alamat & Sekolah Asal

```
Field yang diisi:
├── Alamat Lengkap *
├── Kota *
├── Negara *
├── Kode Pos
├── Nama Sekolah Asal
├── Negara Sekolah Asal
├── Grade Saat Ini
├── Tanggal Mulai Sekolah Asal
└── Tanggal Akhir Sekolah Asal
```

#### Step 4 — Data Orang Tua / Wali

```
Field yang diisi (per orang tua):
├── Tipe: Ayah / Ibu / Wali
├── Nama Depan *
├── Nama Belakang *
├── Email *
├── Nomor HP *
├── Nomor Ponsel
├── Kewarganegaraan
├── Pekerjaan
├── Nama Perusahaan
├── Alamat
└── ✓ Primary Contact / Emergency Contact

Minimal 1 data orang tua harus diisi.
```

#### Step 5 — Informasi Kesehatan

```
Field yang diisi:
├── Golongan Darah
├── Tinggi Badan (cm)
├── Berat Badan (kg)
├── Alergi Makanan (Ya/Tidak)
│   └── Detail alergi jika Ya
├── Kondisi Medis Khusus (Ya/Tidak)
│   └── Detail kondisi jika Ya
├── Konsumsi Obat Harian (Ya/Tidak)
├── Pantangan Makanan (Ya/Tidak)
├── Kebutuhan Khusus (Ya/Tidak)
├── Status Imunisasi
└── Kontak Darurat
```

#### Step 6 — Upload Dokumen

**Dokumen Wajib (9 jenis):**

| No | Jenis Dokumen | Keterangan |
|----|--------------|------------|
| 1 | Foto Siswa 3×4 (1) | JPG/PNG, maks 5 MB |
| 2 | Foto Siswa 3×4 (2) | JPG/PNG, maks 5 MB |
| 3 | Foto Ayah 3×4 | JPG/PNG, maks 5 MB |
| 4 | Foto Ibu 3×4 | JPG/PNG, maks 5 MB |
| 5 | KTP Ayah | PDF/JPG, maks 5 MB |
| 6 | KTP Ibu | PDF/JPG, maks 5 MB |
| 7 | Akta Kelahiran | PDF/JPG, maks 5 MB |
| 8 | Kartu Keluarga | PDF/JPG, maks 5 MB |
| 9 | Rapor Terakhir | PDF, maks 5 MB |

**Dokumen Opsional:** Paspor, Transkrip, Surat Rekomendasi, Rekam Medis, dll.

#### Step 7 — Review & Submit

```
FLOWCHART SUBMIT APLIKASI
─────────────────────────
Tinjau semua data yang telah diisi
          │
          ▼
Cek checklist persyaratan:
├── ✓ Field biodata inti terisi
├── ✓ Minimal 1 data orang tua
├── ✓ 9 dokumen wajib terupload
└── ✓ Saving Seat Payment terverifikasi
          │
          ▼
Klik [Submit Application]
          │
    ┌─────┴─────┐
    ▼           ▼
 [Berhasil]  [Gagal]
 Status:      Sistem tampilkan
 "submitted"  error / item yang kurang
    │
    ▼
Data TERKUNCI (read-only)
Tidak bisa diedit lagi oleh orang tua
```

> ⚠️ Setelah submit, **data biodata tidak bisa diubah**. Pastikan semua data sudah benar.

---

### 3.6 Pembayaran Saving Seat

Sebelum submit, orang tua harus melunasi **Saving Seat Payment** (biaya reservasi tempat).

```
FLOWCHART PEMBAYARAN SAVING SEAT
─────────────────────────────────
Buka menu [My Payments]
          │
          ▼
Klik aplikasi yang ada Saving Seat
          │
          ▼
Lihat detail rekening tujuan:
  Bank: Bank Mandiri
  No. Rek: 137-00-1234567-8
  A/N: PT Veritas Intercultural School Bintaro
          │
          ▼
Transfer sesuai nominal
          │
          ▼
Klik [Upload Payment Proof]
Isi form:
├── Tanggal Bayar
├── Metode Pembayaran
├── Nama Bank Pengirim
├── Nomor Referensi Transaksi
├── Upload Foto/Scan Bukti Transfer
└── Catatan (opsional)
          │
          ▼
Klik [Submit Payment]
          │
          ▼
Status: "Awaiting Verification"
Tunggu konfirmasi dari Finance Admin
(1-2 hari kerja)
          │
          ▼
Jika DIVERIFIKASI → lanjut Submit Aplikasi
Jika DITOLAK → upload bukti baru
```

---

### 3.7 Melihat Jadwal (Interview / Test / Observasi)

**Navigasi:** `My Schedules`

```
Daftar jadwal yang tersedia:
├── Tipe: Interview / Assessment Test / Observation Day
├── Tanggal & Waktu
├── Durasi
├── Lokasi (offline) atau Link (online)
├── Nama Interviewer
└── Status: Scheduled / Confirmed / Completed

AKSI YANG TERSEDIA:
├── [Confirm Attendance] — konfirmasi kehadiran
└── [Request Reschedule] — minta jadwal baru
    ├── Pilih tanggal yang diinginkan
    ├── Pilih waktu yang diinginkan
    └── Isi alasan reschedule
```

---

### 3.8 Notifikasi

Orang tua akan menerima notifikasi dalam aplikasi untuk:

- ✅ Aplikasi diterima / ditolak / diwaitlist
- ✅ Status pembayaran berubah (diverifikasi / ditolak)
- ✅ Jadwal baru dibuat oleh sekolah
- ✅ Dokumen diverifikasi / diminta ulang

---

### 3.9 Profil Akun

**Navigasi:** `My Profile`

```
Data yang bisa diubah:
├── Nama Lengkap
├── Email
├── Nomor HP
├── Pekerjaan
└── Alamat
```

---

## 4. PANDUAN STAFF SEKOLAH (Panel Admin)

**URL:** `/school/s/VIS-BIN`

### 4.1 Login Staff

```
1. Buka URL: /school/s/VIS-BIN
2. Masukkan email staff VIS Bintaro
3. Masukkan password
4. Klik [Login]
5. Akan diarahkan ke Dashboard sekolah
```

> ℹ️ Setiap role memiliki akses yang berbeda (lihat tabel di bawah).

### Ringkasan Akses per Role

| Role | Akses Utama |
|------|------------|
| **super_admin** (Principal) | Semua fitur + manajemen user |
| **school_admin** | Dashboard, aplikasi, jadwal, enrollment, setting |
| **admission_admin** | Aplikasi, dokumen, jadwal, rekam medis |
| **finance_admin** | Pembayaran, laporan keuangan, lihat aplikasi |

---

### 4.2 Dashboard Sekolah

Dashboard menampilkan 7 widget monitoring:

| Widget | Informasi |
|--------|-----------|
| **Stats Overview** | Total aplikasi, diterima, enrolled, ditolak |
| **Applications by Status** | Chart donat per status |
| **Monthly Trend** | Grafik aplikasi per bulan |
| **Pending Verifications** | Dokumen & pembayaran yang menunggu verifikasi |
| **Upcoming Schedules** | Jadwal interview/test dalam waktu dekat |
| **Enrollment Progress** | Progress enrollment per jenjang |
| **Recent Applications** | Tabel aplikasi terbaru |

---

### 4.3 Mengelola Aplikasi (Admission Admin / School Admin)

**Navigasi:** `Applications`

```
FLOWCHART REVIEW APLIKASI
──────────────────────────
Lihat daftar aplikasi (filter: status, jenjang, periode)
          │
          ▼
Klik aplikasi yang ingin direview
          │
          ▼
Halaman Detail Aplikasi:
├── Tab Biodata Siswa
├── Tab Orang Tua / Wali (Relation Manager)
├── Tab Dokumen (Relation Manager)
├── Tab Pembayaran (Relation Manager)
└── Tab Jadwal (Relation Manager)
          │
          ▼
Pilih aksi:
├── [Assign Reviewer] — tentukan siapa yang mereview
├── [Update Status] — ubah status aplikasi
│    Transisi yang tersedia:
│    submitted → under_review
│    under_review → documents_verified / rejected / waitlisted
│    documents_verified → interview_scheduled
│    interview_completed → payment_pending / accepted / rejected / waitlisted
│    payment_verified → accepted
│    accepted → enrolled
└── [Bulk Actions] — update banyak aplikasi sekaligus
```

---

### 4.4 Verifikasi Dokumen (Admission Admin)

**Navigasi:** `Applications` → pilih aplikasi → tab `Documents`

```
FLOWCHART VERIFIKASI DOKUMEN
─────────────────────────────
Lihat daftar dokumen aplikasi
          │
          ▼
Klik dokumen untuk preview/download
          │
          ▼
Periksa dokumen:
    ┌─────┴──────┐
    ▼            ▼
 [VALID]      [TIDAK VALID]
    │               │
    ▼               ▼
 [Verify]       [Reject]
 Status:        Status: "Rejected"
 "Approved"     + Isi alasan penolakan
                + Notif ke Orang Tua
          │
          ▼
Jika semua 9 dokumen wajib "Approved":
→ Update status aplikasi ke "documents_verified"

TIP: Gunakan [Bulk Verify] untuk verifikasi
     banyak dokumen sekaligus (jika semua sudah OK)
```

---

### 4.5 Penjadwalan (Admission Admin / School Admin)

**Navigasi:** `Schedules` atau `Applications` → tab `Schedules`

```
FLOWCHART BUAT JADWAL
──────────────────────
Buka tab Schedules pada aplikasi
          │
          ▼
Klik [+ New Schedule]
          │
          ▼
Isi form jadwal:
├── Tipe: Observation / Assessment Test / Interview
├── Tanggal
├── Waktu Mulai
├── Durasi (menit)
├── Mode: Online / Offline
│   ├── Offline → isi Lokasi
│   └── Online → isi Link Meeting
├── Interviewer / PIC
└── Catatan untuk Orang Tua
          │
          ▼
Klik [Save]
          │
          ▼
Sistem kirim notifikasi ke Orang Tua
Orang tua bisa Confirm / Request Reschedule
          │
          ▼
Setelah sesi selesai:
Klik [Complete Schedule]
├── Input Hasil / Catatan
├── Input Skor (opsional)
└── Rekomendasi
          │
          ▼
Status aplikasi bisa di-update ke
"interview_completed"
```

---

### 4.6 Verifikasi Pembayaran (Finance Admin)

**Navigasi:** `Payments`

```
FLOWCHART VERIFIKASI PEMBAYARAN
────────────────────────────────
Lihat daftar pembayaran
Filter: status "Awaiting Verification"
          │
          ▼
Klik pembayaran yang akan diverifikasi
          │
          ▼
Unduh / lihat bukti pembayaran
          │
          ▼
Periksa kesesuaian:
├── Nominal sesuai?
├── Rekening tujuan benar?
└── Nama pengirim/referensi valid?
          │
     ┌────┴────┐
     ▼         ▼
  [VERIFY]  [REJECT]
     │           │
     ▼           ▼
Status:     Status: "Rejected"
"Verified"  + Isi alasan penolakan
            + Orang tua bisa upload ulang
          │
          ▼
Jika Saving Seat VERIFIED:
→ Orang tua bisa Submit Aplikasi

Jika Post-Acceptance Payment VERIFIED:
→ Ubah status aplikasi ke "payment_verified"
```

**Jenis Pembayaran:**

| Tahap | Jenis | Nominal | Wajib |
|-------|-------|---------|-------|
| Pre-Submission | Saving Seat | Rp 2.500.000 | Ya |
| Post-Acceptance | Registration Fee | Rp 5.000.000 | Ya |
| Post-Acceptance | Development Fee | Rp 10.000.000 | Ya |
| Enrollment | Uniform Package | Rp 3.500.000 | Ya |
| Enrollment | Book Package | Rp 4.000.000 | Ya |
| Enrollment | Technology Fee | Rp 2.000.000 | Tidak |

---

### 4.7 Keputusan Akhir (School Admin / super_admin)

```
FLOWCHART KEPUTUSAN AKHIR
──────────────────────────
Setelah interview_completed:
          │
     ┌────┴──────────┐
     ▼               ▼
 [ACCEPTED]      [REJECTED]    [WAITLISTED]
     │               │              │
     ▼               ▼              ▼
Update status    Update status  Update status
"accepted"       "rejected"     "waitlisted"
     │               │              │
     ▼               ▼              ▼
Notif ke         Notif ke      Notif ke
Orang Tua        Orang Tua     Orang Tua
     │
     ▼
Orang Tua bayar
Post-Acceptance Fee
     │
     ▼
Finance Admin verifikasi
     │
     ▼
Status → "payment_verified"
     │
     ▼
[Buat Enrollment Record]
     │
     ▼
Input data enrollment:
├── Kelas / Homeroom Teacher
├── Tanggal Mulai
└── Catatan
     │
     ▼
Status aplikasi → "enrolled"
Student ID digenerate otomatis
```

---

### 4.8 Master Data (School Admin / super_admin)

#### Academic Years
- Kelola tahun ajaran aktif
- Aktifkan / non-aktifkan tahun ajaran

#### Admission Periods
- Kelola periode penerimaan (buka / tutup)
- Set tanggal mulai, akhir, dan deadline enrollment
- Buka/tutup penerimaan aplikasi

#### Levels (Jenjang)
- Kelola kuota per jenjang
- Set rentang usia
- Aktifkan / non-aktifkan jenjang

#### Payment Types
- Kelola jenis biaya per tahap
- Update nominal dan instruksi pembayaran
- Set rekening bank tujuan

#### Users (Staff)
- Tambah / kelola akun staff sekolah
- Set role: school_admin / admission_admin / finance_admin

#### Settings
- Pengaturan operasional sekolah

---

## 5. FLOWCHART LENGKAP

### 5.1 Flowchart Lengkap dari Perspektif Orang Tua

```
START
  │
  ▼
[Buka /my/register]
  │
  ▼
[Isi Form Registrasi]
  │
  ▼
[Verifikasi Email]
  │
  ▼
[Login ke /my]
  │
  ▼
[Dashboard] ──► [Lihat Notifikasi & Status]
  │
  ▼
[New Application]
  │
  ▼
[Step 1: Pilih Program]
  │
  ▼
[Step 2: Isi Biodata Siswa]
  │
  ▼
[Step 3: Isi Alamat & Sekolah Asal]
  │
  ▼
[Step 4: Data Orang Tua/Wali]
  │
  ▼
[Step 5: Informasi Kesehatan]
  │
  ▼
[Step 6: Upload Dokumen]
  │   (9 dokumen wajib)
  ▼
[Step 7: Review]
  │
  ▼
[My Payments → Upload Saving Seat]
  │
  ▼
[Tunggu Verifikasi Finance]
  │
  ▼
[Submit Aplikasi]
  │
  ▼
[Tunggu Review Dokumen oleh Sekolah]
  │
  ▼
[Terima Notif Jadwal] ──► [Konfirmasi Kehadiran]
  │
  ▼
[Hadiri Interview/Test/Observasi]
  │
  ▼
[Tunggu Keputusan]
  │
  ├── [DITERIMA] ──► [Bayar Post-Acceptance Fee]
  │                          │
  │                          ▼
  │                  [Upload Bukti Bayar]
  │                          │
  │                          ▼
  │                  [Tunggu Verifikasi]
  │                          │
  │                          ▼
  │                  [ENROLLED] ──► 🎉 Selamat!
  │
  ├── [WAITLISTED] ──► Tunggu notifikasi selanjutnya
  │
  └── [DITOLAK] ──► Proses selesai
```

---

### 5.2 Flowchart dari Perspektif Staff Sekolah

```
                    ┌──────────────────────────────────┐
                    │     APLIKASI MASUK: "submitted"   │
                    └──────────────────┬───────────────┘
                                       │
                                       ▼
                             [Admission Admin]
                           Review & Assign Reviewer
                                       │
                                       ▼
                           Status: "under_review"
                                       │
                         ┌─────────────┴─────────────┐
                         ▼                           ▼
                  [Dokumen Lengkap]          [Dokumen Tidak Lengkap]
                         │                           │
                         ▼                           ▼
              Status: "documents_verified"    Notif ke Orang Tua
                         │                  (Minta upload ulang)
                         ▼
              [Jadwalkan Interview/Test/Obs]
                         │
                         ▼
              Status: "interview_scheduled"
                         │
                   [Sesi Berlangsung]
                         │
                         ▼
              Status: "interview_completed"
                         │
               ┌─────────┼─────────┐
               ▼         ▼         ▼
          [ACCEPTED]  [REJECTED] [WAITLISTED]
               │
               ▼
    [Finance: Tunggu Pembayaran]
    Status: "payment_pending"
               │
               ▼
    [Finance: Verifikasi Bukti Bayar]
               │
               ▼
    Status: "payment_verified"
               │
               ▼
    [School Admin: Buat Enrollment]
               │
               ▼
    Status: "enrolled" ──► 🎓 Siswa Resmi Diterima!
```

---

### 5.3 Status Aplikasi & Siapa yang Mengubahnya

```
Status               | Diubah Oleh           | Kondisi
─────────────────────┼───────────────────────┼────────────────────────
draft                │ Sistem (otomatis)      │ Saat aplikasi dibuat
submitted            │ Orang Tua             │ Klik Submit
under_review         │ Admission Admin       │ Mulai review
documents_verified   │ Admission Admin       │ Semua dok OK
interview_scheduled  │ Admission Admin       │ Jadwal dibuat
interview_completed  │ Admission Admin       │ Sesi selesai
payment_pending      │ Admission Admin       │ Setelah interview completed
payment_verified     │ Finance Admin         │ Bukti bayar verified
accepted             │ School Admin          │ Keputusan akhir
rejected             │ School Admin          │ Keputusan akhir
waitlisted           │ School Admin          │ Keputusan akhir
enrolled             │ School Admin          │ Enrollment dibuat
withdrawn            │ Siapapun              │ Orang tua atau staff
```

---

## 6. AKUN LOGIN UNTUK TESTING

> Semua akun menggunakan password: **`password`**

### 6.1 Staff Sekolah — Panel Admin

**URL Panel:** `/school/s/VIS-BIN`

| Role | Email | Jabatan | Akses |
|------|-------|---------|-------|
| **super_admin** | `sarah.johnson@vis-bin.sch.id` | School Principal | Full access semua fitur |
| **school_admin** | `michael.chen@vis-bin.sch.id` | Academic Director | Aplikasi, jadwal, enrollment |
| **admission_admin** | `lisa.wong@vis-bin.sch.id` | Head of Admissions | Aplikasi, dokumen, jadwal |
| **finance_admin** | `robert.bintaro@vis-bin.sch.id` | Finance Manager | Pembayaran, laporan |

### 6.2 Orang Tua — Portal My

**URL Portal:** `/my`

| Email | Nama | Area Tinggal |
|-------|------|-------------|
| `william.thompson@email.com` | William Thompson | Bintaro Jaya |
| `jennifer.martinez@email.com` | Jennifer Martinez | Bintaro Jaya |
| `alexander.brown@email.com` | Alexander Brown | BSD City |
| `sophia.anderson@email.com` | Sophia Anderson | Bintaro Jaya |
| `benjamin.davis@email.com` | Benjamin Davis | Pondok Indah |
| `olivia.wilson@email.com` | Olivia Wilson | Kebayoran Lama |
| `daniel.garcia@email.com` | Daniel Garcia | Cilandak |
| `emma.rodriguez@email.com` | Emma Rodriguez | TB Simatupang |
| `matthew.lee@email.com` | Matthew Lee | Cilandak |
| `isabella.kim@email.com` | Isabella Kim | Lebak Bulus |
| `jonathan.park@email.com` | Jonathan Park | Alam Sutera |
| `priya.sharma@email.com` | Priya Sharma | Gading Serpong |
| `david.nguyen@email.com` | David Nguyen | Alam Sutera |
| `sarah.chen@email.com` | Sarah Chen | Serpong |
| `ryan.johnson@email.com` | Ryan Johnson | Serpong |
| `meilin.zhang@email.com` | Mei Lin Zhang | Kemang |
| `patrick.obrien@email.com` | Patrick O'Brien | Kebayoran Baru |
| `anita.krishnan@email.com` | Anita Krishnan | Kebayoran Baru |
| `thomas.mueller@email.com` | Thomas Mueller | Kemang |
| `yuki.tanaka@email.com` | Yuki Tanaka | Menteng |
| `robert.santos@email.com` | Robert Santos | Cinere |
| `christine.lim@email.com` | Christine Lim | Pamulang |
| `marcus.williams@email.com` | Marcus Williams | Ciputat |
| `hana.jeon@email.com` | Hana Jeon | Pamulang |
| `ahmad.fauzi@email.com` | Ahmad Fauzi | Ciputat |

### 6.3 Data Sample Aplikasi (15 Aplikasi)

Setiap akun parent sudah memiliki aplikasi dengan berbagai status untuk keperluan testing:

| Status | Jumlah | Untuk Test Apa |
|--------|--------|----------------|
| draft | 1 | Lihat aplikasi belum lengkap |
| submitted | 2 | Aplikasi menunggu review |
| under_review | 2 | Proses review dokumen |
| documents_verified | 1 | Dokumen sudah OK |
| interview_scheduled | 1 | Jadwal interview |
| interview_completed | 1 | Setelah interview |
| payment_pending | 1 | Menunggu pembayaran |
| payment_verified | 1 | Pembayaran verified |
| accepted | 1 | Diterima |
| enrolled | 1 | Sudah enrolled ✓ |
| rejected | 1 | Ditolak |
| waitlisted | 1 | Waitlist |
| withdrawn | 1 | Mundur |

---

## 7. CHECKLIST UAT (User Acceptance Testing)

### 7.1 Alur Orang Tua (Happy Path)

```
□ 1. Registrasi akun baru di /my/register
□ 2. Verifikasi email (cek inbox)
□ 3. Login ke /my
□ 4. Lihat Dashboard (3 widget tampil)
□ 5. Klik "New Application" → pilih Grade
□ 6. Isi semua 7 step wizard
□ 7. Upload 9 dokumen wajib (Step 6)
□ 8. Buka My Payments → upload bukti Saving Seat
□ 9. Tunggu verifikasi (simulasi: login finance admin → verify)
□ 10. Submit Aplikasi (status → "submitted")
□ 11. Cek My Applications → status berubah
□ 12. Cek My Schedules setelah jadwal dibuat staff
□ 13. Konfirmasi kehadiran di My Schedules
□ 14. Cek notifikasi masuk setelah keputusan
□ 15. Upload Post-Acceptance Payment jika diterima
```

### 7.2 Alur Staff Sekolah

```
□ 1. Login sebagai Admission Admin (lisa.wong@vis-bin.sch.id)
□ 2. Lihat Dashboard → semua widget tampil
□ 3. Buka Applications → lihat list dengan berbagai status
□ 4. Buka aplikasi "submitted" → assign reviewer
□ 5. Ubah status ke "under_review"
□ 6. Buka tab Dokumen → verify satu per satu
□ 7. Gunakan Bulk Verify untuk efisiensi
□ 8. Ubah status ke "documents_verified"
□ 9. Buat jadwal interview (tab Schedules)
□ 10. Ubah status ke "interview_scheduled"

□ 11. Login sebagai Finance Admin (robert.bintaro@vis-bin.sch.id)
□ 12. Buka Payments → lihat pending verifications
□ 13. Download bukti bayar → verify payment
□ 14. Cek notifikasi masuk ke orang tua

□ 15. Login sebagai School Admin (michael.chen@vis-bin.sch.id)
□ 16. Setelah interview_completed → ubah ke "accepted"
□ 17. Buat Enrollment record
□ 18. Status aplikasi → "enrolled"
```

### 7.3 Manajemen Master Data

```
□ 1. Login sebagai super_admin (sarah.johnson@vis-bin.sch.id)
□ 2. Kelola Academic Years (buat, aktifkan)
□ 3. Kelola Admission Periods (buka/tutup pendaftaran)
□ 4. Kelola Levels (quota, usia minimum-maksimum)
□ 5. Kelola Payment Types (nominal, instruksi bank)
□ 6. Kelola Users staff (tambah, edit role)
□ 7. Cek Settings sekolah
```

---

## 8. FAQ & TROUBLESHOOTING

### Q: Orang tua tidak bisa submit aplikasi — ada error?
**A:** Pastikan semua syarat terpenuhi:
1. Semua field wajib di Step 2–4 terisi
2. Minimal 1 data orang tua ditambahkan
3. 9 dokumen wajib sudah terupload
4. Saving Seat Payment sudah diverifikasi oleh Finance Admin

### Q: Tombol Submit tidak aktif?
**A:** Saving Seat Payment harus berstatus "Verified" terlebih dahulu.

### Q: Bagaimana jika dokumen ditolak?
**A:** Orang tua akan menerima notifikasi. Dokumen yang ditolak bisa diunggah ulang melalui portal `/my`.

### Q: Apakah orang tua bisa edit data setelah submit?
**A:** Tidak. Setelah aplikasi berstatus "submitted", data biodata siswa tidak bisa diubah. Hubungi Admission Admin untuk koreksi data jika diperlukan.

### Q: Bagaimana cara membatalkan aplikasi?
**A:** Aplikasi bisa di-withdraw oleh orang tua (status: "draft") atau oleh staff sekolah kapanpun sebelum "enrolled".

### Q: Apakah bisa mendaftar lebih dari satu anak?
**A:** Ya. Orang tua bisa membuat beberapa aplikasi (maksimum 3 aplikasi aktif secara bersamaan).

### Q: Interview bisa dilakukan secara online?
**A:** Ya. Staff sekolah bisa memilih mode Online (Zoom/Google Meet) atau Offline saat membuat jadwal.

### Q: Berapa lama proses verifikasi pembayaran?
**A:** Biasanya 1–2 hari kerja setelah bukti transfer diterima.

---

## INFORMASI KONTAK VIS BINTARO

| Kebutuhan | Kontak |
|-----------|--------|
| **Informasi Umum** | info@vis-bintaro.sch.id |
| **Admissions** | admissions@vis-bintaro.sch.id |
| **Finance** | finance@vis-bintaro.sch.id |
| **Telepon** | +62-21-7450-5678 |
| **Alamat** | Jl. Bintaro Utama Sektor 9 No. 8, Bintaro Jaya, Tangerang Selatan 15224 |
| **Jam Kantor** | Senin–Jumat: 07.00–16.30 WIB, Sabtu: 08.00–12.00 WIB |

---

*Dokumen ini dibuat untuk keperluan panduan penggunaan sistem PPDB VIS Bintaro.*
*Versi 2.0 — Februari 2026*
