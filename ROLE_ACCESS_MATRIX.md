# Role Access Matrix (Web + Acuan Flutter)

Dokumen ini merangkum hak akses aktual berdasarkan implementasi kode saat ini.

## 1. Daftar Role

-   `super_admin`: akses penuh, termasuk data master dan aksi destruktif.
-   `admin`: akses operasional dan approval pada gudang yang dipegang.
-   `spectator`: read-only pada gudang yang diassign (dapat switch gudang aktif).
-   `user`: sales/staf, fokus input transaksi milik sendiri dan menunggu approval.

Sumber utama:

-   `app/Http/Middleware/CheckRole.php`
-   `app/User.php`
-   `routes/web.php`
-   `routes/api.php`

## 2. Aturan Global Akses

-   Semua route web utama berada di group `auth`.
-   Middleware `role:admin` juga meloloskan `spectator` (read-only by controller/view).
-   `super_admin` di middleware selalu lolos ke semua role route.
-   Cakupan data banyak ditentukan oleh gudang:
    -   `super_admin`: semua gudang.
    -   `admin`: gudang dari pivot `admin_gudang` + `current_gudang_id`.
    -   `spectator`: gudang dari pivot `spectator_gudang` + `current_gudang_id`.
    -   `user`: satu gudang (`gudang_id`).

## 3. Matrix Akses Menu Sidebar (Web)

Sumber menu: `resources/views/layouts/app.blade.php`.

| Menu              | super_admin | admin | spectator | user               | Catatan                                                  |
| ----------------- | ----------- | ----- | --------- | ------------------ | -------------------------------------------------------- |
| Dashboard         | Ya          | Ya    | Ya        | Ya                 | Route tersedia untuk semua login                         |
| Penjualan         | Ya          | Ya    | Ya        | Ya                 | Spectator read-only di level aksi                        |
| Pembelian         | Ya          | Ya    | Ya        | Ya                 | Spectator read-only di level aksi                        |
| Biaya             | Ya          | Ya    | Ya        | Ya                 | Spectator read-only di level aksi                        |
| Pembayaran        | Ya          | Ya    | Ya        | Ya                 | Spectator read-only di level aksi                        |
| Penerimaan Barang | Ya          | Ya    | Ya        | Ya                 | Spectator read-only di level aksi                        |
| Kunjungan         | Ya          | Ya    | Ya        | Ya                 | Spectator read-only di level aksi                        |
| Pengguna          | Ya          | Tidak | Tidak     | Tidak              | Hanya super_admin                                        |
| Gudang            | Ya          | Tidak | Tidak     | Tidak              | Hanya super_admin                                        |
| Produk            | Ya          | Tidak | Tidak     | Tidak              | Hanya super_admin                                        |
| Kontak            | Ya          | Ya    | Ya        | Ya                 | Spectator tidak bisa create/edit/delete                  |
| Stok Gudang       | Ya          | Ya    | Ya        | Ya (menu terlihat) | Untuk `user`, route stok ditolak (lihat gap di bagian 8) |

## 4. Matrix Akses Fitur Inti per Modul

Keterangan singkat:

-   `Lihat`: index/show/print.
-   `Buat`: create/store.
-   `Ubah`: edit/update.
-   `Approve`: approve transaksi.
-   `Cancel`: cancel transaksi.
-   `Uncancel`: kembalikan dari canceled ke pending.
-   `Hapus`: destroy.
-   `Hapus Lampiran`: delete lampiran by index.

### 4.1 Penjualan

Sumber: `app/Http/Controllers/PenjualanController.php`

