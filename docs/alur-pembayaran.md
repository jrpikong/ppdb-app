# Alur Pembayaran PPDB

Flow diagram proses pendaftaran dan pembayaran untuk parent/client.

## Diagram Alur

```mermaid
graph TD
    Start([Mulai Pendaftaran]) --> Submit[Submit Formulir]

    Submit -->|Tidak perlu bayar| CreateInvoice1[📝 Auto-Create<br/>Saving Seat Payment<br/>Rp 2.500.000]

    CreateInvoice1 --> Notify1[🔔 Notifikasi<br/>In-app + Email]
    Notify1 --> Pay1[👤 Parent Upload<br/>Bukti Bayar]

    Pay1 -->|Admin Verifikasi| Verify1[✅ Saving Seat<br/>VERIFIED]
    Pay1 -.->|Ditolakkan| Rejected1[❌ Saving Seat<br/>REJECTED<br/>Upload ulang]
    Rejected1 --> Pay1

    Verify1 --> AdminProcess[⏳ Admin Proses Aplikasi<br/>(under_review → interview)]

    AdminProcess -->|Try Accept?| CheckPay[🔍 Cek Pembayaran]
    CheckPay -.->|Belum Verified| BlockAccept[⛔ Tidak Bisa Accept<br/>Saving Seat belum lunas]
    BlockAccept --> AdminProcess

    CheckPay -->|Sudah Verified| Accept[🎉 Aplikasi DITERIMA<br/>Status: ACCEPTED]
    Accept --> CreateInvoice2[📝 Auto-Create Invoice:<br/>Registration Fee<br/>Rp 5.000.000]
    Accept --> CreateInvoice3[📝 Auto-Create Invoice:<br/>Development Fee<br/>Rp 10.000.000]

    CreateInvoice2 --> Notify2[🔔 Notifikasi<br/>In-app + Email]
    CreateInvoice3 --> Notify2

    Notify2 --> Pay2[👤 Parent Upload<br/>Bukti Bayar x2]

    Pay2 -->|Finance Verifikasi| Verify2[✅ Registration<br/>VERIFIED]
    Verify2 --> Verify3[✅ Development<br/>VERIFIED]
    Verify3 --> Enroll[📚 Buat Enrollment<br/>Status: ENROLLED]

    Enroll --> CreateInvoice4[📝 Auto-Create Invoice:<br/>Uniform Package<br/>Rp 3.500.000]
    Enroll --> CreateInvoice5[📝 Auto-Create Invoice:<br/>Book Package<br/>Rp 4.000.000]
    Enroll --> CreateInvoice6[📝 Auto-Create Invoice:<br/>Technology Fee<br/>Rp 2.000.000<br/>(Opsional)]

    CreateInvoice4 --> Notify3[🔔 Notifikasi<br/>In-app + Email]
    CreateInvoice5 --> Notify3
    CreateInvoice6 -.-> Notify3

    Notify3 --> Pay3[👤 Parent Upload<br/>Bukti Bayar]
    Pay3 --> Finish[🎓 Proses Selesai<br/>Siap Masuk]

    style CreateInvoice1 fill:#fef3c7,stroke:#f59e0b
    style CreateInvoice2 fill:#fef3c7,stroke:#f59e0b
    style CreateInvoice3 fill:#fef3c7,stroke:#f59e0b
    style CreateInvoice4 fill:#fef3c7,stroke:#f59e0b
    style CreateInvoice5 fill:#fef3c7,stroke:#f59e0b
    style CreateInvoice6 fill:#e0f2fe,stroke:#3b82f6,stroke-dasharray: 5 5

    style Notify1 fill:#dbeafe,stroke:#2563eb
    style Notify2 fill:#dbeafe,stroke:#2563eb
    style Notify3 fill:#dbeafe,stroke:#2563eb

    style Verify1 fill:#d1fae5,stroke:#10b981
    style Verify2 fill:#d1fae5,stroke:#10b981
    style Verify3 fill:#d1fae5,stroke:#10b981

    style BlockAccept fill:#fee2e2,stroke:#dc2626
    style Rejected1 fill:#fee2e2,stroke:#dc2626
```

## Ringkasan Invoice Otomatis

