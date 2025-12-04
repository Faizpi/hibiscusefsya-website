# Database Indexes & Constraints - Dokumentasi

## 📋 Overview
Migration ini menambahkan **database indexes** dan **unique constraints** untuk meningkatkan performa query dan menjaga integritas data.

---

## 🚀 Cara Menjalankan Migration

### 1. Jalankan Migration Baru
```bash
php artisan migrate
```

### 2. Jika Ingin Rollback
```bash
php artisan migrate:rollback --step=2
```

### 3. Refresh Seluruh Database (HATI-HATI: Hapus Semua Data)
```bash
php artisan migrate:fresh
```

---

## 📊 Indexes yang Ditambahkan

### **USERS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `role` | Single | `idx_users_role` | Filter by role (admin, user, super_admin) |
| `gudang_id` | Single | `idx_users_gudang` | Filter user by gudang |

### **PRODUKS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `nama_produk` | Single | `idx_produks_nama` | Search produk by nama |
| `item_code` | Unique | (auto) | Sudah unique di migration sebelumnya |

### **GUDANG_PRODUK Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `gudang_id, stok` | Composite | `idx_gudang_produk_stok` | Query stok di gudang tertentu |
| `gudang_id, produk_id` | Unique | (auto) | Sudah unique di migration sebelumnya |

### **PENJUALANS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `status` | Single | `idx_penjualans_status` | Filter by status (Pending, Approved, dll) |
| `tgl_transaksi` | Single | `idx_penjualans_tgl_transaksi` | Laporan & dashboard by tanggal |
| `user_id` | Single | `idx_penjualans_user` | Filter transaksi by user |
| `approver_id` | Single | `idx_penjualans_approver` | Dashboard admin |
| `gudang_id` | Single | `idx_penjualans_gudang` | Filter by gudang |
| `tgl_jatuh_tempo` | Single | `idx_penjualans_jatuh_tempo` | Reminder & telat bayar |
| `nomor` | Single + **UNIQUE** | `idx_penjualans_nomor` + `unique_penjualans_nomor` | Search & prevent duplicate |
| `created_at` | Single | `idx_penjualans_created` | Sorting by tanggal dibuat |
| `user_id, status` | Composite | `idx_penjualans_user_status` | User dashboard (pending count, dll) |
| `approver_id, status` | Composite | `idx_penjualans_approver_status` | Admin dashboard |
| `tgl_transaksi, status` | Composite | `idx_penjualans_tgl_status` | Laporan by periode & status |

### **PEMBELIANS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `status` | Single | `idx_pembelians_status` | Filter by status |
| `tgl_transaksi` | Single | `idx_pembelians_tgl_transaksi` | Laporan & dashboard |
| `user_id` | Single | `idx_pembelians_user` | Filter by user |
| `approver_id` | Single | `idx_pembelians_approver` | Dashboard admin |
| `gudang_id` | Single | `idx_pembelians_gudang` | Filter by gudang |
| `tgl_jatuh_tempo` | Single | `idx_pembelians_jatuh_tempo` | Reminder pembayaran |
| `nomor` | Single + **UNIQUE** | `idx_pembelians_nomor` + `unique_pembelians_nomor` | Search & prevent duplicate |
| `urgensi` | Single | `idx_pembelians_urgensi` | Filter by urgensi |
| `created_at` | Single | `idx_pembelians_created` | Sorting |
| `user_id, status` | Composite | `idx_pembelians_user_status` | User dashboard |
| `approver_id, status` | Composite | `idx_pembelians_approver_status` | Admin dashboard |
| `tgl_transaksi, status` | Composite | `idx_pembelians_tgl_status` | Laporan |

### **BIAYAS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `status` | Single | `idx_biayas_status` | Filter by status |
| `tgl_transaksi` | Single | `idx_biayas_tgl_transaksi` | Laporan & dashboard |
| `user_id` | Single | `idx_biayas_user` | Filter by user |
| `approver_id` | Single | `idx_biayas_approver` | Dashboard admin |
| `nomor` | Single + **UNIQUE** | `idx_biayas_nomor` + `unique_biayas_nomor` | Search & prevent duplicate |
| `created_at` | Single | `idx_biayas_created` | Sorting |
| `user_id, status` | Composite | `idx_biayas_user_status` | User dashboard |
| `approver_id, status` | Composite | `idx_biayas_approver_status` | Admin dashboard |
| `tgl_transaksi, status` | Composite | `idx_biayas_tgl_status` | Laporan |

### **PENJUALAN_ITEMS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `produk_id` | Single | `idx_penjualan_items_produk` | Join dengan produk |
| `penjualan_id, produk_id` | Composite | `idx_penjualan_items_composite` | Aggregate queries |

### **PEMBELIAN_ITEMS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `produk_id` | Single | `idx_pembelian_items_produk` | Join dengan produk |
| `pembelian_id, produk_id` | Composite | `idx_pembelian_items_composite` | Aggregate queries |

### **BIAYA_ITEMS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `kategori` | Single | `idx_biaya_items_kategori` | Filter by kategori |
| `biaya_id, kategori` | Composite | `idx_biaya_items_composite` | Group by kategori |

