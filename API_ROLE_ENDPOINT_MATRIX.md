# API Role Endpoint Matrix (Flutter)

Dokumen ini untuk mapping endpoint API ke role yang boleh memanggilnya.

Catatan:
- Semua endpoint di bawah /api/v1 memerlukan token, kecuali login.
- Beberapa endpoint dibuka di route untuk semua token, tetapi tetap dibatasi di level controller. Matrix ini mengikuti perilaku aktual controller.

## 1. Authentication

| Endpoint | Method | super_admin | admin | spectator | user |
|---|---|---|---|---|---|
| /api/v1/login | POST | Ya | Ya | Ya | Ya |
| /api/v1/logout | POST | Ya | Ya | Ya | Ya |
| /api/v1/profile | GET/PUT | Ya | Ya | Ya | Ya |
| /api/v1/change-password | POST | Ya | Ya | Ya | Ya |

## 2. Dashboard and Reports

| Endpoint Group | super_admin | admin | spectator | user | Catatan |
|---|---|---|---|---|---|
| /api/v1/dashboard* | Ya | Ya | Ya | Ya | Data terfilter by role+gudang |
| /api/v1/dashboard/export/options | Ya | Ya | Tidak | Tidak | Samakan web |
| /api/v1/dashboard/export | Ya | Ya | Tidak | Tidak |  |
| /api/v1/dashboard/daily-report* | Ya | Ya | Ya | Ya | Data milik user login |

## 3. Gudang and Stock

| Endpoint | Method | super_admin | admin | spectator | user |
|---|---|---|---|---|---|
| /api/v1/gudang | GET | Ya | Ya | Ya | Ya |
| /api/v1/gudang/switch | POST | Tidak | Ya | Ya | Tidak |
| /api/v1/gudang/stok | GET | Ya | Ya | Ya | Tidak |
| /api/v1/gudang/stok-log | GET | Ya | Ya | Tidak | Tidak |
| /api/v1/gudang/stok/export | GET | Ya | Ya | Ya | Tidak |
| /api/v1/stok | GET | Ya | Ya | Ya | Tidak |
| /api/v1/stok | POST | Ya | Tidak | Tidak | Tidak |
| /api/v1/stok/log | GET | Ya | Ya | Tidak | Tidak |

## 4. Master Data

### 4.1 Produk

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| /api/v1/produk* (list/show/stok by gudang) | Ya | Ya (scope gudang) | Ya (scope gudang) | Ya (scope gudang) |
| /api/v1/produk (POST/PUT/DELETE) | Ya | Tidak | Tidak | Tidak |

### 4.2 Kontak

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/kontak* | Ya | Ya | Ya | Ya |
| POST /api/v1/kontak | Ya | Ya | Tidak | Ya |
| PUT /api/v1/kontak/{id} | Ya | Ya (pin-only rule) | Tidak | Ya (pin-only rule) |
| DELETE /api/v1/kontak/{id} | Ya | Tidak | Tidak | Tidak |

### 4.3 User Management

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| /api/v1/users* | Ya | Tidak | Tidak | Tidak |

## 5. Transaction Modules

## 5.1 Penjualan

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/penjualan* | Ya | Ya (scope gudang) | Ya (read-only, scope gudang) | Ya (milik sendiri) |
| POST /api/v1/penjualan | Ya | Ya | Tidak | Ya |
| PUT /api/v1/penjualan/{id} | Ya | Ya (terbatas) | Tidak | Terbatas milik sendiri |
| POST /api/v1/penjualan/{id}/approve | Ya | Ya (scope gudang) | Tidak | Tidak |
| POST /api/v1/penjualan/{id}/cancel | Ya | Ya (pending only) | Tidak | Tidak |
| POST /api/v1/penjualan/{id}/uncancel | Ya | Tidak | Tidak | Tidak |
| POST /api/v1/penjualan/{id}/mark-paid | Ya | Ya | Tidak | Tidak |
| POST /api/v1/penjualan/{id}/unmark-paid | Ya | Tidak | Tidak | Tidak |

