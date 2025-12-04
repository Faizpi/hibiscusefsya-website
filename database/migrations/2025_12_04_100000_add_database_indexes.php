<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDatabaseIndexes extends Migration
{
    /**
     * Helper function untuk cek apakah index sudah ada
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Helper function untuk tambah index dengan aman
     */
    private function addIndexIfNotExists($table, $columns, $indexName, $unique = false)
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName, $unique) {
                if ($unique) {
                    $table->unique($columns, $indexName);
                } else {
                    $table->index($columns, $indexName);
                }
            });
            echo "  ✓ Created index: {$indexName}\n";
        } else {
            echo "  - Index exists: {$indexName}\n";
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        echo "\n=== Adding Database Indexes ===\n\n";

        // ==================== USERS ====================
        echo "Table: users\n";
        $this->addIndexIfNotExists('users', ['role'], 'idx_users_role');
        $this->addIndexIfNotExists('users', ['gudang_id'], 'idx_users_gudang');

        // ==================== PRODUKS ====================
        echo "\nTable: produks\n";
        $this->addIndexIfNotExists('produks', ['nama_produk'], 'idx_produks_nama');

        // ==================== GUDANG_PRODUK ====================
        echo "\nTable: gudang_produk\n";
        $this->addIndexIfNotExists('gudang_produk', ['gudang_id', 'stok'], 'idx_gudang_produk_stok');

        // ==================== PENJUALANS ====================
        echo "\nTable: penjualans\n";
        $this->addIndexIfNotExists('penjualans', ['status'], 'idx_penjualans_status');
        $this->addIndexIfNotExists('penjualans', ['tgl_transaksi'], 'idx_penjualans_tgl');
        $this->addIndexIfNotExists('penjualans', ['user_id'], 'idx_penjualans_user');
        $this->addIndexIfNotExists('penjualans', ['approver_id'], 'idx_penjualans_approver');
        $this->addIndexIfNotExists('penjualans', ['gudang_id'], 'idx_penjualans_gudang');
        $this->addIndexIfNotExists('penjualans', ['tgl_jatuh_tempo'], 'idx_penjualans_tempo');
        $this->addIndexIfNotExists('penjualans', ['created_at'], 'idx_penjualans_created');
        $this->addIndexIfNotExists('penjualans', ['user_id', 'status'], 'idx_penjualans_user_status');
        $this->addIndexIfNotExists('penjualans', ['approver_id', 'status'], 'idx_penjualans_appr_status');

        // ==================== PEMBELIANS ====================
        echo "\nTable: pembelians\n";
        $this->addIndexIfNotExists('pembelians', ['status'], 'idx_pembelians_status');
        $this->addIndexIfNotExists('pembelians', ['tgl_transaksi'], 'idx_pembelians_tgl');
        $this->addIndexIfNotExists('pembelians', ['user_id'], 'idx_pembelians_user');
        $this->addIndexIfNotExists('pembelians', ['approver_id'], 'idx_pembelians_approver');
        $this->addIndexIfNotExists('pembelians', ['gudang_id'], 'idx_pembelians_gudang');
        $this->addIndexIfNotExists('pembelians', ['tgl_jatuh_tempo'], 'idx_pembelians_tempo');
        $this->addIndexIfNotExists('pembelians', ['urgensi'], 'idx_pembelians_urgensi');
        $this->addIndexIfNotExists('pembelians', ['created_at'], 'idx_pembelians_created');
        $this->addIndexIfNotExists('pembelians', ['user_id', 'status'], 'idx_pembelians_user_status');
        $this->addIndexIfNotExists('pembelians', ['approver_id', 'status'], 'idx_pembelians_appr_status');

        // ==================== BIAYAS ====================
        echo "\nTable: biayas\n";
        $this->addIndexIfNotExists('biayas', ['status'], 'idx_biayas_status');
        $this->addIndexIfNotExists('biayas', ['tgl_transaksi'], 'idx_biayas_tgl');
        $this->addIndexIfNotExists('biayas', ['user_id'], 'idx_biayas_user');
        $this->addIndexIfNotExists('biayas', ['approver_id'], 'idx_biayas_approver');
        $this->addIndexIfNotExists('biayas', ['created_at'], 'idx_biayas_created');
        $this->addIndexIfNotExists('biayas', ['user_id', 'status'], 'idx_biayas_user_status');
        $this->addIndexIfNotExists('biayas', ['approver_id', 'status'], 'idx_biayas_appr_status');

        // ==================== ITEM TABLES ====================
        echo "\nTable: penjualan_items\n";
        $this->addIndexIfNotExists('penjualan_items', ['produk_id'], 'idx_penjualan_items_produk');

        echo "\nTable: pembelian_items\n";
        $this->addIndexIfNotExists('pembelian_items', ['produk_id'], 'idx_pembelian_items_produk');

        echo "\nTable: biaya_items\n";
        $this->addIndexIfNotExists('biaya_items', ['kategori'], 'idx_biaya_items_kategori');

        // ==================== KONTAKS ====================
        echo "\nTable: kontaks\n";
        $this->addIndexIfNotExists('kontaks', ['nama'], 'idx_kontaks_nama');
        $this->addIndexIfNotExists('kontaks', ['email'], 'idx_kontaks_email');

        echo "\n=== Indexes Added Successfully ===\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Helper untuk drop index dengan aman
        $dropIndex = function ($table, $indexName) {
            try {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // Index tidak ada, skip
            }
        };

        // Users
        $dropIndex('users', 'idx_users_role');
        $dropIndex('users', 'idx_users_gudang');

        // Produks
        $dropIndex('produks', 'idx_produks_nama');

        // Gudang Produk
        $dropIndex('gudang_produk', 'idx_gudang_produk_stok');

        // Penjualans
        $dropIndex('penjualans', 'idx_penjualans_status');
        $dropIndex('penjualans', 'idx_penjualans_tgl');
        $dropIndex('penjualans', 'idx_penjualans_user');
        $dropIndex('penjualans', 'idx_penjualans_approver');
        $dropIndex('penjualans', 'idx_penjualans_gudang');
        $dropIndex('penjualans', 'idx_penjualans_tempo');
        $dropIndex('penjualans', 'idx_penjualans_created');
        $dropIndex('penjualans', 'idx_penjualans_user_status');
        $dropIndex('penjualans', 'idx_penjualans_appr_status');

        // Pembelians
        $dropIndex('pembelians', 'idx_pembelians_status');
        $dropIndex('pembelians', 'idx_pembelians_tgl');
        $dropIndex('pembelians', 'idx_pembelians_user');
        $dropIndex('pembelians', 'idx_pembelians_approver');
        $dropIndex('pembelians', 'idx_pembelians_gudang');
        $dropIndex('pembelians', 'idx_pembelians_tempo');
        $dropIndex('pembelians', 'idx_pembelians_urgensi');
        $dropIndex('pembelians', 'idx_pembelians_created');
        $dropIndex('pembelians', 'idx_pembelians_user_status');
        $dropIndex('pembelians', 'idx_pembelians_appr_status');

        // Biayas
        $dropIndex('biayas', 'idx_biayas_status');
        $dropIndex('biayas', 'idx_biayas_tgl');
        $dropIndex('biayas', 'idx_biayas_user');
        $dropIndex('biayas', 'idx_biayas_approver');
        $dropIndex('biayas', 'idx_biayas_created');
        $dropIndex('biayas', 'idx_biayas_user_status');
        $dropIndex('biayas', 'idx_biayas_appr_status');

        // Items
        $dropIndex('penjualan_items', 'idx_penjualan_items_produk');
        $dropIndex('pembelian_items', 'idx_pembelian_items_produk');
        $dropIndex('biaya_items', 'idx_biaya_items_kategori');

        // Kontaks
        $dropIndex('kontaks', 'idx_kontaks_nama');
        $dropIndex('kontaks', 'idx_kontaks_email');
    }
}