| Aksi                  | super_admin | admin                           | spectator                | user                   |
| --------------------- | ----------- | ------------------------------- | ------------------------ | ---------------------- |
| Lihat data            | Semua       | Gudang yang bisa diakses        | Gudang aktif (read-only) | Milik sendiri          |
| Buat                  | Ya          | Ya                              | Tidak                    | Ya                     |
| Ubah                  | Ya          | Ya (terbatas by logic data)     | Tidak                    | Terbatas milik sendiri |
| Approve               | Ya          | Ya (hanya gudang yang dipegang) | Tidak                    | Tidak                  |
| Mark as Paid (Lunas)  | Ya          | Ya                              | Tidak                    | Tidak                  |
| Unmark Paid           | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Cancel Pending        | Ya          | Ya                              | Tidak                    | Tidak                  |
| Cancel Approved/Lunas | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Uncancel              | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Hapus                 | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Hapus Lampiran        | Ya          | Tidak                           | Tidak                    | Tidak                  |

### 4.2 Pembelian

Sumber: `app/Http/Controllers/PembelianController.php`

| Aksi                  | super_admin | admin                           | spectator                | user                   |
| --------------------- | ----------- | ------------------------------- | ------------------------ | ---------------------- |
| Lihat data            | Semua       | Gudang aktif/dipegang           | Gudang aktif (read-only) | Milik sendiri          |
| Buat                  | Ya          | Ya                              | Tidak                    | Ya                     |
| Ubah                  | Ya          | Ya (terbatas by logic data)     | Tidak                    | Terbatas milik sendiri |
| Approve               | Ya          | Ya (hanya gudang yang dipegang) | Tidak                    | Tidak                  |
| Cancel Pending        | Ya          | Ya                              | Tidak                    | Tidak                  |
| Cancel Approved/Lunas | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Uncancel              | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Hapus                 | Ya          | Tidak                           | Tidak                    | Tidak                  |
| Hapus Lampiran        | Ya          | Tidak                           | Tidak                    | Tidak                  |

### 4.3 Biaya

Sumber: `app/Http/Controllers/BiayaController.php`

| Aksi            | super_admin | admin                                | spectator                            | user                         |
| --------------- | ----------- | ------------------------------------ | ------------------------------------ | ---------------------------- |
| Lihat data      | Semua       | Gudang akses + approver/user terkait | Gudang akses + approver/user terkait | Milik sendiri/gudang sendiri |
| Buat            | Ya          | Ya                                   | Tidak                                | Ya                           |
| Ubah            | Ya          | Tidak (explicit ditolak)             | Tidak                                | Tidak (hanya super_admin)    |
| Approve         | Ya          | Ya (approver/gudang terkait)         | Tidak                                | Tidak                        |
| Cancel Pending  | Ya          | Ya                                   | Tidak                                | Tidak                        |
| Cancel Approved | Ya          | Tidak                                | Tidak                                | Tidak                        |
| Uncancel        | Ya          | Tidak                                | Tidak                                | Tidak                        |
| Hapus           | Ya          | Tidak                                | Tidak                                | Tidak                        |
| Hapus Lampiran  | Ya          | Tidak                                | Tidak                                | Tidak                        |

### 4.4 Kunjungan

Sumber: `app/Http/Controllers/KunjunganController.php`

| Aksi            | super_admin | admin                                        | spectator                                                | user                   |
| --------------- | ----------- | -------------------------------------------- | -------------------------------------------------------- | ---------------------- |
| Lihat data      | Semua       | Gudang akses + yang ditunjuk + milik sendiri | Gudang akses + yang ditunjuk + milik sendiri (read-only) | Milik sendiri          |
| Buat            | Ya          | Ya                                           | Tidak                                                    | Ya                     |
| Ubah            | Ya          | Ya (sesuai akses data)                       | Tidak                                                    | Terbatas milik sendiri |
| Approve         | Ya          | Ya (approver/gudang terkait)                 | Tidak                                                    | Tidak                  |
| Cancel Pending  | Ya          | Ya                                           | Tidak                                                    | Tidak                  |
| Cancel Approved | Ya          | Tidak                                        | Tidak                                                    | Tidak                  |
| Uncancel        | Ya          | Tidak                                        | Tidak                                                    | Tidak                  |
| Hapus           | Ya          | Tidak                                        | Tidak                                                    | Tidak                  |
| Hapus Lampiran  | Ya          | Tidak                                        | Tidak                                                    | Tidak                  |