## 5.2 Pembelian

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/pembelian* | Ya | Ya (scope gudang) | Ya (read-only) | Ya (milik sendiri) |
| POST /api/v1/pembelian | Ya | Ya | Tidak | Ya |
| PUT /api/v1/pembelian/{id} | Ya | Ya (terbatas) | Tidak | Terbatas milik sendiri |
| POST /api/v1/pembelian/{id}/approve | Ya | Ya | Tidak | Tidak |
| POST /api/v1/pembelian/{id}/cancel | Ya | Ya (pending only) | Tidak | Tidak |
| POST /api/v1/pembelian/{id}/uncancel | Ya | Tidak | Tidak | Tidak |

## 5.3 Biaya

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/biaya* | Ya | Ya (scope gudang/approver/user) | Ya (read-only) | Ya (milik sendiri) |
| POST /api/v1/biaya | Ya | Ya | Tidak | Ya |
| PUT /api/v1/biaya/{id} | Ya | Tidak | Tidak | Tidak |
| POST /api/v1/biaya/{id}/approve | Ya | Ya | Tidak | Tidak |
| POST /api/v1/biaya/{id}/cancel | Ya | Ya (pending only) | Tidak | Tidak |
| POST /api/v1/biaya/{id}/uncancel | Ya | Tidak | Tidak | Tidak |

## 5.4 Kunjungan

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/kunjungan* | Ya | Ya (scope gudang/assigned) | Ya (read-only) | Ya (milik sendiri) |
| POST /api/v1/kunjungan | Ya | Ya | Tidak | Ya |
| PUT /api/v1/kunjungan/{id} | Ya | Ya (terbatas) | Tidak | Terbatas milik sendiri |
| POST /api/v1/kunjungan/{id}/approve | Ya | Ya | Tidak | Tidak |
| POST /api/v1/kunjungan/{id}/cancel | Ya | Ya (pending only) | Tidak | Tidak |
| POST /api/v1/kunjungan/{id}/uncancel | Ya | Tidak | Tidak | Tidak |

## 5.5 Pembayaran

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/pembayaran* | Ya | Ya (scope gudang) | Ya (read-only) | Ya (milik sendiri) |
| POST /api/v1/pembayaran | Ya | Ya | Tidak | Ya |
| POST /api/v1/pembayaran/{id}/approve | Ya | Ya | Tidak | Tidak |
| POST /api/v1/pembayaran/{id}/cancel | Ya | Ya (pending only) | Tidak | Tidak |
| POST /api/v1/pembayaran/{id}/uncancel | Ya | Tidak | Tidak | Tidak |

## 5.6 Penerimaan Barang

| Endpoint Pattern | super_admin | admin | spectator | user |
|---|---|---|---|---|
| GET /api/v1/penerimaan-barang* | Ya | Ya (scope gudang) | Ya (read-only) | Ya (milik sendiri) |
| POST /api/v1/penerimaan-barang | Ya | Ya | Tidak | Ya |
| POST /api/v1/penerimaan-barang/{id}/approve | Ya | Ya | Tidak | Tidak |
| POST /api/v1/penerimaan-barang/{id}/cancel | Ya | Ya (pending only) | Tidak | Tidak |

## 6. Flutter Enforcement Strategy

- Layer 1: hide UI berdasarkan role + permission map.
- Layer 2: sebelum call endpoint, lakukan guard di service layer.
- Layer 3: tangani 403 dari backend sebagai source of truth.

## 7. Rule penting implementasi

- Jangan hanya cek role; cek juga status transaksi dan scope gudang.
- Untuk admin/spectator, endpoint list wajib selalu kirim parameter gudang aktif bila dibutuhkan.
- Untuk spectator, mode default harus read-only di semua modul.