| Waktu Dibuat | Invoice | Nominal | Keterangan |
|--------------|---------|----------|-------------|
| **Saat Submit** | Saving Seat Payment | Rp 2.500.000 | Wajib |
| **Saat Accepted** | Registration Fee | Rp 5.000.000 | Wajib |
| **Saat Accepted** | Development Fee | Rp 10.000.000 | Wajib |
| **Saat Enrolled** | Uniform Package | Rp 3.500.000 | Wajib |
| **Saat Enrolled** | Book Package | Rp 4.000.000 | Wajib |
| **Saat Enrolled** | Technology Fee | Rp 2.000.000 | Opsional |

**Total Wajib:** Rp 25.000.000
**Total Opsional:** Rp 2.000.000 (Technology Fee)

---

## Status Pembayaran

| Status | Warna | Keterangan | Aksi |
|--------|---------|-------------|--------|
| 🟡 PENDING | Kuning | Invoice baru dibuat, menunggu upload bukti bayar | Upload bukti bayar |
| 🟠 SUBMITTED | Orange | Bukti bayar sudah di-upload, menunggu verifikasi | Tunggu admin |
| 🟢 VERIFIED | Hijau | Pembayaran diterima dan valid | Lanjut ke tahap berikut |
| 🔴 REJECTED | Merah | Bukti bayar tidak valid | Upload ulang bukti bayar |

---

## Langkah untuk Parent

### Tahap 1: Submit Pendaftaran
1. Isi formulir data siswa dan 2 orang tua/wali
2. Klik tombol **Submit**
3. ✅ Tidak perlu bayar terlebih dahulu
4. Setelah submit, invoice **Saving Seat** otomatis dibuat
5. Cek notifikasi di dashboard

### Tahap 2: Bayar Saving Seat Payment
1. Buka menu **Payments**
2. Klik invoice **Saving Seat Payment**
3. Upload bukti transfer/bayar
4. Klik tombol **Submit**
5. Tunggu verifikasi dari Finance Admin

### Tahap 3: Proses Admin
1. Admin akan memproses aplikasi (review → interview)
2. Admin TIDAK BISA "Accept" jika Saving Seat belum lunas
3. Setelah Saving Seat terverifikasi, admin akan meng-accept aplikasi

### Tahap 4: Bayar Registration & Development
1. Setelah aplikasi diterima (Accepted), invoice otomatis dibuat:
   - Registration Fee (Rp 5.000.000)
   - Development Fee (Rp 10.000.000)
2. Upload bukti bayar untuk kedua invoice
3. Tunggu verifikasi dari Finance Admin

### Tahap 5: Enrollment
1. Setelah semua pembayaran lunas, admin akan membuat Enrollment
2. Invoice otomatis dibuat:
   - Uniform Package (Rp 3.500.000)
   - Book Package (Rp 4.000.000)
   - Technology Fee (Rp 2.000.000) - *Opsional*
3. Upload bukti bayar untuk invoice yang dipilih
4. Proses selesai, siswa resmi terdaftar!

---

## Pertanyaan yang Sering Diajukan

### Q: Kenapa saya belum bisa bayar?
**A:** Invoice akan otomatis dibuat SETELAH Anda submit formulir. Silakan cek notifikasi di dashboard.

### Q: Saya sudah upload bukti bayar tapi status masih SUBMITTED?
**A:** Bukti bayar sedang dicek oleh Finance Admin. Biasanya memakan waktu 1-2 hari kerja.

### Q: Bukti bayar saya di-reject. Apa yang harus dilakukan?
**A:** Silakan upload ulang bukti bayar yang valid dan jelas. Pastikan:
- Nominal yang ditransfer sesuai dengan invoice
- Tanggal dan waktu transfer terlihat jelas
- Nama pengirim sesuai dengan nama orang tua/wali

### Q: Admin tidak bisa accept aplikasi saya?
**A:** Pastikan pembayaran **Saving Seat** sudah berstatus **VERIFIED**. Status Accepted tidak akan terbuka sampai pembayaran ini lunas.

### Q: Berapa lama proses verifikasi?
**A: Biasanya 1-2 hari kerja. Jika lebih dari itu, silakan hubungi tim admission.

---

## Kontak Bantuan

Jika ada pertanyaan atau kendala, hubungi:

- 📧 Email: admissions@vis.sch.id
- 📞 Telepon: (62) 21-xxxx-xxxx
- 📍 Lokasi: Kampus VIS Bintaro / Kelapa Gading / Bali

---
*Last updated: 12 Maret 2026*
