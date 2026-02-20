# PPDB App (VIS Admissions)

Dokumentasi client tersedia di:

- `CLIENT_GUIDE.md`

Isi dokumen meliputi:

- Fitur lengkap panel `My` dan `School`.
- Simulasi login untuk tiap level user berdasarkan data seeder.
- Checklist UAT singkat untuk validasi alur end-to-end.

## Referensi Cepat

- Panel parent: `/my`
- Panel school tenant:
  - `/school/s/VIS-BIN`
  - `/school/s/VIS-KG`
  - `/school/s/VIS-BALI`
- Panel global: `/superadmin`

## Seeder (untuk environment lokal/staging)

```bash
php artisan migrate:fresh --seed
```

Setelah seeding, gunakan akun pada `CLIENT_GUIDE.md` (password default: `password`).

