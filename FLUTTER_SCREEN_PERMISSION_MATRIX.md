# Flutter Screen Permission Matrix

Dokumen ini memetakan screen Flutter ke role dan aksi yang boleh ditampilkan.

## 1. Konvensi Permission

Gunakan key permission berikut di state auth Flutter:

-   can_view_dashboard
-   can_view_charts
-   can_export_report
-   can_switch_gudang
-   can_view_stock
-   can_export_stock
-   can_edit_stock_manual
-   can_view_stock_log
-   can_manage_users
-   can_manage_gudang
-   can_manage_produk
-   can_view_kontak
-   can_create_kontak
-   can_edit_kontak_pin_only
-   can_edit_kontak_full
-   can_delete_kontak
-   can_create_transaction
-   can_edit_transaction
-   can_approve_transaction
-   can_cancel_transaction
-   can_cancel_approved_transaction
-   can_uncancel_transaction
-   can_delete_transaction
-   can_delete_attachment

## 2. Screen-to-Role Matrix

| Screen                       | super_admin | admin | spectator | user  | Catatan UI Flutter                                                   |
| ---------------------------- | ----------- | ----- | --------- | ----- | -------------------------------------------------------------------- |
| DashboardScreen              | Ya          | Ya    | Ya        | Ya    | User: hide chart section                                             |
| DashboardChartSection        | Ya          | Ya    | Ya        | Tidak | Samakan dengan web                                                   |
| ExportReportModal/Screen     | Ya          | Ya    | Tidak     | Tidak | Tombol generate report hanya admin/super_admin                       |
| ActivityTableScreen          | Ya          | Ya    | Ya        | Tidak | User diganti welcome/daily report                                    |
| PenjualanListScreen          | Ya          | Ya    | Ya        | Ya    | Spectator read-only                                                  |
| PenjualanCreateScreen        | Ya          | Ya    | Tidak     | Ya    | Disable entry point untuk spectator                                  |
| PenjualanDetailScreen        | Ya          | Ya    | Ya        | Ya    | Action buttons by status+role                                        |
| PembelianListScreen          | Ya          | Ya    | Ya        | Ya    | Spectator read-only                                                  |
| PembelianCreateScreen        | Ya          | Ya    | Tidak     | Ya    |                                                                      |
| PembelianDetailScreen        | Ya          | Ya    | Ya        | Ya    |                                                                      |
| BiayaListScreen              | Ya          | Ya    | Ya        | Ya    | Spectator read-only                                                  |
| BiayaCreateScreen            | Ya          | Ya    | Tidak     | Ya    |                                                                      |
| BiayaEditScreen              | Ya          | Tidak | Tidak     | Tidak | Hanya super_admin                                                    |
| KunjunganListScreen          | Ya          | Ya    | Ya        | Ya    | Spectator read-only                                                  |
| KunjunganCreateScreen        | Ya          | Ya    | Tidak     | Ya    |                                                                      |
| PembayaranListScreen         | Ya          | Ya    | Ya        | Ya    | Spectator read-only                                                  |
| PembayaranCreateScreen       | Ya          | Ya    | Tidak     | Ya    |                                                                      |
| PenerimaanBarangListScreen   | Ya          | Ya    | Ya        | Ya    | Spectator read-only                                                  |
| PenerimaanBarangCreateScreen | Ya          | Ya    | Tidak     | Ya    |                                                                      |
| KontakListScreen             | Ya          | Ya    | Ya        | Ya    | Semua bisa lihat sesuai scope gudang                                 |
| KontakCreateScreen           | Ya          | Ya    | Tidak     | Ya    |                                                                      |
| KontakEditScreen             | Ya          | Ya    | Tidak     | Ya    | Admin/user: pin-only mode                                            |
| StokScreen                   | Ya          | Ya    | Ya        | Tidak | Penting: web sidebar user menampilkan menu, tapi akses route ditolak |
| StokLogScreen                | Ya          | Ya    | Tidak     | Tidak |                                                                      |
| UserManagementScreen         | Ya          | Tidak | Tidak     | Tidak |                                                                      |
| GudangManagementScreen       | Ya          | Tidak | Tidak     | Tidak |                                                                      |
| ProdukManagementScreen       | Ya          | Tidak | Tidak     | Tidak |                                                                      |
| SwitchGudangControl          | Tidak       | Ya    | Ya        | Tidak | admin/spectator only                                                 |

## 3. Action Visibility Matrix (Transaksi)

| Action                | super_admin | admin                     | spectator | user                   |
| --------------------- | ----------- | ------------------------- | --------- | ---------------------- |
| Create                | Ya          | Ya                        | Tidak     | Ya                     |
| Edit                  | Ya          | Ya (terbatas data/status) | Tidak     | Terbatas milik sendiri |
| Approve               | Ya          | Ya (gudang dipegang)      | Tidak     | Tidak                  |
| Cancel Pending        | Ya          | Ya                        | Tidak     | Tidak                  |
| Cancel Approved/Lunas | Ya          | Tidak                     | Tidak     | Tidak                  |
| Uncancel              | Ya          | Tidak                     | Tidak     | Tidak                  |
| Delete                | Ya          | Tidak                     | Tidak     | Tidak                  |
| Delete Attachment     | Ya          | Tidak                     | Tidak     | Tidak                  |

## 4. Status-based Button Rules

Aturan minimum agar UI Flutter tidak acak:

-   Jika role spectator: hide semua tombol action mutasi.
-   Tombol Approve hanya tampil untuk admin/super_admin dan hanya saat status Pending.
-   Tombol Cancel:
    -   admin: hanya Pending
    -   super_admin: Pending + Approved/Lunas
-   Tombol Uncancel: hanya super_admin dan status Canceled.
-   Tombol Delete/Delete Attachment: hanya super_admin.

## 5. Gudang Scope Rules untuk Query Screen

-   super_admin: tanpa filter gudang default, optional pilih gudang.
-   admin: wajib current_gudang_id atau gudang dari assignment.
-   spectator: wajib current_gudang_id atau gudang dari spectator assignment.
-   user: pakai gudang_id milik user.

## 6. Bottom line untuk Flutter

-   Permission role tidak cukup; wajib gabung dengan status transaksi dan scope gudang.
-   Selalu hitung visible actions secara runtime per item list/detail.