### 4.5 Pembayaran

Sumber: `app/Http/Controllers/PembayaranController.php`

| Aksi            | super_admin | admin                           | spectator                        | user          |
| --------------- | ----------- | ------------------------------- | -------------------------------- | ------------- |
| Lihat data      | Semua       | Gudang yang dikelola            | Gudang yang diassign (read-only) | Milik sendiri |
| Buat            | Ya          | Ya                              | Tidak                            | Ya            |
| Approve         | Ya          | Ya (hanya gudang yang dipegang) | Tidak                            | Tidak         |
| Cancel Pending  | Ya          | Ya (gudang dipegang)            | Tidak                            | Tidak         |
| Cancel Approved | Ya          | Tidak                           | Tidak                            | Tidak         |
| Uncancel        | Ya          | Tidak                           | Tidak                            | Tidak         |
| Hapus           | Ya          | Tidak                           | Tidak                            | Tidak         |
| Hapus Lampiran  | Ya          | Tidak                           | Tidak                            | Tidak         |

### 4.6 Penerimaan Barang

Sumber: `app/Http/Controllers/PenerimaanBarangController.php`

| Aksi            | super_admin | admin                | spectator                        | user          |
| --------------- | ----------- | -------------------- | -------------------------------- | ------------- |
| Lihat data      | Semua       | Gudang yang dikelola | Gudang yang diassign (read-only) | Milik sendiri |
| Buat            | Ya          | Ya                   | Tidak                            | Ya            |
| Approve         | Ya          | Ya                   | Tidak                            | Tidak         |
| Cancel Pending  | Ya          | Ya                   | Tidak                            | Tidak         |
| Cancel Approved | Ya          | Tidak                | Tidak                            | Tidak         |
| Uncancel        | Ya          | Tidak                | Tidak                            | Tidak         |
| Hapus           | Ya          | Tidak                | Tidak                            | Tidak         |
| Hapus Lampiran  | Ya          | Tidak                | Tidak                            | Tidak         |

### 4.7 Kontak

Sumber: `app/Http/Controllers/KontakController.php`

| Aksi            | super_admin | admin                                           | spectator                    | user                           |
| --------------- | ----------- | ----------------------------------------------- | ---------------------------- | ------------------------------ |
| Lihat           | Semua       | Gudang akses + kontak global (`gudang_id` null) | Gudang akses + kontak global | Gudang sendiri + kontak global |
| Buat            | Ya          | Ya                                              | Tidak                        | Ya                             |
| Ubah data penuh | Ya          | Tidak                                           | Tidak                        | Tidak                          |
| Ubah PIN saja   | Ya          | Ya                                              | Tidak                        | Ya                             |
| Hapus           | Ya          | Tidak                                           | Tidak                        | Tidak                          |
| Print/PDF       | Ya          | Ya (sesuai akses kontak)                        | Ya (sesuai akses kontak)     | Ya (sesuai akses kontak)       |

### 4.8 Stok

Sumber: `app/Http/Controllers/StokController.php`

| Aksi               | super_admin | admin             | spectator                                        | user  |
| ------------------ | ----------- | ----------------- | ------------------------------------------------ | ----- |
| Lihat stok         | Ya          | Ya                | Ya                                               | Tidak |
| Export stok        | Ya          | Ya (gudang akses) | Ya (via route admin-group, dengan filter gudang) | Tidak |
| Log perubahan stok | Ya          | Ya                | Tidak                                            | Tidak |
| Ubah stok manual   | Ya          | Tidak             | Tidak                                            | Tidak |

### 4.9 Dashboard dan Report

Sumber: `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php`

