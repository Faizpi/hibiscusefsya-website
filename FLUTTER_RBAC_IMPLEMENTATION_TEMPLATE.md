# Flutter RBAC Implementation Template

Dokumen ini adalah langkah implementasi praktis untuk role access yang konsisten.

## 1. Struktur yang disarankan

-   Simpan role user dari login/profile endpoint.
-   Simpan gudang scope:
    -   user: gudang_id
    -   admin/spectator: current_gudang_id + list gudang assignment
-   Bentuk object PermissionSet dari role.
-   Semua screen, action button, dan service API wajib lewat guard.

Referensi template kode:

-   flutter_templates/rbac_guard_template.dart

## 2. Alur bootstrap setelah login

1. Login ke endpoint /api/v1/login.
2. Fetch /api/v1/profile.
3. Parse role + gudang scope.
4. Build PermissionSet via mapper.
5. Simpan di state global (Provider/Riverpod/BLoC).

## 3. Implementasi route guard

-   Pada navigasi ke screen sensitif (users/gudang/produk/stok/log/report), cek AppRouteGuard.canOpen.
-   Jika false, redirect ke screen unauthorized atau dashboard.

## 4. Implementasi widget guard

Aturan cepat:

-   spectator: hide semua tombol Create/Edit/Approve/Cancel/Delete.
-   user: show Create, hide Approve/Cancel/Uncancel/Delete.
-   admin: show Approve + Cancel Pending, hide Uncancel/Delete.
-   super_admin: show semua action.

## 5. Implementasi service/API guard

Sebelum memanggil endpoint sensitif:

-   cek EndpointGuard.canCall.
-   jika false, hentikan call dan tampilkan pesan akses ditolak.

Tetap tangani 403 dari backend sebagai fallback wajib.

## 6. Gudang aktif

Untuk admin/spectator:

-   tampilkan dropdown switch gudang.
-   setelah switch sukses, refresh list/listener data screen aktif.
-   setiap query yang perlu gudang harus menggunakan current_gudang_id.

## 7. Checklist anti akses acak

-   Semua tombol action di detail transaksi berbasis role + status + gudang scope.
-   Semua list transaksi pakai gudang scope role.
-   Semua endpoint mutasi ditahan di UI layer, bukan hanya mengandalkan backend.
-   Menu Stok untuk user di Flutter sebaiknya tidak ditampilkan.

## 8. Saran integrasi state

Jika pakai Riverpod:

-   authUserProvider
-   permissionProvider
-   activeGudangProvider
-   routeGuardProvider

Jika pakai BLoC:

-   AuthCubit menyimpan AuthUser + PermissionSet
-   GuardHelper stateless dipanggil dari UI dan service