### **KONTAKS Table**
| Column | Index Type | Nama Index | Alasan |
|--------|-----------|-----------|--------|
| `nama` | Single | `idx_kontaks_nama` | Search by nama |
| `email` | Single | `idx_kontaks_email` | Search by email |

---

## 🔒 Unique Constraints yang Ditambahkan

| Table | Column | Constraint Name | Tujuan |
|-------|--------|----------------|--------|
| `penjualans` | `nomor` | `unique_penjualans_nomor` | Mencegah duplikasi nomor invoice |
| `pembelians` | `nomor` | `unique_pembelians_nomor` | Mencegah duplikasi nomor PR |
| `biayas` | `nomor` | `unique_biayas_nomor` | Mencegah duplikasi nomor expense |

---

## 📈 Manfaat Database Indexes

### 1. **Meningkatkan Performa Query**
- Query yang filter by `status`, `user_id`, `tgl_transaksi` akan **10-100x lebih cepat**
- Dashboard loading time berkurang signifikan
- Laporan export tidak timeout

### 2. **Optimasi Join Operations**
- Join antara `penjualans` dan `penjualan_items` lebih cepat
- Join antara `users` dan `transaksi` lebih efisien

### 3. **Sorting & Pagination**
- Pagination di index page lebih smooth
- Sorting by date atau status instant

### 4. **Aggregate Functions**
- `COUNT()`, `SUM()`, `AVG()` pada dashboard lebih cepat
- Chart data generation lebih responsif

---

## ⚡ Query yang Akan Lebih Cepat

### Before Indexes:
```sql
-- SLOW: Full table scan
SELECT * FROM penjualans WHERE status = 'Pending';
SELECT * FROM penjualans WHERE user_id = 5 AND status = 'Pending';
SELECT * FROM penjualans WHERE tgl_transaksi BETWEEN '2025-01-01' AND '2025-12-31';
```

### After Indexes:
```sql
-- FAST: Index scan
SELECT * FROM penjualans WHERE status = 'Pending';  -- uses idx_penjualans_status
SELECT * FROM penjualans WHERE user_id = 5 AND status = 'Pending';  -- uses idx_penjualans_user_status
SELECT * FROM penjualans WHERE tgl_transaksi BETWEEN '2025-01-01' AND '2025-12-31';  -- uses idx_penjualans_tgl_transaksi
```

---

## 🎯 Best Practices

### ✅ DO:
- Gunakan indexes pada kolom yang sering di-filter (`WHERE`, `JOIN`, `ORDER BY`)
- Buat composite index untuk kombinasi filter yang sering digunakan
- Monitor query performance dengan `EXPLAIN` command

### ❌ DON'T:
- Jangan terlalu banyak index (max 5-7 per table)
- Jangan index kolom yang jarang digunakan
- Jangan index kolom dengan cardinality rendah (misal: boolean true/false)

---

## 🔍 Cara Mengecek Indexes

### Cek Semua Indexes di Table:
```sql
SHOW INDEXES FROM penjualans;
```

### Cek Query Performance:
```sql
EXPLAIN SELECT * FROM penjualans WHERE status = 'Pending' AND user_id = 5;
```

### Cek Index Usage:
```sql
-- MySQL 8.0+
SELECT * FROM sys.schema_unused_indexes;
```

---

## 📝 Catatan Penting

1. **Indexes mempercepat SELECT**, tapi sedikit memperlambat `INSERT/UPDATE/DELETE`
   - Trade-off ini worth it untuk aplikasi yang lebih banyak read daripada write

2. **Unique Constraints** akan error jika ada duplikat
   - Pastikan data existing sudah bersih sebelum migrate
   - Jika error, check duplikasi dengan:
   ```sql
   SELECT nomor, COUNT(*) FROM penjualans GROUP BY nomor HAVING COUNT(*) > 1;
   ```

3. **Index Size**
   - Indexes memakan storage space (sekitar 10-20% dari table size)
   - Monitor disk space usage

4. **Maintenance**
   - MySQL akan otomatis maintain indexes
   - Tidak perlu rebuild manual kecuali masalah performa

---

## 🐛 Troubleshooting

### Error: Duplicate entry for unique constraint
```bash
# Cek duplikasi
SELECT nomor, COUNT(*) as count FROM penjualans 
WHERE nomor IS NOT NULL 
GROUP BY nomor 
HAVING count > 1;

# Hapus duplikat atau update nomor yang duplikat
UPDATE penjualans SET nomor = CONCAT(nomor, '-', id) 
WHERE id IN (SELECT id FROM ...);
```

### Migration Timeout
```bash
# Jika table terlalu besar, increase timeout
php artisan config:cache
# Edit database/migrations file, tambahkan:
DB::statement('SET SESSION max_execution_time = 0');
```

---

## 📚 Referensi
- [MySQL Index Documentation](https://dev.mysql.com/doc/refman/8.0/en/optimization-indexes.html)
- [Laravel Migration Indexes](https://laravel.com/docs/7.x/migrations#indexes)
- [Database Performance Best Practices](https://use-the-index-luke.com/)

---

**Dibuat:** 4 Desember 2025  
**Author:** System  
**Version:** 1.0