| Fitur                       | super_admin                   | admin                         | spectator                                     | user                          |
| --------------------------- | ----------------------------- | ----------------------------- | --------------------------------------------- | ----------------------------- |
| Dashboard cards             | Ya                            | Ya                            | Ya                                            | Ya                            |
| Chart dashboard             | Ya                            | Ya                            | Ya                                            | Tidak                         |
| Filter gudang di dashboard  | Ya (semua)                    | Ya (gudang akses)             | Ya (gudang akses)                             | Tidak                         |
| Tabel aktivitas dashboard   | Semua transaksi               | Pending approval              | Semua aktivitas (sesuai query role spectator) | Tidak (diganti welcome card)  |
| Generate report (Excel/PDF) | Ya                            | Ya                            | Tidak                                         | Tidak                         |
| Daily report PDF            | Ya (hanya data milik dirinya) | Ya (hanya data milik dirinya) | Ya (hanya data milik dirinya)                 | Ya (hanya data milik dirinya) |

## 5. Rule Penting untuk Implementasi Flutter

Agar Flutter sama persis dengan backend saat ini, gunakan aturan berikut:

-   Spectator tidak boleh punya tombol create/edit/delete/approve/cancel/uncancel.
-   Admin boleh approve/cancel pending, tapi tidak boleh cancel transaksi yang sudah approved/lunas.
-   Super admin punya semua aksi termasuk uncancel, delete, delete lampiran, ubah stok manual.
-   Semua list dan detail harus difilter berdasarkan akses gudang user.
-   Untuk admin/spectator, gunakan konsep gudang aktif (`current_gudang_id`) dan endpoint switch gudang.

## 6. Mapping Permission Key (Saran untuk Flutter)

Agar rapih, buat permission key (boolean) per role setelah login:

-   `can_view_dashboard`
-   `can_view_charts`
-   `can_export_report`
-   `can_switch_gudang`
-   `can_manage_users`
-   `can_manage_master_data`
-   `can_manage_stock_manual`
-   `can_view_stock_log`
-   `can_create_transaction`
-   `can_approve_transaction`
-   `can_cancel_approved`
-   `can_uncancel_transaction`
-   `can_delete_transaction`
-   `can_delete_attachment`
-   `can_edit_contact_full`
-   `can_edit_contact_pin_only`

## 7. Ringkasan Cepat per Role

-   `super_admin`: full access semua menu dan fitur.
-   `admin`: operasional + approval pada gudang yang dipegang, tanpa akses master super admin.
-   `spectator`: lihat data (read-only) pada gudang yang diassign, bisa switch gudang aktif.
-   `user`: input transaksi sendiri, tidak bisa approval/cancel/destructive actions.

## 8. Gap/Ketidakkonsistenan Saat Ini (Penting)

Berikut temuan yang biasanya membuat role access terasa acak:

-   Menu `Stok Gudang` tampil untuk `user` di sidebar, tetapi route stok berada di group `role:admin`, sehingga user akan ditolak saat klik.
-   Beberapa filter dan akses biaya memperlakukan data biaya lintas gudang lebih longgar dibanding modul lain, karena biaya juga dibaca via relasi user/approver.
-   Di beberapa modul, aturan read-only spectator dijaga di controller dan view (bukan hanya middleware), jadi Flutter wajib meniru guard ini di level UI juga.

## 9. Referensi File Kunci

-   `routes/web.php`
-   `routes/api.php`
-   `app/Http/Middleware/CheckRole.php`
-   `app/User.php`
-   `resources/views/layouts/app.blade.php`
-   `resources/views/dashboard.blade.php`
-   `app/Http/Controllers/DashboardController.php`
-   `app/Http/Controllers/PenjualanController.php`
-   `app/Http/Controllers/PembelianController.php`
-   `app/Http/Controllers/BiayaController.php`
-   `app/Http/Controllers/KunjunganController.php`
-   `app/Http/Controllers/PembayaranController.php`
-   `app/Http/Controllers/PenerimaanBarangController.php`
-   `app/Http/Controllers/KontakController.php`
-   `app/Http/Controllers/StokController.php`

---

Jika diinginkan, dokumen ini bisa dilanjutkan ke versi "siap coding Flutter" berisi:

-   matrix screen-to-permission,
-   matrix endpoint API per permission,
-   contoh guard navigator dan widget visibility per role.
