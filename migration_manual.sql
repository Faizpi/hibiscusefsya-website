-- ============================================================
-- MIGRATION MANUAL - Jalankan di phpMyAdmin Hostinger
-- Tanggal: 28 Februari 2026
-- ============================================================

-- 1. Tambah kolom stok_penjualan, stok_gratis, stok_sample ke gudang_produk
ALTER TABLE `gudang_produk` 
    ADD COLUMN `stok_penjualan` INT NOT NULL DEFAULT 0 AFTER `stok`,
    ADD COLUMN `stok_gratis` INT NOT NULL DEFAULT 0 AFTER `stok_penjualan`,
    ADD COLUMN `stok_sample` INT NOT NULL DEFAULT 0 AFTER `stok_gratis`;

-- Migrasi stok lama ke stok_penjualan
UPDATE `gudang_produk` SET `stok_penjualan` = `stok` WHERE `stok` > 0;

-- 2. Tambah kolom tipe_stok (penjualan/gratis/sample), batch_number, expired_date ke penerimaan_barang_items
ALTER TABLE `penerimaan_barang_items` 
    ADD COLUMN `tipe_stok` VARCHAR(20) NOT NULL DEFAULT 'penjualan' AFTER `qty_reject`,
    ADD COLUMN `batch_number` VARCHAR(100) NULL AFTER `tipe_stok`,
    ADD COLUMN `expired_date` DATE NULL AFTER `batch_number`;

-- 3. Tambah kolom pin ke kontaks (untuk login customer)
ALTER TABLE `kontaks` 
    ADD COLUMN `pin` VARCHAR(6) NULL AFTER `no_telp`;

-- 4. Update tabel migrations agar artisan tahu migration sudah jalan
INSERT INTO `migrations` (`migration`, `batch`) VALUES 
    ('2026_01_15_000001_add_stok_type_columns_to_gudang_produk_table', (SELECT COALESCE(MAX(b.batch), 0) + 1 FROM (SELECT batch FROM migrations) AS b)),
    ('2026_01_15_000002_add_batch_expired_stok_type_to_penerimaan_barang_items', (SELECT COALESCE(MAX(b.batch), 0) + 1 FROM (SELECT batch FROM migrations) AS b)),
    ('2026_01_15_000003_add_pin_to_kontaks_table', (SELECT COALESCE(MAX(b.batch), 0) + 1 FROM (SELECT batch FROM migrations) AS b));
