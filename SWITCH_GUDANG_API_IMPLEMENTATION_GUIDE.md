# Switch Gudang API Implementation Guide

Dokumen ini merangkum dampak fitur **switch gudang** untuk role **admin** dan **spectator** serta perbaikan API yang sudah diterapkan.

## Policy Inti (Strict Mode)

-   Untuk role **admin** dan **spectator**, akses bersifat **single-gudang aktif**.
-   Artinya endpoint list/detail/action hanya boleh memproses data dengan `gudang_id == current_gudang_id`.
-   Assignment multi-gudang tetap dipakai untuk memilih gudang saat switch, tapi sesudah switch, API hanya melayani gudang aktif.

## 1) Area yang Terdampak oleh Switch Gudang

Saat user admin/spectator mengganti gudang aktif (`current_gudang_id`), endpoint berikut harus konsisten:

-   Endpoint list harus mengikuti gudang aktif atau gudang yang diizinkan.
-   Endpoint detail harus menolak akses data dari gudang yang tidak diizinkan.
-   Endpoint create harus menolak `gudang_id` di luar akses user.
-   Endpoint export harus menolak gudang di luar akses user.

## 2) Perbaikan API yang Sudah Diterapkan

### A. Gudang & Stok

-   `GET /api/v1/stok`

    -   Sudah diperbaiki sebelumnya agar:
        -   role spectator memakai relasi gudang yang benar.
        -   mendukung `gudang_id` dengan validasi akses.
        -   fallback ke `current_gudang_id` untuk non-super-admin.
        -   normalisasi total `stok = stok_penjualan + stok_gratis + stok_sample`.

-   `GET /api/v1/gudang/stok/export`
    -   Diperbaiki validasi akses gudang:
        -   sebelumnya hanya cek role admin.
        -   sekarang semua non-super-admin (termasuk spectator) wajib lolos `canAccessGudang`.

### B. Dashboard

-   `GET /api/v1/dashboard`
    -   Untuk admin/spectator, query biaya sekarang ikut `gudang_id` aktif.
    -   `pending_approval` sekarang konsisten mengikuti gudang aktif untuk admin/spectator.

### C. Strict Scope pada Modul Transaksi

Untuk admin/spectator, endpoint berikut sekarang dipaksa mengikuti **gudang aktif**:

-   Penjualan
-   Pembelian
-   Pembayaran
-   Kunjungan
-   Biaya
-   Penerimaan Barang

Aturan yang diterapkan:

-   List: hanya data dari gudang aktif.
-   Detail by ID: ditolak jika gudang data bukan gudang aktif.
-   Approve/Cancel (admin): hanya bisa untuk transaksi di gudang aktif.
-   Create (admin): `gudang_id` request harus sama dengan gudang aktif.

Tambahan:

-   Spectator diblokir membuat pembayaran agar tetap read-only.
-   Penerimaan barang list admin/spectator sekarang mengikuti gudang aktif (tidak semua assignment sekaligus).

### D. Detail Endpoint Authorization (anti bocor lintas gudang)

Ditambahkan guard akses gudang untuk admin/spectator pada endpoint detail berikut:

-   `GET /api/v1/pembayaran/{id}`
-   `GET /api/v1/penerimaan-barang/{id}`
-   `GET /api/v1/pembelian/{id}`
-   `GET /api/v1/kunjungan/{id}`
-   `GET /api/v1/biaya/{id}` (pakai akses gudang + relasi user/approver)
-   `GET /api/v1/kontak/{id}` (dibatasi current gudang / global `gudang_id = null`)

### E. Create Endpoint Authorization (anti create lintas gudang)

Ditambahkan guard akses gudang untuk non-super-admin:

-   `POST /api/v1/penjualan`
-   `POST /api/v1/pembelian`
-   `POST /api/v1/pembayaran` (cek akses terhadap gudang milik penjualan)

### F. Penerimaan Barang API Scope

Ditambahkan guard akses gudang:

-   `GET /api/v1/penerimaan-barang/pembelian-by-gudang/{gudangId}`
-   `GET /api/v1/penerimaan-barang/pembelian-detail/{id}`

## 3) File yang Diubah

-   `app/Http/Controllers/Api/StokController.php`
-   `app/Http/Controllers/Api/GudangController.php`
-   `app/Http/Controllers/Api/DashboardController.php`
-   `app/Http/Controllers/Api/PembayaranController.php`
-   `app/Http/Controllers/Api/PenerimaanBarangController.php`
-   `app/Http/Controllers/Api/PembelianController.php`
-   `app/Http/Controllers/Api/PenjualanController.php`
-   `app/Http/Controllers/Api/KunjunganController.php`
-   `app/Http/Controllers/Api/BiayaController.php`
-   `app/Http/Controllers/Api/KontakController.php`

## 4) Checklist Integrasi Flutter

-   Setelah switch gudang, selalu panggil:
    -   `POST /api/v1/gudang/switch`
-   Setelah sukses switch, refresh modul yang sensitif gudang:
    -   dashboard
    -   stok
    -   produk
    -   penjualan/pembelian/kunjungan/pembayaran/penerimaan-barang
-   Saat `current_gudang_id` berubah, invalidate cache list/detail modul transaksi agar data lama dari gudang sebelumnya tidak tampil.
-   Untuk request stok, rekomendasi tetap kirim `gudang_id` eksplisit saat dibutuhkan:
    -   `GET /api/v1/stok?gudang_id={currentGudangId}`

## 5) Catatan Keamanan

Jika endpoint detail/create tidak memiliki guard `canAccessGudang`, user admin/spectator bisa mengakses data lintas gudang hanya dengan menebak ID resource. Perbaikan ini menutup celah tersebut dan menyamakan perilaku API dengan konsep switch gudang.
