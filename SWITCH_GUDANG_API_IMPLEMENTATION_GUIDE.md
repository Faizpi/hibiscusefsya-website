# Switch Gudang API Implementation Guide

Dokumen ini merangkum dampak fitur **switch gudang** untuk role **admin** dan **spectator**, perbaikan API yang sudah diterapkan, dan langkah penerapan perubahan di GitHub.

## 1) Area yang Terdampak oleh Switch Gudang

Saat user admin/spectator mengganti gudang aktif (`current_gudang_id`), endpoint berikut harus konsisten:

- Endpoint list harus mengikuti gudang aktif atau gudang yang diizinkan.
- Endpoint detail harus menolak akses data dari gudang yang tidak diizinkan.
- Endpoint create harus menolak `gudang_id` di luar akses user.
- Endpoint export harus menolak gudang di luar akses user.

## 2) Perbaikan API yang Sudah Diterapkan

### A. Gudang & Stok

- `GET /api/v1/stok`
  - Sudah diperbaiki sebelumnya agar:
    - role spectator memakai relasi gudang yang benar.
    - mendukung `gudang_id` dengan validasi akses.
    - fallback ke `current_gudang_id` untuk non-super-admin.
    - normalisasi total `stok = stok_penjualan + stok_gratis + stok_sample`.

- `GET /api/v1/gudang/stok/export`
  - Diperbaiki validasi akses gudang:
    - sebelumnya hanya cek role admin.
    - sekarang semua non-super-admin (termasuk spectator) wajib lolos `canAccessGudang`.

### B. Dashboard

- `GET /api/v1/dashboard`
  - Untuk admin/spectator, query biaya sekarang ikut `gudang_id` aktif.
  - `pending_approval` sekarang konsisten mengikuti gudang aktif untuk admin/spectator.

### C. Detail Endpoint Authorization (anti bocor lintas gudang)

Ditambahkan guard akses gudang untuk admin/spectator pada endpoint detail berikut:

- `GET /api/v1/pembayaran/{id}`
- `GET /api/v1/penerimaan-barang/{id}`
- `GET /api/v1/pembelian/{id}`
- `GET /api/v1/kunjungan/{id}`
- `GET /api/v1/biaya/{id}` (pakai akses gudang + relasi user/approver)
- `GET /api/v1/kontak/{id}` (dibatasi current gudang / global `gudang_id = null`)

### D. Create Endpoint Authorization (anti create lintas gudang)

Ditambahkan guard akses gudang untuk non-super-admin:

- `POST /api/v1/penjualan`
- `POST /api/v1/pembelian`
- `POST /api/v1/pembayaran` (cek akses terhadap gudang milik penjualan)

### E. Penerimaan Barang API Scope

Ditambahkan guard akses gudang:

- `GET /api/v1/penerimaan-barang/pembelian-by-gudang/{gudangId}`
- `GET /api/v1/penerimaan-barang/pembelian-detail/{id}`

## 3) File yang Diubah

- `app/Http/Controllers/Api/StokController.php`
- `app/Http/Controllers/Api/GudangController.php`
- `app/Http/Controllers/Api/DashboardController.php`
- `app/Http/Controllers/Api/PembayaranController.php`
- `app/Http/Controllers/Api/PenerimaanBarangController.php`
- `app/Http/Controllers/Api/PembelianController.php`
- `app/Http/Controllers/Api/PenjualanController.php`
- `app/Http/Controllers/Api/KunjunganController.php`
- `app/Http/Controllers/Api/BiayaController.php`
- `app/Http/Controllers/Api/KontakController.php`

## 4) Checklist Integrasi Flutter

- Setelah switch gudang, selalu panggil:
  - `POST /api/v1/gudang/switch`
- Setelah sukses switch, refresh modul yang sensitif gudang:
  - dashboard
  - stok
  - produk
  - penjualan/pembelian/kunjungan/pembayaran/penerimaan-barang
- Untuk request stok, rekomendasi tetap kirim `gudang_id` eksplisit saat dibutuhkan:
  - `GET /api/v1/stok?gudang_id={currentGudangId}`

## 5) Cara Penerapan di GitHub

### Opsi A (langsung ke `main`)

```bash
git checkout main
git pull origin main
git add app/Http/Controllers/Api/*.php SWITCH_GUDANG_API_IMPLEMENTATION_GUIDE.md
git commit -m "Harden warehouse switch API scope for admin/spectator"
git push origin main
```

### Opsi B (via branch + Pull Request)

```bash
git checkout -b fix/switch-gudang-api-scope
git add app/Http/Controllers/Api/*.php SWITCH_GUDANG_API_IMPLEMENTATION_GUIDE.md
git commit -m "Harden warehouse switch API scope for admin/spectator"
git push origin fix/switch-gudang-api-scope
```

Lalu buat Pull Request ke `main` dengan ringkasan:

- Perbaikan scope gudang untuk admin/spectator.
- Penambahan guard akses di endpoint detail/create.
- Konsistensi dashboard terhadap gudang aktif.

## 6) Deploy Setelah Merge

```bash
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && git pull origin main && php artisan cache:clear"
```

Jika ada perubahan konfigurasi/route yang besar, jalankan juga:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 7) Catatan Keamanan

Jika endpoint detail/create tidak memiliki guard `canAccessGudang`, user admin/spectator bisa mengakses data lintas gudang hanya dengan menebak ID resource. Perbaikan ini menutup celah tersebut dan menyamakan perilaku API dengan konsep switch gudang.
