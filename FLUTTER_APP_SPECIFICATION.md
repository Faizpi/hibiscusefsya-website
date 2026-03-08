# 📱 Hibiscus Efsya POS — Spesifikasi Ultra-Lengkap Aplikasi Flutter

> **Dokumen ini adalah blueprint ultra-lengkap untuk membangun aplikasi Flutter yang 100% sinkron dengan website POS Hibiscus Efsya.**
> Semua menu, fitur, halaman, role, alur, form create & edit, validasi, API request/response, termasuk print Bluetooth, email invoice, public invoice, dan lainnya didokumentasikan secara detail.

---

## 📋 Daftar Isi

1. [Informasi Umum](#1-informasi-umum)
2. [Arsitektur & Teknologi](#2-arsitektur--teknologi)
3. [Tema & Design System (Biru Gradasi Pink)](#3-tema--design-system-biru-gradasi-pink)
4. [Autentikasi & Role System](#4-autentikasi--role-system)
5. [Struktur Menu per Role](#5-struktur-menu-per-role)
6. [Halaman: Login](#6-halaman-login)
7. [Halaman: Dashboard](#7-halaman-dashboard)
8. [Halaman: Profil & Pengaturan](#8-halaman-profil--pengaturan)
9. [Modul: Penjualan (Sales Invoice)](#9-modul-penjualan-sales-invoice)
10. [Modul: Pembelian (Purchase Request)](#10-modul-pembelian-purchase-request)
11. [Modul: Biaya (Expense Management)](#11-modul-biaya-expense-management)
12. [Modul: Kunjungan (Field Visit)](#12-modul-kunjungan-field-visit)
13. [Modul: Pembayaran (Payment)](#13-modul-pembayaran-payment)
14. [Modul: Penerimaan Barang (Goods Receipt)](#14-modul-penerimaan-barang-goods-receipt)
15. [Modul: Kontak (Customer/Supplier)](#15-modul-kontak-customersupplier)
16. [Modul: Produk (Product Master)](#16-modul-produk-product-master)
17. [Modul: Stok Gudang (Inventory)](#17-modul-stok-gudang-inventory)
18. [Modul: Gudang (Warehouse)](#18-modul-gudang-warehouse)
19. [Modul: Manajemen User](#19-modul-manajemen-user)
20. [Fitur: Print Bluetooth (ESC/POS)](#20-fitur-print-bluetooth-escpos)
21. [Fitur: Print Struk / Cetak PDF](#21-fitur-print-struk--cetak-pdf)
22. [Fitur: Public Invoice & QR Code](#22-fitur-public-invoice--qr-code)
23. [Fitur: Email Invoice & Notifikasi](#23-fitur-email-invoice--notifikasi)
24. [Fitur: Barcode & QR Scanner](#24-fitur-barcode--qr-scanner)
25. [Fitur: GPS / Lokasi](#25-fitur-gps--lokasi)
26. [Fitur: Export Excel](#26-fitur-export-excel)
27. [Fitur: Notifikasi & Approval](#27-fitur-notifikasi--approval)
28. [Fitur: Switch Gudang](#28-fitur-switch-gudang)
29. [Fitur: Upload Lampiran](#29-fitur-upload-lampiran)
30. [API Endpoints Lengkap](#30-api-endpoints-lengkap)
31. [Skema Database / Model](#31-skema-database--model)
32. [Navigasi & Flow Diagram](#32-navigasi--flow-diagram)

---

## 1. Informasi Umum

| Item               | Detail                                                        |
| ------------------ | ------------------------------------------------------------- |
| **Nama Aplikasi**  | Hibiscus Efsya POS                                            |
| **Platform**       | Android (Flutter)                                             |
| **Backend**        | Laravel 7 REST API                                            |
| **Base URL API**   | `https://sales.hibiscusefsya.com/api/v1`                      |
| **Autentikasi**    | Bearer Token (Personal Access Token, SHA256, expires 30 hari) |
| **Format Data**    | JSON                                                          |
| **Bahasa UI**      | Bahasa Indonesia                                              |
| **Target Min SDK** | Android 6.0 (API 23)                                          |

---

## 2. Arsitektur & Teknologi

### Stack Flutter yang Direkomendasikan

| Kategori             | Package                               |
| -------------------- | ------------------------------------- |
| State Management     | `flutter_riverpod` atau `bloc`        |
| HTTP Client          | `dio`                                 |
| Local Storage        | `shared_preferences` + `sqflite`      |
| Navigation           | `go_router`                           |
| Bluetooth Print      | `esc_pos_bluetooth` + `esc_pos_utils` |
| PDF Generation       | `pdf` + `printing`                    |
| QR/Barcode Scan      | `mobile_scanner`                      |
| QR Generation        | `qr_flutter`                          |
| Location/GPS         | `geolocator` + `geocoding`            |
| File Picker          | `file_picker`                         |
| Image Picker         | `image_picker`                        |
| Charts               | `fl_chart`                            |
| Excel Export         | `excel`                               |
| URL Launcher         | `url_launcher`                        |
| Permission           | `permission_handler`                  |
| Secure Storage       | `flutter_secure_storage`              |
| Intl/Format          | `intl`                                |
| Cached Network Image | `cached_network_image`                |
| Pull to Refresh      | `pull_to_refresh`                     |
| Shimmer Loading      | `shimmer`                             |
| Push Notification    | `firebase_messaging`                  |

### Arsitektur Folder

```
lib/
├── main.dart
├── app/
│   ├── app.dart
│   ├── routes.dart
│   └── theme.dart
├── core/
│   ├── constants/
│   │   ├── api_endpoints.dart
│   │   ├── app_colors.dart
│   │   ├── app_gradients.dart
│   │   ├── app_strings.dart
│   │   └── enums.dart
│   ├── network/
│   │   ├── api_client.dart
│   │   ├── api_interceptor.dart
│   │   └── api_exceptions.dart
│   ├── utils/
│   │   ├── currency_formatter.dart
│   │   ├── date_formatter.dart
│   │   ├── validators.dart
│   │   └── bluetooth_printer.dart
│   └── services/
│       ├── auth_service.dart
│       ├── location_service.dart
│       ├── printer_service.dart
│       └── storage_service.dart
├── data/
│   ├── models/
│   │   ├── user.dart
│   │   ├── gudang.dart
│   │   ├── produk.dart
│   │   ├── kontak.dart
│   │   ├── penjualan.dart
│   │   ├── pembelian.dart
│   │   ├── biaya.dart
│   │   ├── kunjungan.dart
│   │   ├── pembayaran.dart
│   │   ├── penerimaan_barang.dart
│   │   ├── stok.dart
│   │   └── dashboard_stats.dart
│   └── repositories/
│       ├── auth_repository.dart
│       ├── dashboard_repository.dart
│       ├── penjualan_repository.dart
│       └── ... (per modul)
├── presentation/
│   ├── widgets/
│   │   ├── app_drawer.dart
│   │   ├── gradient_app_bar.dart
│   │   ├── gradient_button.dart
│   │   ├── status_badge.dart
│   │   ├── summary_card.dart
│   │   ├── product_row.dart
│   │   ├── empty_state.dart
│   │   ├── loading_shimmer.dart
│   │   └── confirmation_dialog.dart
│   └── screens/
│       ├── auth/
│       │   └── login_screen.dart
│       ├── dashboard/
│       │   └── dashboard_screen.dart
│       ├── profile/
│       │   ├── profile_screen.dart
│       │   └── change_password_screen.dart
│       ├── penjualan/
│       │   ├── penjualan_list_screen.dart
│       │   ├── penjualan_create_screen.dart
│       │   ├── penjualan_edit_screen.dart
│       │   ├── penjualan_detail_screen.dart
│       │   └── penjualan_print_screen.dart
│       ├── pembelian/
│       │   ├── pembelian_list_screen.dart
│       │   ├── pembelian_create_screen.dart
│       │   ├── pembelian_edit_screen.dart
│       │   └── pembelian_detail_screen.dart
│       ├── biaya/
│       │   ├── biaya_list_screen.dart
│       │   ├── biaya_create_screen.dart
│       │   ├── biaya_edit_screen.dart
│       │   └── biaya_detail_screen.dart
│       ├── kunjungan/
│       │   ├── kunjungan_list_screen.dart
│       │   ├── kunjungan_create_screen.dart
│       │   ├── kunjungan_edit_screen.dart
│       │   └── kunjungan_detail_screen.dart
│       ├── pembayaran/
│       │   ├── pembayaran_list_screen.dart
│       │   ├── pembayaran_create_screen.dart
│       │   └── pembayaran_detail_screen.dart
│       ├── penerimaan_barang/
│       │   ├── penerimaan_list_screen.dart
│       │   ├── penerimaan_create_screen.dart
│       │   └── penerimaan_detail_screen.dart
│       ├── kontak/
│       │   ├── kontak_list_screen.dart
│       │   ├── kontak_create_screen.dart
│       │   ├── kontak_edit_screen.dart
│       │   └── kontak_detail_screen.dart
│       ├── produk/
│       │   ├── produk_list_screen.dart
│       │   ├── produk_create_screen.dart
│       │   ├── produk_edit_screen.dart
│       │   └── produk_detail_screen.dart
│       ├── stok/
│       │   ├── stok_screen.dart
│       │   └── stok_log_screen.dart
│       ├── gudang/
│       │   ├── gudang_list_screen.dart
│       │   ├── gudang_create_screen.dart
│       │   └── gudang_edit_screen.dart
│       ├── users/
│       │   ├── user_list_screen.dart
│       │   ├── user_create_screen.dart
│       │   └── user_edit_screen.dart
│       └── settings/
│           └── settings_screen.dart
└── assets/
    ├── images/
    │   └── logo.png
    └── fonts/
        └── PlusJakartaSans/
```

---

## 3. Tema & Design System (Biru Gradasi Pink)

### Palet Warna Utama

Tema menggunakan **gradasi biru ke pink** (sesuai gambar referensi) untuk elemen-elemen utama seperti AppBar, Drawer header, Login background, tombol utama, dan accent.

```dart
class AppColors {
  // ═══════════════════════════════════════
  // GRADIENT BIRU → PINK (Warna Utama)
  // ═══════════════════════════════════════
  static const Color gradientBlueStart   = Color(0xFF6366F1); // Indigo/Blue (kiri atas)
  static const Color gradientBlueMid     = Color(0xFF8B5CF6); // Violet/Purple (tengah)
  static const Color gradientPinkMid     = Color(0xFFC084FC); // Light Purple
  static const Color gradientPinkEnd     = Color(0xFFEC4899); // Hot Pink (kanan bawah)
  static const Color gradientPinkLight   = Color(0xFFF472B6); // Soft Pink

  // ═══════════════════════════════════════
  // PRIMARY (untuk button, link, active state)
  // ═══════════════════════════════════════
  static const Color primary       = Color(0xFF7C3AED); // Violet-600 (titik tengah gradient)
  static const Color primaryDark   = Color(0xFF6D28D9); // Violet-700
  static const Color primaryDarker = Color(0xFF5B21B6); // Violet-800
  static const Color primaryLight  = Color(0xFFEDE9FE); // Violet-50
  static const Color primarySoft   = Color(0xFFDDD6FE); // Violet-200

  // ═══════════════════════════════════════
  // SECONDARY PINK (accent)
  // ═══════════════════════════════════════
  static const Color accent        = Color(0xFFEC4899); // Pink-500
  static const Color accentDark    = Color(0xFFDB2777); // Pink-600
  static const Color accentLight   = Color(0xFFFDF2F8); // Pink-50
  static const Color accentSoft    = Color(0xFFFBCFE8); // Pink-200

  // ═══════════════════════════════════════
  // NEUTRAL
  // ═══════════════════════════════════════
  static const Color textPrimary   = Color(0xFF1F2937); // Gray-800  (teks utama)
  static const Color textSecondary = Color(0xFF6B7280); // Gray-500  (teks sekunder)
  static const Color textMuted     = Color(0xFF9CA3AF); // Gray-400  (teks muted)
  static const Color borderColor   = Color(0xFFE5E7EB); // Gray-200  (border)
  static const Color bgLight       = Color(0xFFF9FAFB); // Gray-50   (background)
  static const Color bgCard        = Color(0xFFFFFFFF); // White     (card background)
  static const Color white         = Color(0xFFFFFFFF);

  // ═══════════════════════════════════════
  // STATUS COLORS
  // ═══════════════════════════════════════
  static const Color success   = Color(0xFF10B981); // Emerald-500
  static const Color warning   = Color(0xFFF59E0B); // Amber-500
  static const Color danger    = Color(0xFFEF4444); // Red-500
  static const Color info      = Color(0xFF3B82F6); // Blue-500

  // ═══════════════════════════════════════
  // TRANSACTION STATUS
  // ═══════════════════════════════════════
  static const Color approved  = Color(0xFF10B981); // Green
  static const Color pending   = Color(0xFFF59E0B); // Yellow
  static const Color lunas     = Color(0xFF10B981); // Green
  static const Color canceled  = Color(0xFF6B7280); // Gray
  static const Color telat     = Color(0xFFEF4444); // Red
}
```

### Gradient Definitions

```dart
class AppGradients {
  // Gradient utama (untuk AppBar, Drawer header, Login bg, splash screen)
  static const LinearGradient primary = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xFF6366F1), // Indigo blue
      Color(0xFF8B5CF6), // Violet
      Color(0xFFC084FC), // Light purple
      Color(0xFFEC4899), // Hot pink
    ],
    stops: [0.0, 0.35, 0.65, 1.0],
  );

  // Gradient untuk tombol utama
  static const LinearGradient button = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xFF7C3AED), // Violet
      Color(0xFFEC4899), // Pink
    ],
  );

  // Gradient lembut untuk summary cards
  static const LinearGradient cardSoft = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xFFEDE9FE), // Violet-50
      Color(0xFFFDF2F8), // Pink-50
    ],
  );

  // Gradient untuk sidebar/drawer active item
  static const LinearGradient sidebarActive = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xFFEDE9FE), // Violet-50
      Color(0xFFFCE7F3), // Pink-50
    ],
  );
}
```

### Penggunaan Gradient di Komponen

| Komponen             | Gradient                     | Keterangan                             |
| -------------------- | ---------------------------- | -------------------------------------- |
| **Login Background** | `AppGradients.primary`       | Full screen gradient                   |
| **AppBar**           | `AppGradients.primary`       | Gradient horizontal biru→pink          |
| **Drawer Header**    | `AppGradients.primary`       | Background header drawer               |
| **Splash Screen**    | `AppGradients.primary`       | Logo di tengah gradient                |
| **FAB**              | `AppGradients.button`        | Tombol floating biru→pink              |
| **Tombol Utama**     | `AppGradients.button`        | Tombol Simpan, Masuk, dll              |
| **Summary Cards**    | `AppGradients.cardSoft`      | Background card di dashboard           |
| **Drawer Active**    | `AppGradients.sidebarActive` | Background menu aktif                  |
| **Status Bar**       | `Color(0xFF6366F1)`          | Warna status bar (ujung kiri gradient) |

### Tipografi

```dart
// Font utama: Plus Jakarta Sans (sama dengan website)
// Import via google_fonts package atau bundle di assets

TextTheme appTextTheme = TextTheme(
  headlineLarge:  TextStyle(fontWeight: FontWeight.w700, fontSize: 24),
  headlineMedium: TextStyle(fontWeight: FontWeight.w700, fontSize: 20),
  headlineSmall:  TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
  titleLarge:     TextStyle(fontWeight: FontWeight.w600, fontSize: 16),
  titleMedium:    TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
  titleSmall:     TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
  bodyLarge:      TextStyle(fontWeight: FontWeight.w400, fontSize: 16),
  bodyMedium:     TextStyle(fontWeight: FontWeight.w400, fontSize: 14),
  bodySmall:      TextStyle(fontWeight: FontWeight.w400, fontSize: 12),
  labelLarge:     TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
  labelMedium:    TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
  labelSmall:     TextStyle(fontWeight: FontWeight.w500, fontSize: 10),
);
```

### Komponen UI Standar

| Komponen                | Spesifikasi                                                             |
| ----------------------- | ----------------------------------------------------------------------- |
| **Card**                | `borderRadius: 12`, `border: 1px #E5E7EB`, no shadow, bg white          |
| **Button Primary**      | `gradient: biru→pink`, `borderRadius: 8`, `fontWeight: 600`, text white |
| **Button Secondary**    | `bg: transparent`, `border: #7C3AED`, text `#7C3AED`                    |
| **Button Danger**       | `bg: #EF4444`, text white                                               |
| **Button Success**      | `bg: #10B981`, text white                                               |
| **Input Field**         | `borderRadius: 8`, `border: #E5E7EB`, focus border: `#7C3AED`           |
| **Badge Status**        | Rounded pill, sesuai warna status                                       |
| **AppBar**              | `gradient: biru→pink`, `elevation: 0`, title: white, icons: white       |
| **Bottom Nav**          | Tidak digunakan — pakai **Drawer/Sidebar**                              |
| **Drawer**              | White bg, header gradient biru→pink, active item gradient lembut        |
| **FAB**                 | `gradient: biru→pink`, icon white                                       |
| **Tab / Chip Active**   | `bg: #7C3AED`, text white                                               |
| **Tab / Chip Inactive** | `bg: #F3F4F6`, text `#6B7280`                                           |

### Status Badge Mapping

| Status   | Warna Background | Warna Text | Label             |
| -------- | ---------------- | ---------- | ----------------- |
| Approved | `#D1FAE5`        | `#059669`  | Disetujui         |
| Pending  | `#FEF3C7`        | `#D97706`  | Menunggu          |
| Lunas    | `#D1FAE5`        | `#059669`  | Lunas             |
| Canceled | `#F3F4F6`        | `#6B7280`  | Dibatalkan        |
| Telat    | `#FEE2E2`        | `#DC2626`  | Jatuh Tempo Lewat |
| Masuk    | `#D1FAE5`        | `#059669`  | Biaya Masuk       |
| Keluar   | `#FEE2E2`        | `#DC2626`  | Biaya Keluar      |

### Contoh Widget Gradient Button

```dart
class GradientButton extends StatelessWidget {
  final String text;
  final VoidCallback onPressed;
  final bool isLoading;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: AppGradients.button,
        borderRadius: BorderRadius.circular(8),
      ),
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          padding: EdgeInsets.symmetric(vertical: 14, horizontal: 24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
        child: isLoading
          ? SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
          : Text(text, style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14)),
      ),
    );
  }
}
```

### Contoh Gradient AppBar

```dart
AppBar(
  flexibleSpace: Container(
    decoration: BoxDecoration(gradient: AppGradients.primary),
  ),
  title: Text('Penjualan', style: TextStyle(color: Colors.white)),
  iconTheme: IconThemeData(color: Colors.white),
  elevation: 0,
)
```

---

## 4. Autentikasi & Role System

### Alur Login

```
┌─────────────┐     POST /api/v1/login      ┌──────────────┐
│ Login Screen │ ─── {email, password} ────▶ │   Backend    │
│              │                              │              │
│  [Email]     │ ◀── {token, user, gudang} ─ │  Return JWT  │
│  [Password]  │                              │  + user data │
│  [Masuk]     │                              └──────────────┘
└─────────────┘
        │
        ▼ Simpan token ke flutter_secure_storage
        │
        ▼ Navigate ke Dashboard
```

### 4 Role User

| Role             | Kode          | Akses                      | Deskripsi                                                                  |
| ---------------- | ------------- | -------------------------- | -------------------------------------------------------------------------- |
| **Super Admin**  | `super_admin` | **Semua fitur**            | Mengelola seluruh sistem, semua gudang, semua user. Transaksi auto-approve |
| **Admin**        | `admin`       | **Gudang yang ditugaskan** | Approve transaksi, kelola gudang sendiri. Multi-gudang lewat pivot         |
| **Spectator**    | `spectator`   | **Read-only**              | Lihat semua data di gudang yang ditugaskan, tidak bisa create/edit/approve |
| **User (Sales)** | `user`        | **Transaksi sendiri**      | Buat transaksi, lihat milik sendiri. Single gudang                         |

### Hak Akses Detail per Role

| Fitur                 |     Super Admin     |       Admin       |     Spectator     |       User       |
| --------------------- | :-----------------: | :---------------: | :---------------: | :--------------: |
| Dashboard             |   ✅ Semua gudang   | ✅ Gudang sendiri | ✅ Gudang sendiri | ✅ Data sendiri  |
| **Penjualan**         |                     |                   |                   |                  |
| — Lihat               |      ✅ Semua       | ✅ Gudang sendiri | ✅ Gudang sendiri | ✅ Milik sendiri |
| — Buat                |  ✅ (auto-approve)  |        ✅         |        ❌         |        ✅        |
| — Edit                |         ✅          |        ❌         |        ❌         |        ❌        |
| — Approve             |         ✅          |        ✅         |        ❌         |        ❌        |
| — Cancel              |  ✅ (semua status)  | ✅ (pending saja) |        ❌         |        ❌        |
| — Uncancel            |         ✅          |        ❌         |        ❌         |        ❌        |
| — Mark Lunas          |         ✅          |        ✅         |        ❌         |        ❌        |
| **Pembelian**         |                     |                   |                   |                  |
| — Lihat               |         ✅          |        ✅         |        ✅         |        ✅        |
| — Buat                |  ✅ (auto-approve)  |        ✅         |        ❌         |        ✅        |
| — Edit                |         ✅          |        ❌         |        ❌         |        ❌        |
| — Approve/Cancel      |         ✅          |        ✅         |        ❌         |        ❌        |
| **Biaya**             |                     |                   |                   |                  |
| — Lihat               |         ✅          |        ✅         |        ✅         |        ✅        |
| — Buat                |  ✅ (auto-approve)  |        ✅         |        ❌         |        ✅        |
| — Edit                |         ✅          |        ❌         |        ❌         |        ❌        |
| — Approve/Cancel      |         ✅          |        ✅         |        ❌         |        ❌        |
| **Kunjungan**         |                     |                   |                   |                  |
| — Lihat               |         ✅          |        ✅         |        ✅         |        ✅        |
| — Buat                |  ✅ (auto-approve)  |        ✅         |        ❌         |        ✅        |
| — Edit                |         ✅          |        ❌         |        ❌         |        ❌        |
| — Approve/Cancel      |         ✅          |        ✅         |        ❌         |        ❌        |
| **Pembayaran**        |                     |                   |                   |                  |
| — Lihat               |         ✅          |        ✅         |        ✅         |        ✅        |
| — Buat                |         ✅          |        ✅         |        ❌         |        ✅        |
| — Edit                | ❌ (tidak ada edit) |        ❌         |        ❌         |        ❌        |
| — Approve/Cancel      |         ✅          |        ✅         |        ❌         |        ❌        |
| **Penerimaan Barang** |                     |                   |                   |                  |
| — Lihat               |         ✅          |        ✅         |        ✅         |        ✅        |
| — Buat                |         ✅          |        ✅         |        ❌         |        ✅        |
| — Edit                | ❌ (tidak ada edit) |        ❌         |        ❌         |        ❌        |
| — Approve/Cancel      |         ✅          |        ✅         |        ❌         |        ❌        |
| **Master Data**       |                     |                   |                   |                  |
| Kontak - Lihat        |         ✅          |        ✅         |        ✅         |        ✅        |
| Kontak - Create       |         ✅          |        ✅         |        ❌         |        ❌        |
| Kontak - Edit (full)  |         ✅          |        ❌         |        ❌         |        ❌        |
| Kontak - Edit PIN     |         ✅          |        ✅         |        ❌         |        ✅        |
| Kontak - Delete       |         ✅          |        ❌         |        ❌         |        ❌        |
| Produk - CRUD         |         ✅          |        ❌         |        ❌         |        ❌        |
| Produk - Lihat        |         ✅          |        ✅         |        ✅         |        ✅        |
| Gudang - CRUD         |         ✅          |        ❌         |        ❌         |        ❌        |
| Stok - Manual Update  |         ✅          |        ❌         |        ❌         |        ❌        |
| Stok - Lihat          |         ✅          |        ✅         |        ✅         |        ✅        |
| Stok - Export Excel   |         ✅          |        ✅         |        ✅         |        ✅        |
| **User & System**     |                     |                   |                   |                  |
| User Management       |         ✅          |        ❌         |        ❌         |        ❌        |
| Switch Gudang         |     ✅ (semua)      |  ✅ (ditugaskan)  |  ✅ (ditugaskan)  |        ❌        |
| Print Bluetooth       |         ✅          |        ✅         |        ✅         |        ✅        |
| Email Invoice         |         ✅          |        ✅         |        ❌         |        ❌        |
| Share Public Invoice  |         ✅          |        ✅         |        ✅         |        ✅        |

---

## 5. Struktur Menu per Role

### Layout Aplikasi

```
┌──────────────────────────────────────┐
│ ╔══ Gradient AppBar (Biru→Pink) ══╗  │
│ ║ [☰] Logo             [🔔] [👤] ║  │
│ ╚═════════════════════════════════╝  │
├──────────────────────────────────────┤
│                                      │
│         Content Area                 │
│         (bg: #F9FAFB)               │
│                                      │
│                                      │
│                          [+FAB]      │
│                     (gradient btn)   │
└──────────────────────────────────────┘
```

### Drawer Menu (Super Admin)

```
┌────────────────────────────────┐
│ ╔══ Gradient Header ═════════╗ │
│ ║ [Logo]                 [X] ║ │
│ ║ Admin Name                 ║ │
│ ║ admin@email.com            ║ │
│ ╚════════════════════════════╝ │
│                                │
│  MENU UTAMA                    │
│  🏠 Dashboard                  │
│  ─────────────────────────     │
│  TRANSAKSI                     │
│  📝 Penjualan                  │
│  🛒 Pembelian                  │
│  💰 Biaya                      │
│  🚗 Kunjungan                  │
│  💳 Pembayaran                 │
│  📦 Penerimaan Barang          │
│  ─────────────────────────     │
│  MASTER DATA                   │
│  👥 Kontak                     │
│  📊 Stok Gudang                │
│  ─────────────────────────     │
│  PENGATURAN                    │
│  🏭 Produk                     │
│  🏢 Gudang                     │
│  👤 Manajemen User             │
│  ─────────────────────────     │
│  ⚙️ Pengaturan / Profil        │
│  🚪 Keluar                     │
└────────────────────────────────┘
```

### Drawer Menu (Admin) — Tanpa: Produk, Gudang, Manajemen User

### Drawer Menu (Spectator) — Sama seperti Admin, **tanpa FAB / tombol Create/Edit/Approve**

### Drawer Menu (User/Sales) — Tanpa: Kontak, Stok, Produk, Gudang, User

---

## 6. Halaman: Login

### Layout

```
┌──────────────────────────────────────┐
│ ╔══ Full Screen Gradient ═══════╗    │
│ ║      (Biru → Pink)            ║    │
│ ║                               ║    │
│ ║        [Logo Image]           ║    │
│ ║      Hibiscus Efsya           ║    │
│ ║   Point of Sales System       ║    │
│ ║                               ║    │
│ ║  ┌─── Card (White) ───────┐  ║    │
│ ║  │ 📧 Email               │  ║    │
│ ║  │ ┌────────────────────┐  │  ║    │
│ ║  │ │                    │  │  ║    │
│ ║  │ └────────────────────┘  │  ║    │
│ ║  │ 🔒 Password          👁 │  ║    │
│ ║  │ ┌────────────────────┐  │  ║    │
│ ║  │ │                    │  │  ║    │
│ ║  │ └────────────────────┘  │  ║    │
│ ║  │                         │  ║    │
│ ║  │ ┌════════════════════┐  │  ║    │
│ ║  │ ║  MASUK (gradient)  ║  │  ║    │
│ ║  │ └════════════════════┘  │  ║    │
│ ║  └─────────────────────────┘  ║    │
│ ║                               ║    │
│ ║   © 2026 Hibiscus Efsya      ║    │
│ ╚═══════════════════════════════╝    │
└──────────────────────────────────────┘
```

### Field Login

| Field        | Widget           | Tipe Input                   | Validasi              | Keterangan                                        |
| ------------ | ---------------- | ---------------------------- | --------------------- | ------------------------------------------------- |
| Email        | `TextFormField`  | `TextInputType.emailAddress` | Required, valid email | Prefix icon: `Icons.email_outlined`               |
| Password     | `TextFormField`  | `obscureText: true`          | Required, min 6 char  | Prefix: `Icons.lock_outlined`, Suffix: eye toggle |
| Tombol Masuk | `GradientButton` | —                            | —                     | Gradient biru→pink, loading state                 |

### API Call

```
POST /api/v1/login
Content-Type: application/json

Request Body:
{
  "email": "admin@hibiscusefsya.com",
  "password": "password123"
}

Response Success (200):
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "1|abc123def456...",
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@hibiscusefsya.com",
      "role": "super_admin",
      "no_telp": "6281234567890",
      "alamat": "Jl. Merdeka 123",
      "gudang_id": null,
      "current_gudang_id": null
    },
    "gudang": {
      "id": 1,
      "nama_gudang": "Gudang Utama",
      "alamat_gudang": "Jl. Industri 45"
    }
  }
}

Response Error (401):
{
  "success": false,
  "message": "Email atau password salah"
}
```

### Setelah Login

1. Simpan `token` ke `flutter_secure_storage`
2. Simpan `user` data ke `shared_preferences` (JSON)
3. Simpan `gudang` aktif ke `shared_preferences`
4. Navigate ke Dashboard (replace stack)

---

## 7. Halaman: Dashboard

### Layout

```
┌──────────────────────────────────────┐
│ ╔══ Gradient AppBar ═══════════════╗ │
│ ║ [☰] Dashboard          [🔔] [👤]║ │
│ ╚══════════════════════════════════╝ │
├──────────────────────────────────────┤
│  ┌─── Gradient Card ────┐ ┌────────┐│
│  │ 📊 Penjualan         │ │Pembelian││
│  │ Rp 15.000.000        │ │Rp 8jt  ││
│  │ bulan ini             │ │        ││
│  └───────────────────────┘ └────────┘│
│  ┌──────────┐ ┌──────────┐          │
│  │Kunjungan │ │ Canceled │          │
│  │  25 kali │ │    3     │          │
│  └──────────┘ └──────────┘          │
│  ┌──────────┐ ┌──────────┐          │
│  │Biaya     │ │Biaya     │          │
│  │Masuk     │ │Keluar    │          │
│  │Rp 5jt   │ │Rp 3jt   │          │
│  └──────────┘ └──────────┘          │
│  ┌──────────┐ ┌──────────┐          │
│  │Total     │ │Pending   │          │
│  │Produk:150│ │Approval:8│          │
│  └──────────┘ └──────────┘          │
│                                      │
│  📊 Grafik Penjualan Bulanan        │
│  ┌──────────────────────────────┐    │
│  │  [Bar/Line Chart]            │    │
│  │  (gradient fill biru→pink)   │    │
│  └──────────────────────────────┘    │
└──────────────────────────────────────┘
```

### Summary Cards (8 kartu, grid 2 kolom)

| #   | Label               | Tipe Value | Icon | Sumber Data                                  |
| --- | ------------------- | ---------- | ---- | -------------------------------------------- |
| 1   | Penjualan Bulan Ini | Rp format  | 📊   | Total grand_total penjualan (approved+lunas) |
| 2   | Pembelian Bulan Ini | Rp format  | 🛒   | Total grand_total pembelian (approved)       |
| 3   | Kunjungan Bulan Ini | Count      | 🚗   | Count kunjungan bulan ini                    |
| 4   | Canceled            | Count      | ❌   | Count semua transaksi canceled               |
| 5   | Biaya Masuk         | Rp format  | ⬇️   | Total biaya jenis=masuk                      |
| 6   | Biaya Keluar        | Rp format  | ⬆️   | Total biaya jenis=keluar                     |
| 7   | Total Produk        | Count      | 📦   | Count produk                                 |
| 8   | Pending Approval    | Count      | ⏳   | Count transaksi pending (admin/super)        |

### API Call

```
GET /api/v1/dashboard
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "penjualan_total": 15000000,
    "pembelian_total": 8000000,
    "kunjungan_count": 25,
    "canceled_count": 3,
    "biaya_masuk_total": 5000000,
    "biaya_keluar_total": 3000000,
    "produk_count": 150,
    "pending_count": 8,
    "chart_data": {
      "labels": ["Jan","Feb","Mar",...],
      "values": [12000000, 15000000, 18000000,...]
    }
  }
}
```

---

## 8. Halaman: Profil & Pengaturan

### 8.1 Halaman Profil

**Layout:**

```
│  ╔══ Gradient Header ══════╗         │
│  ║   👤 Avatar (initial)   ║         │
│  ║   Nama User             ║         │
│  ║   admin@email.com       ║         │
│  ║   [Super Admin]         ║         │
│  ╚═════════════════════════╝         │
│                                      │
│  ── Informasi Akun ──                │
│  Nama:   Super Admin                 │
│  Email:  admin@mail.com (readonly)   │
│  Telp:   6281234567890               │
│  Alamat: Jl. Merdeka 123            │
│  Role:   Super Admin (readonly)      │
│  Gudang: Gudang Utama (readonly)     │
│                                      │
│  [✏️ Edit Profil]                     │
│  [🔒 Ubah Password]                  │
│  [🚪 Logout]                         │
```

### 8.2 Form Edit Profil

| Field    | Widget          | Tipe      | Validasi          | Keterangan        |
| -------- | --------------- | --------- | ----------------- | ----------------- |
| Nama     | `TextFormField` | text      | Required, max 255 | Editable          |
| Email    | Display only    | —         | —                 | Tidak bisa diubah |
| No. Telp | `TextFormField` | phone     | Optional, max 20  | Format 628xxx     |
| Alamat   | `TextFormField` | multiline | Optional          | Textarea          |

**API Call:**

```
PUT /api/v1/profile
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "name": "Nama Baru",
  "no_telp": "6281234567890",
  "alamat": "Jl. Baru 456"
}

Response:
{
  "success": true,
  "message": "Profil berhasil diupdate",
  "data": { ...user object... }
}
```

### 8.3 Form Ubah Password

| Field               | Widget          | Tipe    | Validasi             |
| ------------------- | --------------- | ------- | -------------------- |
| Password Saat Ini   | `TextFormField` | obscure | Required             |
| Password Baru       | `TextFormField` | obscure | Required, min 8      |
| Konfirmasi Password | `TextFormField` | obscure | Required, must match |

**API Call:**

```
POST /api/v1/change-password
Authorization: Bearer {token}

Request Body:
{
  "current_password": "oldpass123",
  "new_password": "newpass456",
  "new_password_confirmation": "newpass456"
}
```

---

## 9. Modul: Penjualan (Sales Invoice)

### Format Nomor: `INV-YYYYMMDD-USERID-NOURUT`

### 9.1 List Penjualan

**Layout:**

```
┌──────────────────────────────────────┐
│ ╔══ AppBar: "Penjualan" ══[🔍][📊]╗ │
│ ╚══════════════════════════════════╝ │
│  ┌────────┐ ┌────────┐ ┌────────┐   │
│  │Pending │ │Approved│ │ Lunas  │   │
│  │  12    │ │  45    │ │  30    │   │
│  └────────┘ └────────┘ └────────┘   │
│  ┌────────┐ ┌────────┐              │
│  │Telat   │ │Canceled│              │
│  │  3     │ │  5     │              │
│  └────────┘ └────────┘              │
│  ──── Filter ────────────────────   │
│  [Sales ▼] (admin/super only)       │
│  [Status ▼] [🔍 Search...]          │
│  ──────────────────────────────────  │
│  ┌──────────────────────────────┐    │
│  │ INV-20260307-001-01          │    │
│  │ Toko ABC  •  Rp 5.250.000   │    │
│  │ 07 Mar 2026  [Approved ✓]   │    │
│  │ Sales: Budi  •  Net 30      │    │
│  └──────────────────────────────┘    │
│  ┌──────────────────────────────┐    │
│  │ INV-20260306-001-02          │    │
│  │ PT XYZ   •  Rp 12.000.000   │    │
│  │ 06 Mar 2026  [Pending ⏳]    │    │
│  │ Sales: Andi  [🔴TELAT]       │    │
│  └──────────────────────────────┘    │
│                                      │
│  ... (Pull to refresh + Infinite     │
│       scroll pagination, 15/page)    │
│                            [+FAB]    │
└──────────────────────────────────────┘
```

**Summary Cards (5):**

-   Pending (count)
-   Approved (count)
-   Lunas (count)
-   Jatuh Tempo Lewat / Telat (count)
-   Canceled (count)

**Filter:**

-   Dropdown Sales (admin/super: list semua user di gudang)
-   Dropdown Status (Semua/Pending/Approved/Lunas/Canceled)
-   Search text (nomor, pelanggan)

**Setiap Card Item:**

-   Nomor Invoice (tap → detail)
-   Nama Pelanggan
-   Grand Total (format Rupiah)
-   Tanggal Transaksi
-   Status Badge
-   Indikator TELAT (merah, jika jatuh tempo lewat)
-   Nama Sales + Syarat Pembayaran

**API Call:**

```
GET /api/v1/penjualan?page=1&per_page=15&search=&status=&user_id=
Authorization: Bearer {token}
```

---

### 9.2 Create Penjualan

> **Akses:** super_admin (auto-approve), admin, user

**Layout (Scrollable Form):**

```
┌──────────────────────────────────────┐
│ ╔══ "Buat Penjualan"     [Simpan] ╗ │
│ ╚══════════════════════════════════╝ │
│                                      │
│  ═══ INFORMASI PELANGGAN ═══        │
│                                      │
│  Pelanggan *         [📷 Scan]       │
│  ┌──────────────────────────────┐    │
│  │ [Searchable Dropdown Kontak] │    │
│  └──────────────────────────────┘    │
│  Email                               │
│  ┌──────────────────────────────┐    │
│  │ (auto-fill dari kontak)      │    │
│  └──────────────────────────────┘    │
│  Alamat Penagihan                    │
│  ┌──────────────────────────────┐    │
│  │ (auto-fill dari kontak)      │    │
│  └──────────────────────────────┘    │
│                                      │
│  ═══ DETAIL TRANSAKSI ═══           │
│                                      │
│  No. Transaksi (readonly):           │
│  INV-20260308-1-01                   │
│                                      │
│  Tgl. Transaksi *        [📅]        │
│  ┌──────────────────────────────┐    │
│  │ 08/03/2026                   │    │
│  └──────────────────────────────┘    │
│  ⚠️ Readonly untuk role=user         │
│                                      │
│  Syarat Pembayaran *                 │
│  ┌──────────────────────────────┐    │
│  │ [Cash / Net 7 / 14 / 30 /60]│    │
│  └──────────────────────────────┘    │
│  Tgl. Jatuh Tempo (auto):           │
│  08/04/2026                          │
│                                      │
│  No. Referensi                       │
│  ┌──────────────────────────────┐    │
│  │ (opsional, referensi PO)     │    │
│  └──────────────────────────────┘    │
│                                      │
│  Gudang *                            │
│  ┌──────────────────────────────┐    │
│  │ [Dropdown] SA=semua, lain=   │    │
│  │  hidden (auto current gudang)│    │
│  └──────────────────────────────┘    │
│                                      │
│  Tipe Harga *                        │
│  ○ Retail   ● Grosir                 │
│  (mengubah harga_satuan otomatis)    │
│                                      │
│  📍 Koordinat: -6.2088, 106.8456    │
│  (auto GPS, readonly)                │
│                                      │
│  Tag: Budi (auto nama user, readonly)│
│                                      │
│  ═══ DAFTAR PRODUK ═══              │
│                                      │
│  ┌────────────────────────────────┐  │
│  │ Produk * [📷 Scan Barcode]     │  │
│  │ ┌────────────────────────────┐ │  │
│  │ │ [Searchable Dropdown]      │ │  │
│  │ └────────────────────────────┘ │  │
│  │ Deskripsi:                     │  │
│  │ ┌────────────────────────────┐ │  │
│  │ │ (opsional, varian/catatan) │ │  │
│  │ └────────────────────────────┘ │  │
│  │ Kuantitas *     Satuan (auto)  │  │
│  │ ┌──────┐       ┌──────────┐   │  │
│  │ │ [10] │       │ Pcs      │   │  │
│  │ └──────┘       └──────────┘   │  │
│  │ Harga Satuan *  Diskon (%)    │  │
│  │ ┌──────────┐   ┌──────┐      │  │
│  │ │ Rp 50.000│   │  5%  │      │  │
│  │ └──────────┘   └──────┘      │  │
│  │ ─────────────────────────     │  │
│  │ Jumlah Baris: Rp 475.000     │  │
│  │                          [🗑] │  │
│  └────────────────────────────────┘  │
│                                      │
│  ┌────────────────────────────────┐  │
│  │  [+ TAMBAH PRODUK]             │  │
│  └────────────────────────────────┘  │
│                                      │
│  ═══ CATATAN & LAMPIRAN ═══         │
│                                      │
│  Memo                                │
│  ┌──────────────────────────────┐    │
│  │ (opsional, catatan transaksi)│    │
│  └──────────────────────────────┘    │
│                                      │
│  📎 Lampiran                         │
│  [Pilih File] (jpg,png,pdf,doc,zip)  │
│  Max 2MB per file, multiple          │
│  📎 foto1.jpg [x]                    │
│  📎 bukti.pdf [x]                    │
│                                      │
│  ═══ RINGKASAN ═══                  │
│                                      │
│  Subtotal:        Rp 10.000.000      │
│                                      │
│  Diskon Akhir (Rp):                  │
│  ┌──────────────────────────────┐    │
│  │ Rp 500.000                   │    │
│  └──────────────────────────────┘    │
│                                      │
│  Pajak (%):                          │
│  ┌──────────────────────────────┐    │
│  │ 11                           │    │
│  └──────────────────────────────┘    │
│  Nominal Pajak:   Rp  1.045.000     │
│  ──────────────────────────────      │
│  GRAND TOTAL:     Rp 10.545.000     │
│                                      │
│  ┌════════════════════════════════┐  │
│  ║    SIMPAN PENJUALAN (gradient) ║  │
│  └════════════════════════════════┘  │
└──────────────────────────────────────┘
```

**Tabel Field Create Penjualan Lengkap:**

| #   | Field               | Widget                     | Tipe Input  | Validasi             | Default                | Keterangan                                   |
| --- | ------------------- | -------------------------- | ----------- | -------------------- | ---------------------- | -------------------------------------------- |
| 1   | `pelanggan`         | Searchable Dropdown + Scan | text/select | **Required**         | —                      | Dari data Kontak. Barcode scan icon          |
| 2   | `email`             | TextFormField              | email       | Optional             | Auto-fill dari kontak  | Bisa diedit manual                           |
| 3   | `alamat_penagihan`  | TextFormField              | multiline   | Optional             | Auto-fill dari kontak  | Textarea                                     |
| 4   | `tgl_transaksi`     | DatePicker                 | date        | **Required**         | Hari ini               | Readonly untuk role=user                     |
| 5   | `syarat_pembayaran` | Dropdown                   | select      | **Required**         | —                      | Cash, Net 7, Net 14, Net 30, Net 60          |
| 6   | `tgl_jatuh_tempo`   | Display only               | —           | Auto                 | `tgl_transaksi + hari` | Auto-hitung: Cash=0, Net7=7, dll             |
| 7   | `no_referensi`      | TextFormField              | text        | Optional             | —                      | Referensi eksternal                          |
| 8   | `gudang_id`         | Dropdown / Hidden          | select      | **Required**         | User's gudang          | SA=dropdown semua, lain=hidden auto          |
| 9   | `tipe_harga`        | RadioGroup                 | radio       | Optional             | `retail`               | `retail` / `grosir`. Mengubah harga otomatis |
| 10  | `koordinat`         | Display (readonly)         | text        | Optional             | Auto GPS               | `navigator.geolocation`                      |
| 11  | `tag`               | Display (readonly)         | text        | Optional             | `user.name`            | Otomatis nama login                          |
| 12  | `no_transaksi`      | Display (readonly)         | —           | —                    | Auto-generate          | Preview nomor                                |
| 13  | `produk_id[]`       | Searchable Dropdown + Scan | select      | **Required** (min 1) | —                      | Item baris, barcode scan                     |
| 14  | `deskripsi[]`       | TextFormField              | text        | Optional             | —                      | Per item, varian/catatan                     |
| 15  | `kuantitas[]`       | TextFormField              | number      | **Required**, min 1  | —                      | Validasi ≤ stok_penjualan                    |
| 16  | `unit[]`            | Display (readonly)         | text        | —                    | Dari produk            | Pcs/Lusin/Karton                             |
| 17  | `harga_satuan[]`    | TextFormField              | currency    | **Required**, min 0  | Dari produk            | Switch retail/grosir                         |
| 18  | `diskon[]`          | TextFormField              | number      | Optional, 0-100      | 0                      | Persentase per item                          |
| 19  | `memo`              | TextFormField              | multiline   | Optional             | —                      | Textarea                                     |
| 20  | `lampiran[]`        | FilePicker                 | file        | Optional             | —                      | jpg,jpeg,png,pdf,zip,doc,docx max 2MB        |
| 21  | `diskon_akhir`      | TextFormField              | currency    | Optional, min 0      | 0                      | Diskon total (Rupiah)                        |
| 22  | `tax_percentage`    | TextFormField              | number      | **Required**, min 0  | 0                      | Dalam persen                                 |

**Kalkulasi Realtime:**

```
Per Baris:   jumlah_baris = kuantitas × harga_satuan × (1 - diskon/100)
Subtotal:    sum(semua jumlah_baris)
After Disc:  subtotal - diskon_akhir
Tax Amount:  (subtotal - diskon_akhir) × (tax_percentage / 100)
Grand Total: (subtotal - diskon_akhir) + tax_amount
```

**API Request (POST):**

```
POST /api/v1/penjualan
Authorization: Bearer {token}
Content-Type: multipart/form-data

Fields:
  pelanggan: "Toko ABC"
  email: "abc@mail.com"
  alamat_penagihan: "Jl. Merdeka 123"
  tgl_transaksi: "2026-03-08"
  syarat_pembayaran: "Net 30"
  no_referensi: "PO-001"
  gudang_id: 1
  tipe_harga: "retail"
  koordinat: "-6.2088,106.8456"
  tag: "Budi"
  memo: "Catatan..."
  diskon_akhir: 500000
  tax_percentage: 11
  items[0][produk_id]: 5
  items[0][deskripsi]: "Varian A"
  items[0][kuantitas]: 10
  items[0][unit]: "Pcs"
  items[0][harga_satuan]: 50000
  items[0][diskon]: 5
  items[1][produk_id]: 8
  items[1][kuantitas]: 5
  items[1][harga_satuan]: 120000
  lampiran[]: (file binary)
  lampiran[]: (file binary)

Response Success (201):
{
  "success": true,
  "message": "Penjualan berhasil dibuat",
  "data": {
    "id": 123,
    "uuid": "a1b2c3d4-...",
    "nomor": "INV-20260308-1-01",
    "status": "Pending",  // atau "Approved" jika super_admin
    ...
  }
}
```

---

### 9.3 Edit Penjualan

> **Akses:** HANYA super_admin

**Perbedaan dengan Create:**

| Aspek           | Create                   | Edit                                  |
| --------------- | ------------------------ | ------------------------------------- |
| Akses           | super_admin, admin, user | **super_admin ONLY**                  |
| Barcode scan    | ✅ Ada                   | ❌ Tidak ada                          |
| tgl_jatuh_tempo | Display only             | **Hidden input (editable)**           |
| Lampiran        | Upload baru              | **Tampil existing + upload tambahan** |
| Pre-fill        | Kosong                   | **Semua field terisi dari data**      |
| Status          | Auto (Pending/Approved)  | **Tidak berubah**                     |

**Field Edit — Sama seperti Create plus:**

-   Tombol hapus lampiran existing (per file)
-   Nomor transaksi **readonly** (tidak bisa diubah)
-   Status ditampilkan tapi **readonly**

**API Request (PUT — via web only, API tidak support edit):**

```
PUT /api/v1/penjualan/{id}  ← Jika API support
Content-Type: multipart/form-data

Fields: sama seperti POST, dengan tambahan:
  _method: "PUT"
  lampiran_hapus[]: [id_lampiran_yang_dihapus]
```

---

### 9.4 Detail Penjualan

**Layout:**

```
┌──────────────────────────────────────┐
│ ╔══ "INV-20260308-1-01"   [⋮ Menu]╗ │
│ ║                                  ║ │
│ ║  ⋮ Menu Popup:                   ║ │
│ ║    🖨 Print Bluetooth             ║ │
│ ║    📄 Cetak Struk/PDF            ║ │
│ ║    📱 Tampilkan QR Code          ║ │
│ ║    📧 Kirim Email                ║ │
│ ║    🔗 Share Invoice              ║ │
│ ║    ✏️ Edit (super_admin)          ║ │
│ ╚══════════════════════════════════╝ │
│                                      │
│  ┌──────────────────────────────┐    │
│  │  Status: [Approved ✓]       │    │
│  │  GRAND TOTAL: Rp 10.545.000 │    │
│  └──────────────────────────────┘    │
│                                      │
│  ── INFORMASI UMUM ──                │
│  Pelanggan:        Toko ABC          │
│  Email:            abc@mail.com      │
│  Alamat:           Jl. Merdeka 123   │
│  Sales:            Budi              │
│  Gudang:           Gudang Utama      │
│  Tipe Harga:       Retail            │
│  Tgl Transaksi:    08/03/2026        │
│  Tgl Jatuh Tempo:  07/04/2026        │
│  Syarat:           Net 30            │
│  No. Referensi:    PO-001            │
│  Approver:         Admin             │
│  Koordinat:      -6.208,106.845 [🗺] │
│  Tag:              Budi              │
│  Dibuat:           08/03 14:30       │
│  Diupdate:         08/03 15:00       │
│                                      │
│  ── DAFTAR PRODUK ──                 │
│  ┌──────────────────────────────┐    │
│  │ 1. Produk A (PRD001)        │    │
│  │    10 Pcs × Rp 50.000       │    │
│  │    Disc 5%                   │    │
│  │    Subtotal: Rp 475.000     │    │
│  └──────────────────────────────┘    │
│  ┌──────────────────────────────┐    │
│  │ 2. Produk B (PRD002)        │    │
│  │    5 Box × Rp 120.000       │    │
│  │    Subtotal: Rp 600.000     │    │
│  └──────────────────────────────┘    │
│                                      │
│  ── RINGKASAN ──                     │
│  Subtotal:       Rp 1.075.000       │
│  Diskon:         Rp    50.000        │
│  Pajak 11%:      Rp   112.750       │
│  ─────────────────────               │
│  GRAND TOTAL:    Rp 1.137.750       │
│                                      │
│  ── LAMPIRAN ──                      │
│  📎 foto1.jpg  📎 bukti.pdf          │
│  (tap untuk preview/download)        │
│                                      │
│  ── MEMO ──                          │
│  "Kirim sebelum tanggal 15..."       │
│                                      │
│  ── ACTION BUTTONS ──                │
│  ┌════════════════┐ ┌═══════════┐    │
│  ║  ✅ Approve    ║ ║ 💰 Lunas  ║    │
│  └════════════════┘ └═══════════┘    │
│  ┌════════════════┐ ┌═══════════┐    │
│  ║  ❌ Cancel     ║ ║ ↩ Uncancel║    │
│  └════════════════┘ └═══════════┘    │
│  ┌════════════════┐ ┌═══════════┐    │
│  ║  🖨 Print BT  ║ ║ 📄 Struk  ║    │
│  └════════════════┘ └═══════════┘    │
│  ┌════════════════┐ ┌═══════════┐    │
│  ║  📱 QR Code   ║ ║ 📧 Email  ║    │
│  └════════════════┘ └═══════════┘    │
│  ┌══════════════════════════════┐    │
│  ║  🔗 Share Invoice            ║    │
│  └══════════════════════════════┘    │
└──────────────────────────────────────┘
```

**Action Buttons (Conditional per Role & Status):**

| Button         | Tampil Jika Status | Role yang Melihat  | Warna            |
| -------------- | ------------------ | ------------------ | ---------------- |
| ✅ Approve     | Pending            | admin, super_admin | Success (hijau)  |
| 💰 Mark Lunas  | Approved           | admin, super_admin | Success (hijau)  |
| ❌ Cancel      | Pending            | admin, super_admin | Danger (merah)   |
| ❌ Cancel      | Approved           | super_admin ONLY   | Danger           |
| ↩ Uncancel     | Canceled           | super_admin ONLY   | Warning (kuning) |
| ✏️ Edit        | Pending/Approved   | super_admin ONLY   | Gradient         |
| 🖨 Print BT     | Semua              | Semua role         | Outline          |
| 📄 Cetak Struk | Semua              | Semua role         | Outline          |
| 📱 QR Code     | Semua              | Semua role         | Outline          |
| 📧 Email       | Bukan Canceled     | admin, super_admin | Outline          |
| 🔗 Share       | Semua              | Semua role         | Outline          |

**API Calls:**

```
GET  /api/v1/penjualan/{id}         → Detail + items
POST /api/v1/penjualan/{id}/approve → Approve
POST /api/v1/penjualan/{id}/cancel  → Cancel
```

---

## 10. Modul: Pembelian (Purchase Request)

### Format Nomor: `PR-YYYYMMDD-USERID-NOURUT`

### 10.1 List Pembelian

Sama seperti Penjualan list. **Summary Cards:** Pending, Approved, Jatuh Tempo Lewat, Canceled.

**Filter:** Status, Sales (admin/super), Search.

### 10.2 Create Pembelian

> **Akses:** super_admin (auto-approve), admin, user

**Tabel Field Create Pembelian Lengkap:**

| #   | Field               | Widget                     | Tipe      | Validasi             | Default       | Keterangan                          |
| --- | ------------------- | -------------------------- | --------- | -------------------- | ------------- | ----------------------------------- |
| 1   | `tgl_transaksi`     | DatePicker                 | date      | **Required**         | Hari ini      | Readonly untuk role=user            |
| 2   | `syarat_pembayaran` | Dropdown                   | select    | **Required**         | —             | Cash, Net 7, Net 14, Net 30, Net 60 |
| 3   | `tgl_jatuh_tempo`   | Display only               | —         | Auto                 | Auto-hitung   |                                     |
| 4   | `no_transaksi`      | Display (readonly)         | —         | —                    | Auto          | PR-YYYYMMDD-...                     |
| 5   | `urgensi`           | Dropdown                   | select    | **Required**         | Sedang        | **Rendah / Sedang / Tinggi**        |
| 6   | `gudang_id`         | Dropdown / Hidden          | select    | **Required**         | User's gudang | SA=dropdown, lain=auto              |
| 7   | `tahun_anggaran`    | TextFormField              | text      | Optional             | —             | Tahun anggaran                      |
| 8   | `tag`               | Display (readonly)         | text      | Optional             | `user.name`   |                                     |
| 9   | `koordinat`         | Display (readonly)         | text      | Optional             | Auto GPS      |                                     |
| 10  | `produk_id[]`       | Searchable Dropdown + Scan | select    | **Required** (min 1) | —             | Barcode scan                        |
| 11  | `deskripsi[]`       | TextFormField              | text      | Optional             | —             | Per item                            |
| 12  | `kuantitas[]`       | TextFormField              | number    | **Required**, min 1  | —             |                                     |
| 13  | `unit[]`            | Display (readonly)         | text      | —                    | Dari produk   |                                     |
| 14  | `harga_satuan[]`    | TextFormField              | currency  | **Required**, min 0  | —             | Manual input                        |
| 15  | `diskon[]`          | TextFormField              | number    | Optional, 0-100      | 0             |                                     |
| 16  | `memo`              | TextFormField              | multiline | Optional             | —             |                                     |
| 17  | `lampiran[]`        | FilePicker                 | file      | Optional             | —             | Max 2MB                             |
| 18  | `diskon_akhir`      | TextFormField              | currency  | Optional             | 0             |                                     |
| 19  | `tax_percentage`    | TextFormField              | number    | **Required**, min 0  | 0             |                                     |

**⚠️ Berbeda dari Penjualan — TIDAK ADA:**

-   `pelanggan`, `email`, `alamat_penagihan` (pembelian ke supplier, bukan customer)
-   `no_referensi`
-   `tipe_harga` (tidak ada retail/grosir)

**⚠️ TAMBAHAN dari Penjualan:**

-   `urgensi` (Rendah/Sedang/Tinggi)
-   `tahun_anggaran`

**Kalkulasi:** Sama seperti Penjualan

**API Request (POST):**

```
POST /api/v1/pembelian
Authorization: Bearer {token}
Content-Type: multipart/form-data

Fields:
  tgl_transaksi: "2026-03-08"
  syarat_pembayaran: "Net 30"
  urgensi: "Tinggi"
  gudang_id: 1
  tahun_anggaran: "2026"
  tag: "Budi"
  koordinat: "-6.2088,106.8456"
  memo: "Urgent restok"
  diskon_akhir: 0
  tax_percentage: 11
  items[0][produk_id]: 5
  items[0][kuantitas]: 100
  items[0][harga_satuan]: 45000
  items[0][diskon]: 0
  lampiran[]: (file)
```

### 10.3 Edit Pembelian

> **Akses:** HANYA super_admin. Perbedaan sama seperti Edit Penjualan.

### 10.4 Detail Pembelian

Sama seperti detail Penjualan, dengan tambahan info:

-   **Urgensi** (badge: Rendah=hijau, Sedang=kuning, Tinggi=merah)
-   **Tahun Anggaran**
-   TIDAK ada: Pelanggan, Email, Alamat, Tipe Harga, No. Referensi

---

## 11. Modul: Biaya (Expense Management)

### Format Nomor: `EXP-YYYYMMDD-USERID-NOURUT`

### 11.1 List Biaya

**Summary Cards (4):**

-   Biaya Masuk (Rp, bulan ini)
-   Biaya Keluar (Rp, bulan ini)
-   Total Keseluruhan (Rp)
-   Pending Approval (count)

**Filter:** Jenis (Semua / Masuk / Keluar), Status, Search.

**Card Item:**

-   Nomor (EXP-xxx)
-   Jenis Badge (Masuk: hijau, Keluar: merah)
-   Penerima
-   Grand Total
-   Tanggal
-   Status Badge
-   Nama Creator

### 11.2 Create Biaya

> **Akses:** super_admin (auto-approve), admin, user

**Tabel Field Create Biaya Lengkap:**

| #   | Field              | Widget                     | Tipe      | Validasi            | Default          | Keterangan                             |
| --- | ------------------ | -------------------------- | --------- | ------------------- | ---------------- | -------------------------------------- |
| 1   | `jenis_biaya`      | Dropdown/Radio             | select    | **Required**        | keluar           | **keluar / masuk**                     |
| 2   | `bayar_dari`       | Dropdown                   | select    | **Required**        | —                | Kas (1-10001), Bank (1-10002), dll     |
| 3   | `penerima`         | Searchable Dropdown + Scan | select    | Optional            | —                | Dari data Kontak. Barcode scan         |
| 4   | `alamat_penagihan` | TextFormField              | multiline | Optional            | Auto-fill kontak |                                        |
| 5   | `tgl_transaksi`    | DatePicker                 | date      | **Required**        | Hari ini         | Readonly role=user                     |
| 6   | `cara_pembayaran`  | Dropdown                   | select    | Optional            | —                | **Tunai / Transfer Bank / Cek & Giro** |
| 7   | `no_transaksi`     | Display (readonly)         | —         | —                   | Auto             | EXP-YYYYMMDD-...                       |
| 8   | `tag`              | Display (readonly)         | text      | —                   | `user.name`      |                                        |
| 9   | `koordinat`        | Display (readonly)         | text      | —                   | Auto GPS         |                                        |
| 10  | `kategori[]`       | TextFormField              | text      | **Required**        | —                | **Item baris: nama kategori biaya**    |
| 11  | `deskripsi_akun[]` | TextFormField              | text      | Optional            | —                | Keterangan per item                    |
| 12  | `total[]`          | TextFormField              | currency  | **Required**, min 0 | —                | Nominal per item (Rp)                  |
| 13  | `memo`             | TextFormField              | multiline | Optional            | —                |                                        |
| 14  | `lampiran[]`       | FilePicker                 | file      | Optional            | —                | Max 2MB                                |
| 15  | `tax_percentage`   | TextFormField              | number    | **Required**, min 0 | 0                |                                        |

**⚠️ PERBEDAAN UTAMA — Item biaya BUKAN produk!**

-   Tidak ada `produk_id`, `kuantitas`, `harga_satuan`, `diskon`
-   Diganti dengan: `kategori` (teks bebas), `deskripsi_akun`, `total` (nominal langsung)

**Contoh Item Biaya:**

```
Item 1: Kategori="Transportasi", Deskripsi="Ongkir Jakarta-Bandung", Total=Rp 500.000
Item 2: Kategori="Operasional", Deskripsi="ATK Kantor", Total=Rp 200.000
```

**Kalkulasi:**

```
Subtotal:    sum(semua total[])
Tax Amount:  subtotal × (tax_percentage / 100)
Grand Total: subtotal + tax_amount
```

**Catatan Khusus:**

-   Super Admin → auto-approve saat create (status langsung "Approved")
-   Role lain → status "Pending"

**API Request (POST):**

```
POST /api/v1/biaya
Authorization: Bearer {token}
Content-Type: application/json

{
  "jenis_biaya": "keluar",
  "tgl_transaksi": "2026-03-08",
  "cara_pembayaran": "Transfer Bank",
  "bayar_dari": "Bank Mandiri",
  "penerima": "CV Logistik",
  "alamat_penagihan": "Jl. Industri 45",
  "tag": "Admin",
  "koordinat": "-6.2088,106.8456",
  "memo": "Pembayaran ongkir bulan Maret",
  "tax_percentage": 0,
  "items": [
    {
      "deskripsi": "Transportasi - Ongkir JKT",
      "jumlah": 500000
    },
    {
      "deskripsi": "Operasional - ATK",
      "jumlah": 200000
    }
  ]
}
```

**⚠️ Perbedaan API vs Web:**

-   API: `items[].deskripsi` + `items[].jumlah`
-   Web: `kategori[]` + `deskripsi_akun[]` + `total[]`

### 11.3 Edit Biaya

> **Akses:** HANYA super_admin

**Perbedaan dengan Create:**

-   `jenis_biaya` field **TIDAK muncul** di form edit (nilai tetap dari create)
-   `cara_pembayaran` hanya opsi: Tunai / Transfer Bank (tanpa Cek & Giro)
-   Barcode scan kontak tidak tersedia
-   Lampiran existing ditampilkan + bisa upload tambahan

### 11.4 Detail Biaya

**Info ditampilkan:**

-   Jenis Biaya badge (Masuk hijau / Keluar merah)
-   Bayar Dari
-   Penerima
-   Cara Pembayaran
-   Items: Kategori | Deskripsi | Nominal
-   Ringkasan (subtotal, pajak, total)
-   Lampiran
-   Memo
-   Print title: "BUKTI PENGELUARAN" atau "BUKTI PEMASUKAN"

---

## 12. Modul: Kunjungan (Field Visit)

### Format Nomor: `VST-YYYYMMDD-USERID-NOURUT`

### 12.1 List Kunjungan

**Summary Cards (5):**

-   Pemeriksaan Stock (count)
-   Penagihan (count)
-   Promo Gratis (count)
-   Promo Sample (count)
-   Canceled (count)

**Filter:** Tujuan (Semua / Pemeriksaan Stock / Penagihan / Promo Gratis / Promo Sample), Search.

**Grafik (admin/super_admin only):** Bar chart "Frekuensi Pemeriksaan Stock per Sales" dengan filter tanggal & produk.

### 12.2 Create Kunjungan

> **Akses:** super_admin (auto-approve), admin, user

**Tabel Field Create Kunjungan Lengkap:**

| #   | Field           | Widget                     | Tipe      | Validasi               | Default               | Keterangan                                              |
| --- | --------------- | -------------------------- | --------- | ---------------------- | --------------------- | ------------------------------------------------------- |
| 1   | `kontak_id`     | Searchable Dropdown + Scan | select    | **Required**           | —                     | Pilih kontak. Barcode scan. **Menyimpan ID bukan nama** |
| 2   | `sales_nama`    | Hidden                     | text      | **Required**           | Auto dari kontak      | Nama kontak yang dipilih                                |
| 3   | `sales_email`   | TextFormField              | email     | Optional               | Auto dari kontak      | Email kontak                                            |
| 4   | `sales_alamat`  | TextFormField              | multiline | Optional               | Auto dari kontak      | Alamat kontak                                           |
| 5   | `tgl_kunjungan` | Display (readonly)         | —         | **Required**           | **Hari ini (locked)** | **TIDAK BISA DIUBAH**                                   |
| 6   | `tujuan`        | Dropdown                   | select    | **Required**           | —                     | 4 opsi (lihat bawah)                                    |
| 7   | `gudang_id`     | Display / Hidden           | —         | —                      | User's gudang         |                                                         |
| 8   | `koordinat`     | Display (readonly)         | text      | Optional               | Auto GPS              | **Wajib untuk kunjungan**                               |
| 9   | `no_transaksi`  | Display (readonly)         | —         | —                      | Auto                  | VST-YYYYMMDD-...                                        |
| 10  | `produk_id[]`   | Searchable Dropdown + Scan | select    | **Conditional**        | —                     | Wajib jika tujuan butuh items                           |
| 11  | `jumlah[]`      | TextFormField              | number    | **Conditional**, min 1 | —                     |                                                         |
| 12  | `memo`          | TextFormField              | multiline | Optional               | —                     |                                                         |
| 13  | `lampiran[]`    | FilePicker                 | file      | Optional               | —                     | Max 2MB                                                 |

**4 Jenis Tujuan & Pengaruhnya:**

| Tujuan                | Items Wajib? |  Validasi Stok  | Efek saat Approve                           |
| --------------------- | :----------: | :-------------: | ------------------------------------------- |
| **Pemeriksaan Stock** |    ✅ Ya     |    Tidak ada    | Tidak ada perubahan stok (hanya pencatatan) |
| **Penagihan**         |   ❌ Tidak   |        —        | Tidak ada (kunjungan tagih hutang)          |
| **Promo Gratis**      |    ✅ Ya     | ≤ `stok_gratis` | **Mengurangi `stok_gratis`** per produk     |
| **Promo Sample**      |    ✅ Ya     | ≤ `stok_sample` | **Mengurangi `stok_sample`** per produk     |

**Logika Conditional:**

```dart
// Saat tujuan berubah:
if (tujuan == 'Penagihan') {
  // Sembunyikan tabel items
  // Items tidak perlu diisi
} else {
  // Tampilkan tabel items
  // Minimal 1 produk wajib diisi
}
```

**API Request (POST):**

```
POST /api/v1/kunjungan
Authorization: Bearer {token}
Content-Type: multipart/form-data

Fields:
  kontak_id: 15
  gudang_id: 1
  tgl_kunjungan: "2026-03-08"
  tujuan: "Pemeriksaan Stock"
  koordinat: "-6.2088,106.8456"
  memo: "Cek stok produk A dan B"
  items[0][produk_id]: 5
  items[0][kuantitas]: 50
  items[0][keterangan]: "Stok di rak depan"
  items[1][produk_id]: 8
  items[1][kuantitas]: 30
  lampiran[]: (file)
```

### 12.3 Edit Kunjungan

> **Akses:** HANYA super_admin

**Perbedaan dengan Create:**

-   Nomor & Status ditampilkan (readonly)
-   `tgl_kunjungan` **TETAP TIDAK BISA DIUBAH** (dari original)
-   `koordinat` editable dengan tombol refresh GPS
-   Barcode scan masih tersedia di edit
-   Lampiran existing ditampilkan + upload tambahan

### 12.4 Detail Kunjungan

**Info:**

-   Kontak (nama, email, alamat)
-   Tujuan (badge warna per tujuan)
-   Tanggal Kunjungan
-   Status
-   Items (jika ada): Produk | Jumlah | Keterangan
-   Koordinat → tombol "Lihat di Maps"
-   Lampiran
-   Memo
-   Actions: Approve, Cancel, Print, QR, Share

---

## 13. Modul: Pembayaran (Payment)

### Format Nomor: `PAY-YYYYMMDD-USERID-NOURUT`

### 13.1 List Pembayaran

**Summary Cards (4):**

-   Total Pembayaran Bulan Ini (Rp)
-   Total 30 Hari Terakhir (Rp)
-   Total Approved (Rp)
-   Pending Approval (count)

**Card Item:**

-   Nomor (PAY-xxx)
-   Nomor Invoice Penjualan terkait
-   Metode Pembayaran
-   Jumlah Bayar (Rp)
-   Status Badge
-   Tanggal

### 13.2 Create Pembayaran

> **Akses:** super_admin, admin, user. **TIDAK ADA FORM EDIT.**

**Tabel Field Create Pembayaran Lengkap:**

| #   | Field               | Widget             | Tipe         | Validasi             | Default            | Keterangan                                       |
| --- | ------------------- | ------------------ | ------------ | -------------------- | ------------------ | ------------------------------------------------ |
| 1   | `gudang_id`         | Dropdown / Hidden  | select       | **Required**         | User's gudang      | SA=dropdown, lain=auto                           |
| 2   | `no_transaksi`      | Display (readonly) | —            | —                    | Auto               | PAY-YYYYMMDD-...                                 |
| 3   | `tgl_pembayaran`    | DatePicker         | date         | **Required**         | Hari ini           | Readonly role=user                               |
| 4   | `metode_pembayaran` | Dropdown           | select       | **Required**         | —                  | **Cash / Transfer Bank / Giro / QRIS / Lainnya** |
| 5   | `penjualan_ids[]`   | Checkboxes         | multi-select | **Required** (min 1) | —                  | **AJAX load berdasarkan gudang**                 |
| 6   | `jumlah_bayar`      | TextFormField      | currency     | **Required**, min 1  | Auto dari invoices | Nominal pembayaran                               |
| 7   | `keterangan`        | TextFormField      | multiline    | Optional             | —                  |                                                  |
| 8   | `lampiran[]`        | FilePicker         | file         | Optional             | —                  | Bukti bayar. Max 2MB                             |

**Alur Khusus Form Pembayaran:**

```
1. Pilih Gudang
   ↓
2. AJAX load invoice penjualan yang belum lunas di gudang tersebut
   ↓
   ┌─────────────────────────────────────┐
   │ ☐ Pilih Semua                       │
   │ ☑ INV-20260301-1-01  Rp 5.000.000  │
   │   → Sisa: Rp 3.000.000             │
   │ ☐ INV-20260302-1-02  Rp 8.000.000  │
   │   → Sisa: Rp 8.000.000             │
   └─────────────────────────────────────┘
   ↓
3. Centang invoice yang akan dibayar
   ↓
4. `jumlah_bayar` otomatis terisi total sisa tagihan
   (bisa diedit untuk pembayaran parsial)
   ↓
5. Info ditampilkan:
   - Grand Total yang dipilih: Rp 5.000.000
   - Sudah Dibayar:   Rp 2.000.000
   - Sisa Tagihan:    Rp 3.000.000
   ↓
6. Simpan pembayaran
   ↓
7. Jika total_paid >= grand_total → Penjualan auto "Lunas"
```

**⚠️ Perbedaan API vs Web:**

-   **Web:** `penjualan_ids[]` (array, bisa bayar multiple invoice sekaligus)
-   **API:** `penjualan_id` (single integer, 1 invoice per pembayaran)

**API Request (POST):**

```
POST /api/v1/pembayaran
Authorization: Bearer {token}
Content-Type: application/json

{
  "penjualan_id": 123,
  "tgl_pembayaran": "2026-03-08",
  "metode_pembayaran": "Transfer Bank",
  "jumlah_bayar": 3000000,
  "keterangan": "Pembayaran cicilan ke-2"
}
```

**API Endpoints Tambahan:**

```
GET /api/v1/pembayaran/penjualan-by-gudang/{gudangId}
→ Return list invoice belum lunas {id, nomor, pelanggan, grand_total, sudah_bayar, sisa}

GET /api/v1/pembayaran/penjualan-detail/{id}
→ Return detail sisa tagihan spesifik invoice
```

### 13.3 Detail Pembayaran

**Info:**

-   Nomor Pembayaran
-   Invoice terkait (link ke detail penjualan)
-   Gudang
-   Tanggal Pembayaran
-   Metode
-   Jumlah Bayar (Rp)
-   Keterangan
-   Bukti/Lampiran
-   Status Badge
-   Actions: Approve, Cancel, Print, QR, Share

---

## 14. Modul: Penerimaan Barang (Goods Receipt)

### Format Nomor: `RCV-YYYYMMDD-USERID-NOURUT`

### 14.1 List Penerimaan Barang

**Summary Cards (4):**

-   Penerimaan Bulan Ini (count)
-   30 Hari Terakhir (count)
-   Total Approved (count)
-   Pending (count)

**Card Item:** Nomor, Nomor PO, No. Surat Jalan, Jumlah Item, Status, Tanggal.

### 14.2 Create Penerimaan Barang

> **Akses:** super_admin, admin, user. **TIDAK ADA FORM EDIT.**

**Tabel Field Create Penerimaan Barang Lengkap:**

| #   | Field             | Widget             | Tipe         | Validasi             | Default       | Keterangan                 |
| --- | ----------------- | ------------------ | ------------ | -------------------- | ------------- | -------------------------- |
| 1   | `gudang_id`       | Dropdown / Hidden  | select       | **Required**         | User's gudang |                            |
| 2   | `no_transaksi`    | Display (readonly) | —            | —                    | Auto          | RCV-YYYYMMDD-...           |
| 3   | `tgl_penerimaan`  | DatePicker         | date         | **Required**         | Hari ini      | Readonly role=user         |
| 4   | `no_surat_jalan`  | TextFormField      | text         | Optional, max 100    | —             | Nomor surat jalan          |
| 5   | `pembelian_ids[]` | Checkboxes         | multi-select | **Required** (min 1) | —             | AJAX load dari PO Approved |
| 6   | `keterangan`      | TextFormField      | multiline    | Optional             | —             |                            |
| 7   | `lampiran[]`      | FilePicker         | file         | Optional             | —             | Max 2MB                    |

**Field per Item Barang (muncul setelah pilih PO):**

| #   | Field                  | Widget        | Tipe   | Validasi                    | Keterangan                     |
| --- | ---------------------- | ------------- | ------ | --------------------------- | ------------------------------ | ------------------------------- |
| 8   | `items[].pembelian_id` | Hidden        | int    | **Required**                | Auto dari PO                   |
| 9   | `items[].produk_id`    | Hidden        | int    | **Required**                | Auto dari PO                   |
| 10  | `items[].nama_produk`  | Display       | —      | —                           | Nama produk (readonly)         |
| 11  | `items[].qty_dipesan`  | Display       | —      | —                           | Jumlah dipesan (readonly)      |
| 12  | `items[].qty_sisa`     | Display       | —      | —                           | Sisa belum diterima (readonly) |
| 13  | `items[].qty_diterima` | TextFormField | number | **Required**, min 0, ≤ sisa | —                              |
| 14  | `items[].qty_reject`   | TextFormField | number | Optional, min 0             | —                              |
| 15  | `items[].tipe_stok`    | Dropdown      | select | Optional                    | —                              | **penjualan / gratis / sample** |
| 16  | `items[].batch_number` | TextFormField | text   | Optional, max 100           | —                              |
| 17  | `items[].expired_date` | DatePicker    | date   | Optional                    | —                              |
| 18  | `items[].keterangan`   | TextFormField | text   | Optional                    | —                              |

**Alur Khusus:**

```
1. Pilih Gudang
   ↓
2. AJAX load PO yang Approved di gudang tersebut
   ┌─────────────────────────────────┐
   │ ☐ Pilih Semua                   │
   │ ☑ PR-20260301-1-01              │
   │   Supplier A | 5 items          │
   │ ☐ PR-20260302-1-02              │
   │   Supplier B | 3 items          │
   └─────────────────────────────────┘
   ↓
3. Centang PO → Items otomatis muncul
   ┌─────────────────────────────────────┐
   │ Produk A (dari PR-20260301-1-01)    │
   │ Dipesan: 100 Pcs | Sisa: 75        │
   │ Diterima: [___] Reject: [___]       │
   │ Tipe Stok: [penjualan ▼]           │
   │ Batch: [___] Exp: [📅]             │
   │ Catatan: [___]                      │
   └─────────────────────────────────────┘
   ↓
4. Input qty diterima per item
   ↓
5. Simpan → Status Pending
   ↓
6. Saat Approve:
   - stok bertambah sesuai tipe_stok:
     * penjualan → +stok_penjualan
     * gratis → +stok_gratis
     * sample → +stok_sample
   - StokLog entry dibuat
```

**⚠️ Perbedaan API vs Web:**

-   **Web:** `pembelian_ids[]` (array checkboxes)
-   **API:** `pembelian_id` (single integer)

**API Request (POST):**

```
POST /api/v1/penerimaan-barang
Authorization: Bearer {token}
Content-Type: application/json

{
  "gudang_id": 1,
  "pembelian_id": 45,
  "tgl_penerimaan": "2026-03-08",
  "no_surat_jalan": "SJ-001",
  "keterangan": "Barang datang lengkap",
  "items": [
    {
      "produk_id": 5,
      "qty_diterima": 80,
      "qty_reject": 5,
      "tipe_stok": "penjualan",
      "batch_number": "BATCH-2603",
      "expired_date": "2027-03-08",
      "keterangan": "OK"
    }
  ]
}
```

**API Endpoints Tambahan:**

```
GET /api/v1/penerimaan-barang/pembelian-by-gudang/{gudangId}
→ List PO Approved & belum fully received

GET /api/v1/penerimaan-barang/pembelian-detail/{id}
→ Detail PO + sisa item belum diterima
```

---

## 15. Modul: Kontak (Customer/Supplier)

### 15.1 List Kontak

**Search:** Cari berdasarkan kode, nama, email, no_telp

**Card Item:**

-   Kode Kontak (KT1, KT2, ...)
-   Nama
-   Email
-   No. Telp
-   Gudang
-   Diskon default (%)
-   Actions: Lihat, Edit, Delete (conditional)

### 15.2 Create Kontak

> **Akses:** super_admin, admin

**Tabel Field Create Kontak Lengkap:**

| #   | Field           | Widget        | Tipe      | Validasi                       | Default                | Keterangan                     |
| --- | --------------- | ------------- | --------- | ------------------------------ | ---------------------- | ------------------------------ |
| 1   | `kode_kontak`   | TextFormField | text      | Optional, max 50, **unique**   | Auto-generate (KT{id}) | Jika kosong, auto dari backend |
| 2   | `nama`          | TextFormField | text      | **Required**, max 255          | —                      |                                |
| 3   | `email`         | TextFormField | email     | Optional, max 255, **unique**  | —                      |                                |
| 4   | `no_telp`       | TextFormField | phone     | Optional, max 20               | —                      | Auto-normalize: `08` → `628`   |
| 5   | `pin`           | TextFormField | text      | Optional, **exactly 6 digits** | —                      | Untuk login customer portal    |
| 6   | `alamat`        | TextFormField | multiline | Optional                       | —                      |                                |
| 7   | `diskon_persen` | TextFormField | number    | Optional, 0-100                | 0                      | Diskon default pelanggan ini   |

**⚠️ CATATAN:** Field `gudang_id` **TIDAK ada di create** — akan auto-assigned dari gudang user yang membuat.

**API Request (POST):**

```
POST /api/v1/kontak
Authorization: Bearer {token}

{
  "nama": "Toko ABC",
  "email": "abc@mail.com",
  "no_telp": "6281234567890",
  "alamat": "Jl. Merdeka 123",
  "diskon_persen": 5,
  "gudang_id": 1
}
```

### 15.3 Edit Kontak

> **Akses berbeda per role!**

**Edit untuk admin / user:**

| #   | Field | Widget        | Validasi           | Keterangan                      |
| --- | ----- | ------------- | ------------------ | ------------------------------- |
| 1   | `pin` | TextFormField | Optional, 6 digits | **HANYA PIN yang bisa diedit!** |

**Edit untuk super_admin (SEMUA field):**

| #   | Field           | Widget        | Validasi                        | Keterangan                     |
| --- | --------------- | ------------- | ------------------------------- | ------------------------------ |
| 1   | `kode_kontak`   | TextFormField | Optional, unique (exclude self) |                                |
| 2   | `nama`          | TextFormField | **Required**                    |                                |
| 3   | `email`         | TextFormField | Optional, unique (exclude self) |                                |
| 4   | `no_telp`       | TextFormField | Optional                        |                                |
| 5   | `pin`           | TextFormField | Optional, 6 digits              |                                |
| 6   | `alamat`        | TextFormField | Optional                        |                                |
| 7   | `diskon_persen` | TextFormField | Optional, 0-100                 |                                |
| 8   | `gudang_id`     | Dropdown      | Optional                        | **HANYA di edit super_admin!** |

**API Request (PUT):**

```
PUT /api/v1/kontak/{id}
Authorization: Bearer {token}

{
  "nama": "Toko ABC Updated",
  "email": "abc_new@mail.com",
  "no_telp": "6281234567899",
  "alamat": "Jl. Baru 456",
  "diskon_persen": 10
}
```

### 15.4 Detail Kontak

-   Semua info kontak
-   Barcode/QR Code dari kode kontak
-   Tombol: Print Barcode, Edit, Delete (super_admin only)

---

## 16. Modul: Produk (Product Master)

> **CRUD:** super_admin ONLY. **Read:** Semua role.

### 16.1 List Produk

**Search:** item_code, nama_produk

**Card Item:** Item Code, Nama, Harga Retail (Rp), Harga Grosir (Rp), Satuan.

### 16.2 Create Produk

> **Akses:** super_admin ONLY

**Tabel Field Create Produk Lengkap:**

| #   | Field          | Widget        | Tipe      | Validasi                      | Default | Keterangan               |
| --- | -------------- | ------------- | --------- | ----------------------------- | ------- | ------------------------ |
| 1   | `nama_produk`  | TextFormField | text      | **Required**, max 255         | —       |                          |
| 2   | `item_code`    | TextFormField | text      | Optional, max 255, **unique** | —       | SKU / kode barcode       |
| 3   | `harga`        | TextFormField | currency  | **Required**, min 0           | —       | **Harga Retail**         |
| 4   | `harga_grosir` | TextFormField | currency  | Optional, min 0               | 0       | Harga untuk tipe grosir  |
| 5   | `satuan`       | Dropdown      | select    | **Required**                  | —       | **Pcs / Lusin / Karton** |
| 6   | `deskripsi`    | TextFormField | multiline | Optional                      | —       |                          |

**API Request (POST):**

```
POST /api/v1/produk
Authorization: Bearer {token}

{
  "nama_produk": "Produk A",
  "item_code": "PRD001",
  "harga": 50000,
  "harga_grosir": 45000,
  "satuan": "Pcs",
  "deskripsi": "Deskripsi produk A"
}
```

### 16.3 Edit Produk

> **Akses:** super_admin ONLY

**Field sama persis dengan Create.** `item_code` unique validation exclude current ID.

**API Request (PUT):**

```
PUT /api/v1/produk/{id}
Authorization: Bearer {token}

{
  "nama_produk": "Produk A Updated",
  "item_code": "PRD001",
  "harga": 55000,
  "harga_grosir": 48000,
  "satuan": "Pcs",
  "deskripsi": "Deskripsi updated"
}
```

### 16.4 Detail Produk

-   Semua info produk
-   Barcode/QR Code dari item_code
-   **Stok per gudang (tabel):**

| Gudang        | Stok Penjualan | Stok Gratis | Stok Sample | Total |
| ------------- | :------------: | :---------: | :---------: | :---: |
| Gudang Utama  |      100       |     20      |     10      |  130  |
| Gudang Cabang |       50       |      5      |      3      |  58   |

---

## 17. Modul: Stok Gudang (Inventory)

### 17.1 Halaman Stok

**Layout:**

```
┌──────────────────────────────────────┐
│ ╔══ "Stok Gudang"       [📥Export]╗  │
│ ╚══════════════════════════════════╝ │
│  Gudang: [Dropdown ▼]               │
│                                      │
│  ── Update Stok ──  (Super Admin)    │
│  Produk:         [Dropdown ▼]        │
│  Stok Penjualan: [___]              │
│  Stok Gratis:    [___]              │
│  Stok Sample:    [___]              │
│  Keterangan *:   [_______________]  │
│  [UPDATE STOK] (gradient btn)        │
│                                      │
│  ── Daftar Stok ──                   │
│  ┌──────────────────────────────┐    │
│  │ PRD001 - Produk A            │    │
│  │ Penjualan: 100               │    │
│  │ Gratis: 20 | Sample: 10      │    │
│  │ Total: 130            [📋]   │    │
│  └──────────────────────────────┘    │
└──────────────────────────────────────┘
```

**Field Update Stok (Super Admin only):**

| #   | Field            | Widget        | Tipe   | Validasi     | Keterangan                         |
| --- | ---------------- | ------------- | ------ | ------------ | ---------------------------------- |
| 1   | `gudang_id`      | Dropdown      | select | **Required** |                                    |
| 2   | `produk_id`      | Dropdown      | select | **Required** |                                    |
| 3   | `stok_penjualan` | TextFormField | number | Optional     | Set langsung (bukan tambah/kurang) |
| 4   | `stok_gratis`    | TextFormField | number | Optional     |                                    |
| 5   | `stok_sample`    | TextFormField | number | Optional     |                                    |
| 6   | `keterangan`     | TextFormField | text   | **Required** | Alasan update wajib diisi          |

**API Request (POST):**

```
POST /api/v1/stok
Authorization: Bearer {token}

{
  "gudang_id": 1,
  "produk_id": 5,
  "stok_penjualan": 150,
  "stok_gratis": 25,
  "stok_sample": 15,
  "keterangan": "Restok dari PO supplier"
}
```

### 17.2 Riwayat Stok (Log)

**Filter:** Gudang, Produk, Tanggal Dari, Tanggal Sampai.

**Card Item Log:**

```
┌──────────────────────────────────┐
│ Produk A - Gudang Utama          │
│ 08 Mar 2026, 14:30               │
│ Sebelum: 80 → Sesudah: 100      │
│ Selisih: +20                     │
│ Oleh: Admin                      │
│ Ket: Restok dari supplier        │
└──────────────────────────────────┘
```

**API:**

```
GET /api/v1/stok?gudang_id=1
GET /api/v1/stok/log?gudang_id=1&produk_id=5&dari=2026-03-01&sampai=2026-03-08
```

---

## 18. Modul: Gudang (Warehouse)

> **CRUD:** super_admin ONLY. **API Gudang hanya Read + Switch.**

### 18.1 List Gudang

| Kolom       | Tipe         |
| ----------- | ------------ |
| ID          | Angka        |
| Nama Gudang | Text         |
| Alamat      | Text         |
| Actions     | Edit, Delete |

### 18.2 Create Gudang

| #   | Field           | Widget        | Tipe      | Validasi                          | Keterangan |
| --- | --------------- | ------------- | --------- | --------------------------------- | ---------- |
| 1   | `nama_gudang`   | TextFormField | text      | **Required**, max 255, **unique** |            |
| 2   | `alamat_gudang` | TextFormField | multiline | Optional                          |            |

### 18.3 Edit Gudang

**Field sama dengan Create.** `nama_gudang` unique exclude current.

> **⚠️ API tidak punya endpoint create/update gudang.** Hanya web-based. Jika ingin di Flutter, perlu tambah API endpoint.

---

## 19. Modul: Manajemen User

> **Akses:** super_admin ONLY

### 19.1 List User

**Filter:** Role dropdown, Search nama/email.

**Card Item:**

-   Nama
-   Email
-   Role Badge:
    -   Super Admin: 🟣 Purple/Violet
    -   Admin: 🟢 Green
    -   Spectator: 🔵 Blue
    -   User: 🩵 Light Blue
-   Gudang (assignment)

### 19.2 Create User

**Tabel Field Create User Lengkap:**

| #   | Field                   | Widget        | Tipe         | Validasi                                     | Default | Keterangan                                              |
| --- | ----------------------- | ------------- | ------------ | -------------------------------------------- | ------- | ------------------------------------------------------- |
| 1   | `name`                  | TextFormField | text         | **Required**, max 255                        | —       |                                                         |
| 2   | `email`                 | TextFormField | email        | **Required**, **unique**                     | —       |                                                         |
| 3   | `no_telp`               | TextFormField | phone        | Optional                                     | —       |                                                         |
| 4   | `alamat`                | TextFormField | text         | Optional                                     | —       |                                                         |
| 5   | `role`                  | Dropdown      | select       | **Required**                                 | —       | super_admin / admin / spectator / user                  |
| 6   | `gudang_id`             | Dropdown      | select       | **Required if role=user**                    | —       | Single gudang. **Tampil jika role=user**                |
| 7   | `gudangs[]`             | Checkboxes    | multi-select | **Required if role=admin/spectator** (min 1) | —       | Multi gudang. **Tampil jika role=admin atau spectator** |
| 8   | `password`              | TextFormField | obscure      | **Required**, min 8                          | —       |                                                         |
| 9   | `password_confirmation` | TextFormField | obscure      | **Required**, must match                     | —       |                                                         |

**Logika Conditional Gudang:**

```dart
// Saat role berubah:
switch (selectedRole) {
  case 'super_admin':
    // Sembunyikan gudang_id DAN gudangs[]
    // SA akses semua gudang otomatis
    break;
  case 'admin':
  case 'spectator':
    // Tampilkan gudangs[] (checkboxes multi-select)
    // Sembunyikan gudang_id
    break;
  case 'user':
    // Tampilkan gudang_id (single dropdown)
    // Sembunyikan gudangs[]
    break;
}
```

**API Request (POST):**

```
POST /api/v1/users
Authorization: Bearer {token}

{
  "name": "Budi Sales",
  "email": "budi@hibiscusefsya.com",
  "role": "user",
  "password": "password123",
  "password_confirmation": "password123",
  "no_telp": "6281234567890",
  "alamat": "Jl. Raya 100",
  "gudang_id": 1
}

// Untuk admin/spectator:
{
  "name": "Admin Cabang",
  "email": "admin_cabang@hibiscusefsya.com",
  "role": "admin",
  "password": "password123",
  "password_confirmation": "password123",
  "gudangs": [1, 2, 3]
}
```

### 19.3 Edit User

**Perbedaan dengan Create:**

-   `password` **OPTIONAL** (kosong = tidak diubah)
-   `password_confirmation` hanya wajib jika password diisi
-   Pre-load gudang assignment dari pivot tables
-   Email unique exclude current user

**API Request (PUT):**

```
PUT /api/v1/users/{id}
Authorization: Bearer {token}

{
  "name": "Budi Sales Updated",
  "email": "budi@hibiscusefsya.com",
  "role": "user",
  "no_telp": "6281234567899",
  "gudang_id": 2
}
// Password field omitted = no change
```

---

## 20. Fitur: Print Bluetooth (ESC/POS)

### Deskripsi

Cetak struk langsung ke printer thermal Bluetooth 58mm menggunakan ESC/POS commands.

### Package Flutter

```yaml
dependencies:
    esc_pos_bluetooth: ^0.4.1
    esc_pos_utils: ^1.1.0
```

### Alur Print

```
1. User klik "Print Bluetooth" di halaman detail
2. App scan Bluetooth devices → dialog pilih printer
3. Connect → kirim ESC/POS bytes
4. Printer cetak struk 58mm
5. Disconnect
```

### Format Struk 58mm — Penjualan

```
═════════════════════════════
         [LOGO]
    INVOICE PENJUALAN
─────────────────────────────
Nomor    : INV-20260308-1-01
Tanggal  : 08/03/2026
Jatuh Tempo: 07/04/2026
Pembayaran: Net 30
─────────────────────────────
Pelanggan: Toko ABC
Sales    : Budi
Gudang   : Gudang Utama
─────────────────────────────
DAFTAR PRODUK:

1. Produk A
   Qty: 10 Pcs
   Harga: Rp 50.000
   Disc: 5%
   Jumlah: Rp 475.000

2. Produk B
   Qty: 5 Box
   Harga: Rp 120.000
   Jumlah: Rp 600.000

─────────────────────────────
Subtotal    : Rp 1.075.000
Diskon      : Rp    50.000
Pajak 11%   : Rp   112.750
─────────────────────────────
GRAND TOTAL : Rp 1.137.750
═════════════════════════════
       [QR CODE]
  Scan untuk melihat invoice

marketing@hibiscusefsya.com
──── Terima Kasih ────
```

### Format Struk 58mm — Biaya / Expense

```
═════════════════════════════
     BUKTI PENGELUARAN
─────────────────────────────
Nomor   : EXP-20260308-1-01
Tanggal : 08/03/2026
Jenis   : Biaya Keluar
Bayar   : Transfer
Dari    : Bank Mandiri
Penerima: CV Logistik
─────────────────────────────
RINCIAN:

1. Transportasi
   Ket: Ongkir Jakarta
   Jumlah: Rp 500.000

2. Operasional
   Ket: ATK Kantor
   Jumlah: Rp 200.000

─────────────────────────────
GRAND TOTAL : Rp 700.000
═════════════════════════════
──── Dokumen Internal ────
```

### Format Struk 58mm — Kunjungan

```
═════════════════════════════
      LAPORAN KUNJUNGAN
─────────────────────────────
Nomor    : VST-20260308-1-01
Tanggal  : 08/03/2026
Tujuan   : Pemeriksaan Stock
─────────────────────────────
Kontak   : Toko ABC
Alamat   : Jl. Merdeka 123
Sales    : Budi
─────────────────────────────
PRODUK DIPERIKSA:

1. Produk A
   Jumlah: 50 Pcs

2. Produk B
   Jumlah: 30 Box

─────────────────────────────
       [QR CODE]
──── Terima Kasih ────
```

### Format Struk 58mm — Pembayaran

```
═════════════════════════════
     BUKTI PEMBAYARAN
─────────────────────────────
Nomor   : PAY-20260308-1-01
Tanggal : 08/03/2026
Metode  : Transfer Bank
─────────────────────────────
Invoice : INV-20260301-1-01
Pelanggan: Toko ABC
─────────────────────────────
Grand Total   : Rp 5.000.000
Sudah Bayar   : Rp 2.000.000
Pembayaran Ini: Rp 3.000.000
─────────────────────────────
Sisa Tagihan  : Rp 0
Status        : LUNAS ✓
═════════════════════════════
──── Terima Kasih ────
```

### Format Struk 58mm — Penerimaan Barang

```
═════════════════════════════
   BUKTI PENERIMAAN BARANG
─────────────────────────────
Nomor   : RCV-20260308-1-01
Tanggal : 08/03/2026
Surat Jalan: SJ-001
─────────────────────────────
PO      : PR-20260301-1-01
Gudang  : Gudang Utama
─────────────────────────────
BARANG DITERIMA:

1. Produk A
   Diterima: 80 Pcs
   Reject: 5 Pcs
   Batch: BATCH-2603
   Exp: 08/03/2027

2. Produk B
   Diterima: 50 Box
   Reject: 0

─────────────────────────────
Total Diterima: 130
Total Reject  : 5
═════════════════════════════
──── Terima Kasih ────
```

### Implementasi ESC/POS

```dart
Future<void> printPenjualan(Penjualan data) async {
  final profile = await CapabilityProfile.load();
  final generator = Generator(PaperSize.mm58, profile);
  List<int> bytes = [];

  // Header
  bytes += generator.text('INVOICE PENJUALAN',
    styles: PosStyles(align: PosAlign.center, bold: true, height: PosTextSize.size2));
  bytes += generator.hr(ch: '─');

  // Info
  bytes += generator.text('Nomor  : ${data.nomor}');
  bytes += generator.text('Tanggal: ${formatTanggal(data.tglTransaksi)}');
  bytes += generator.text('J.Tempo: ${formatTanggal(data.tglJatuhTempo)}');
  bytes += generator.text('Bayar  : ${data.syaratPembayaran}');
  bytes += generator.hr(ch: '─');
  bytes += generator.text('Pembeli: ${data.pelanggan}');
  bytes += generator.text('Sales  : ${data.tag}');
  bytes += generator.text('Gudang : ${data.gudang?.namaGudang}');
  bytes += generator.hr(ch: '─');

  // Items
  for (int i = 0; i < data.items.length; i++) {
    final item = data.items[i];
    bytes += generator.text('${i+1}. ${item.produk?.namaProduk}',
      styles: PosStyles(bold: true));
    bytes += generator.text('   ${item.kuantitas} ${item.unit} × ${formatRp(item.hargaSatuan)}');
    if (item.diskon > 0) bytes += generator.text('   Disc: ${item.diskon}%');
    bytes += generator.text('   Jumlah: ${formatRp(item.jumlahBaris)}');
  }

  bytes += generator.hr(ch: '─');
  bytes += generator.text('Subtotal : ${formatRp(data.subtotal)}');
  if (data.diskonAkhir > 0) bytes += generator.text('Diskon   : ${formatRp(data.diskonAkhir)}');
  if (data.taxPercentage > 0) bytes += generator.text('Pajak ${data.taxPercentage}%: ${formatRp(data.taxAmount)}');
  bytes += generator.hr(ch: '═');
  bytes += generator.text('TOTAL: ${formatRp(data.grandTotal)}',
    styles: PosStyles(bold: true, height: PosTextSize.size2));

  // QR
  bytes += generator.qrcode(data.publicUrl);
  bytes += generator.text('Scan untuk invoice', styles: PosStyles(align: PosAlign.center));
  bytes += generator.cut();

  await printerManager.writeBytes(bytes);
}
```

---

## 21. Fitur: Print Struk / Cetak PDF

### Package Flutter

```yaml
dependencies:
    pdf: ^3.10.0
    printing: ^5.12.0
```

### Implementasi

Generate PDF dengan format 58mm lalu show print dialog atau share:

```dart
Future<void> generatePdf(Penjualan data) async {
  final pdf = pw.Document();
  pdf.addPage(pw.Page(
    pageFormat: PdfPageFormat(58 * PdfPageFormat.mm, double.infinity),
    build: (context) => pw.Column(children: [
      pw.Text('INVOICE PENJUALAN', style: pw.TextStyle(fontWeight: pw.FontWeight.bold)),
      pw.Divider(),
      // ... layout sama seperti BT print
    ]),
  ));
  await Printing.layoutPdf(onLayout: (format) => pdf.save());
}
```

---

## 22. Fitur: Public Invoice & QR Code

### URL Format (6 tipe transaksi)

```
https://sales.hibiscusefsya.com/public/invoice/penjualan/{uuid}
https://sales.hibiscusefsya.com/public/invoice/pembelian/{uuid}
https://sales.hibiscusefsya.com/public/invoice/biaya/{uuid}
https://sales.hibiscusefsya.com/public/invoice/kunjungan/{uuid}
https://sales.hibiscusefsya.com/public/invoice/pembayaran/{uuid}
https://sales.hibiscusefsya.com/public/invoice/penerimaan-barang/{uuid}

Download PDF:
https://sales.hibiscusefsya.com/public/invoice/penjualan/{uuid}/download
```

### QR Code di App

```
1. User klik "QR Code" di detail
2. BottomSheet muncul dengan QR Code (package qr_flutter)
3. QR berisi public URL
4. Bisa di-scan dari HP lain → buka browser → invoice cantik
```

### Share Invoice

```dart
Share.share(
  'Invoice Penjualan ${data.nomor}\nTotal: ${formatRp(data.grandTotal)}\nLihat: ${data.publicUrl}',
  subject: 'Invoice ${data.nomor}',
);
```

---

## 23. Fitur: Email Invoice & Notifikasi

### 23.1 Email Invoice (Admin/Super Admin → Customer)

**Trigger:** Klik "Email Invoice" di halaman detail
**Alur:**

1. Backend generate PDF dari template
2. Kirim email ke customer/email yang tertera
3. Subject: `Invoice Penjualan #INV-xxx - Hibiscus Efsya`
4. Body: HTML template cantik
5. Attachment: PDF invoice

### 23.2 Notifikasi Otomatis

**Saat Transaksi Dibuat:**

-   Email ke Super Admin + Admin gudang
-   Subject: `[Transaksi Baru] Penjualan #INV-xxx`

**Saat Diapprove:**

-   Email ke pembuat transaksi
-   Subject: `[Disetujui] Penjualan #INV-xxx`

### 23.3 Push Notification (Flutter)

-   Firebase Cloud Messaging (FCM)
-   Bell icon (🔔) di AppBar + badge count
-   Notifikasi pending approval
-   Tap notification → navigate ke detail transaksi

---

## 24. Fitur: Barcode & QR Scanner

### Package: `mobile_scanner: ^5.0.0`

### Penggunaan di Form

| Form             | Field     | Yang Di-scan      | Output                             |
| ---------------- | --------- | ----------------- | ---------------------------------- |
| Penjualan Create | Pelanggan | QR/Barcode kontak | Auto-fill pelanggan, email, alamat |
| Penjualan Create | Produk    | Barcode item_code | Auto-add produk ke list            |
| Pembelian Create | Produk    | Barcode item_code | Auto-add produk                    |
| Biaya Create     | Penerima  | QR/Barcode kontak | Auto-fill penerima                 |
| Kunjungan Create | Kontak    | QR/Barcode kontak | Auto-fill kontak                   |
| Kunjungan Create | Produk    | Barcode item_code | Auto-add item                      |

---

## 25. Fitur: GPS / Lokasi

### Package: `geolocator: ^12.0.0` + `url_launcher: ^6.2.0`

### Penggunaan

| Form      | Behavior                                           |
| --------- | -------------------------------------------------- |
| Penjualan | Auto-capture saat buka form, simpan di `koordinat` |
| Pembelian | Auto-capture saat buka form                        |
| Biaya     | Auto-capture saat buka form                        |
| Kunjungan | Auto-capture saat buka form (**wajib**)            |

### Tampilan di Detail

```
📍 Koordinat: -6.2088, 106.8456  [🗺 Lihat di Maps]
```

Tap → `launchUrl(Uri.parse('https://www.google.com/maps?q=-6.2088,106.8456'))`

---

## 26. Fitur: Export Excel

### Package: `excel: ^4.0.0` + `path_provider` + `share_plus`

### Data yang Bisa Di-export

| Modul           | Kolom Excel                                              |
| --------------- | -------------------------------------------------------- |
| Stok per Gudang | Kode, Nama Produk, Stok Penjualan, Gratis, Sample, Total |
| Penjualan       | Nomor, Pelanggan, Tanggal, Grand Total, Status           |
| Pembelian       | Nomor, Tanggal, Grand Total, Urgensi, Status             |

---

## 27. Fitur: Notifikasi & Approval

### Bell Notification di AppBar

```
┌──────────────────────────────────────┐
│ ╔══ Gradient AppBar ═══════════════╗ │
│ ║ [☰]  Logo          [🔔 3] [👤]  ║ │
│ ╚══════════════════════════════════╝ │
│                          │           │
│                          ▼           │
│  ┌────────────────────────────┐      │
│  │ 📝 INV-20260308-1-01      │      │
│  │ Rp 5.250.000 • 2 jam lalu │      │
│  ├────────────────────────────┤      │
│  │ 🛒 PR-20260307-1-02       │      │
│  │ Rp 12.000.000 • 5 jam lalu│      │
│  ├────────────────────────────┤      │
│  │ [Lihat Semua Pending]     │      │
│  └────────────────────────────┘      │
└──────────────────────────────────────┘
```

**Tampil untuk:** Super Admin, Admin
**Isi:** Transaksi pending di gudang yang diakses

### Approval Flow

```
┌──────────┐  Create   ┌──────────┐  Approve  ┌──────────┐
│  (none)  │ ────────▶ │ Pending  │ ────────▶ │ Approved │
└──────────┘           └──────────┘            └──────────┘
                            │                       │
                    Cancel  │               Cancel  │  (Penjualan only)
                            ▼                       │  Mark Lunas
                       ┌──────────┐                 ▼
                       │ Canceled │           ┌──────────┐
                       └──────────┘           │  Lunas   │
                            │                 └──────────┘
                   Uncancel │
                   (SA only)│
                            ▼
                       ┌──────────┐
                       │ Pending  │ (kembali)
                       └──────────┘
```

---

## 28. Fitur: Switch Gudang

### UI (di Drawer atau Settings)

```
┌──────────────────────────────┐
│ 🏢 Gudang Aktif              │
│ ┌────────────────────────┐   │
│ │ Gudang Utama       ✓   │   │
│ │ Gudang Cabang Jakarta  │   │
│ │ Gudang Cabang Surabaya │   │
│ └────────────────────────┘   │
└──────────────────────────────┘
```

**API:** `POST /api/v1/gudang/switch` → `{ "gudang_id": 2 }`

**Setelah switch:** Refresh dashboard, list transaksi, stok → data gudang baru.

---

## 29. Fitur: Upload Lampiran

### Semua form transaksi mendukung multiple lampiran.

| Tipe     | Extension               |
| -------- | ----------------------- |
| Image    | `.jpg`, `.jpeg`, `.png` |
| Document | `.pdf`, `.doc`, `.docx` |
| Archive  | `.zip`                  |

**Max size:** 2MB per file

### Tampilan di Detail

```
📎 Lampiran (3 file)
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ 🖼 foto1.jpg │ │ 📄 bukti.pdf │ │ 📄 surat.doc │
│   [Lihat]    │ │   [Unduh]    │ │   [Unduh]    │
└──────────────┘ └──────────────┘ └──────────────┘
```

---

## 30. API Endpoints Lengkap

### Base URL: `https://sales.hibiscusefsya.com/api/v1`

### Auth Header: `Authorization: Bearer {token}`

### Autentikasi

| Method | Endpoint           | Deskripsi                     |
| ------ | ------------------ | ----------------------------- |
| `POST` | `/login`           | Login → token + user + gudang |
| `POST` | `/logout`          | Revoke token                  |
| `GET`  | `/profile`         | Get profile + gudang aktif    |
| `PUT`  | `/profile`         | Update nama, telp, alamat     |
| `POST` | `/change-password` | Ganti password                |

### Dashboard

| Method | Endpoint     | Deskripsi           |
| ------ | ------------ | ------------------- |
| `GET`  | `/dashboard` | Statistik dashboard |

### Gudang

| Method | Endpoint           | Deskripsi           |
| ------ | ------------------ | ------------------- |
| `GET`  | `/gudang`          | List semua gudang   |
| `POST` | `/gudang/switch`   | Switch gudang aktif |
| `GET`  | `/gudang/stok`     | Stok per gudang     |
| `GET`  | `/gudang/stok-log` | Log perubahan stok  |

### Produk

| Method   | Endpoint                  | Deskripsi                |
| -------- | ------------------------- | ------------------------ |
| `GET`    | `/produk`                 | List (paginated, search) |
| `GET`    | `/produk/{id}`            | Detail                   |
| `POST`   | `/produk`                 | Create (super_admin)     |
| `PUT`    | `/produk/{id}`            | Update (super_admin)     |
| `DELETE` | `/produk/{id}`            | Delete (super_admin)     |
| `GET`    | `/produk/stok/{gudangId}` | Stok per gudang          |

### Kontak

| Method   | Endpoint       | Deskripsi                |
| -------- | -------------- | ------------------------ |
| `GET`    | `/kontak`      | List (paginated, search) |
| `GET`    | `/kontak/{id}` | Detail                   |
| `POST`   | `/kontak`      | Create                   |
| `PUT`    | `/kontak/{id}` | Update                   |
| `DELETE` | `/kontak/{id}` | Delete                   |

### Penjualan

| Method | Endpoint                  | Deskripsi                                         |
| ------ | ------------------------- | ------------------------------------------------- |
| `GET`  | `/penjualan`              | List (paginated, filter: status, user_id, search) |
| `GET`  | `/penjualan/{id}`         | Detail + items                                    |
| `POST` | `/penjualan`              | Create (multipart/form-data)                      |
| `POST` | `/penjualan/{id}/approve` | Approve → status Approved                         |
| `POST` | `/penjualan/{id}/cancel`  | Cancel → status Canceled                          |

### Pembelian

| Method | Endpoint                  | Deskripsi      |
| ------ | ------------------------- | -------------- |
| `GET`  | `/pembelian`              | List           |
| `GET`  | `/pembelian/{id}`         | Detail + items |
| `POST` | `/pembelian`              | Create         |
| `POST` | `/pembelian/{id}/approve` | Approve        |
| `POST` | `/pembelian/{id}/cancel`  | Cancel         |

### Biaya

| Method | Endpoint              | Deskripsi                         |
| ------ | --------------------- | --------------------------------- |
| `GET`  | `/biaya`              | List (filter: jenis=masuk/keluar) |
| `GET`  | `/biaya/{id}`         | Detail + items                    |
| `POST` | `/biaya`              | Create                            |
| `POST` | `/biaya/{id}/approve` | Approve                           |
| `POST` | `/biaya/{id}/cancel`  | Cancel                            |

### Kunjungan

| Method | Endpoint                  | Deskripsi             |
| ------ | ------------------------- | --------------------- |
| `GET`  | `/kunjungan`              | List (filter: tujuan) |
| `GET`  | `/kunjungan/{id}`         | Detail + items        |
| `POST` | `/kunjungan`              | Create                |
| `POST` | `/kunjungan/{id}/approve` | Approve               |
| `POST` | `/kunjungan/{id}/cancel`  | Cancel                |

### Pembayaran

| Method | Endpoint                                     | Deskripsi                 |
| ------ | -------------------------------------------- | ------------------------- |
| `GET`  | `/pembayaran`                                | List                      |
| `GET`  | `/pembayaran/{id}`                           | Detail                    |
| `POST` | `/pembayaran`                                | Create                    |
| `POST` | `/pembayaran/{id}/approve`                   | Approve                   |
| `POST` | `/pembayaran/{id}/cancel`                    | Cancel                    |
| `GET`  | `/pembayaran/penjualan-by-gudang/{gudangId}` | Invoice unpaid per gudang |
| `GET`  | `/pembayaran/penjualan-detail/{id}`          | Sisa tagihan invoice      |

### Penerimaan Barang

| Method | Endpoint                                            | Deskripsi               |
| ------ | --------------------------------------------------- | ----------------------- |
| `GET`  | `/penerimaan-barang`                                | List                    |
| `GET`  | `/penerimaan-barang/{id}`                           | Detail + items          |
| `POST` | `/penerimaan-barang`                                | Create                  |
| `POST` | `/penerimaan-barang/{id}/approve`                   | Approve (+ update stok) |
| `POST` | `/penerimaan-barang/{id}/cancel`                    | Cancel                  |
| `GET`  | `/penerimaan-barang/pembelian-by-gudang/{gudangId}` | PO approved per gudang  |
| `GET`  | `/penerimaan-barang/pembelian-detail/{id}`          | Detail PO + sisa item   |

### Stok

| Method | Endpoint    | Deskripsi                   |
| ------ | ----------- | --------------------------- |
| `GET`  | `/stok`     | List stok per gudang        |
| `POST` | `/stok`     | Manual update (super_admin) |
| `GET`  | `/stok/log` | Log perubahan stok          |

### User Management

| Method   | Endpoint      | Deskripsi          |
| -------- | ------------- | ------------------ |
| `GET`    | `/users`      | List (super_admin) |
| `GET`    | `/users/{id}` | Detail             |
| `POST`   | `/users`      | Create             |
| `PUT`    | `/users/{id}` | Update             |
| `DELETE` | `/users/{id}` | Delete             |

---

## 31. Skema Database / Model

### User

```dart
class User {
  int id;
  String name;
  String email;
  String role;             // super_admin, admin, spectator, user
  String? noTelp;
  String? alamat;
  int? gudangId;           // For role=user (single)
  int? currentGudangId;    // Currently active gudang
  Gudang? gudang;
  List<Gudang>? gudangs;          // Admin multi-gudang (pivot)
  List<Gudang>? spectatorGudangs; // Spectator multi-gudang (pivot)
}
```

### Gudang

```dart
class Gudang {
  int id;
  String namaGudang;
  String? alamatGudang;
}
```

### Produk

```dart
class Produk {
  int id;
  String itemCode;
  String namaProduk;
  String? kategori;
  String? deskripsi;
  String? satuan;         // Pcs, Lusin, Karton
  double harga;           // Harga retail
  double? hargaGrosir;
  int? stokAlert;
}
```

### GudangProduk (Stok per Gudang)

```dart
class GudangProduk {
  int id;
  int gudangId;
  int produkId;
  int stok;               // Legacy field
  int stokPenjualan;
  int stokGratis;
  int stokSample;
  Produk? produk;
  Gudang? gudang;

  int get total => stokPenjualan + stokGratis + stokSample;
}
```

### Kontak

```dart
class Kontak {
  int id;
  String kodeKontak;      // Auto: KT{id}
  String nama;
  String? email;
  String? noTelp;         // Format: 628xxx
  String? pin;            // 6 digit
  String? alamat;
  double? diskonPersen;
  int? gudangId;
  Gudang? gudang;
}
```

### Penjualan

```dart
class Penjualan {
  int id;
  String uuid;
  String nomor;                  // INV-YYYYMMDD-USERID-NOURUT
  int userId;
  int? approverId;
  int? gudangId;
  String tipeHarga;              // retail / grosir
  String pelanggan;
  String? email;
  String? alamatPenagihan;
  String? koordinat;
  String tglTransaksi;
  String? tglJatuhTempo;
  String syaratPembayaran;       // Cash, Net 7, Net 14, Net 30, Net 60
  String? noReferensi;
  String? tag;
  String? memo;
  List<String>? lampiranPaths;
  String status;                 // Pending, Approved, Lunas, Canceled
  double diskonAkhir;
  double taxPercentage;
  double grandTotal;
  int noUrutHarian;
  User? user;
  User? approver;
  Gudang? gudang;
  List<PenjualanItem> items;
  String? createdAt;
  String? updatedAt;

  String get publicUrl =>
    'https://sales.hibiscusefsya.com/public/invoice/penjualan/$uuid';

  double get subtotal => items.fold(0, (sum, i) => sum + i.jumlahBaris);
  double get taxAmount => (subtotal - diskonAkhir) * (taxPercentage / 100);
}
```

### PenjualanItem

```dart
class PenjualanItem {
  int id;
  int penjualanId;
  int? produkId;
  String? deskripsi;
  double kuantitas;
  String? unit;
  double hargaSatuan;
  double diskon;             // Persentase
  double jumlahBaris;        // qty × harga × (1-disc/100)
  Produk? produk;
}
```

### Pembelian

```dart
class Pembelian {
  // Mirip Penjualan + tambahan:
  int id;
  String uuid;
  String nomor;              // PR-YYYYMMDD-USERID-NOURUT
  int userId;
  int? approverId;
  int? gudangId;
  String tglTransaksi;
  String? tglJatuhTempo;
  String syaratPembayaran;
  String? urgensi;           // Rendah, Sedang, Tinggi
  String? tahunAnggaran;
  String? tag;
  String? koordinat;
  String? memo;
  List<String>? lampiranPaths;
  String status;
  double diskonAkhir;
  double taxPercentage;
  double grandTotal;
  List<PembelianItem> items;
  // TIDAK ADA: pelanggan, email, alamatPenagihan, noReferensi, tipeHarga
}
```

### PembelianItem

```dart
class PembelianItem {
  int id;
  int pembelianId;
  int? produkId;
  String? deskripsi;
  double kuantitas;
  String? unit;
  double hargaSatuan;
  double diskon;
  double jumlahBaris;
  Produk? produk;
}
```

### Biaya

```dart
class Biaya {
  int id;
  String uuid;
  String nomor;              // EXP-YYYYMMDD-USERID-NOURUT
  int userId;
  int? approverId;
  String jenisBiaya;         // masuk / keluar
  String? bayarDari;
  String? penerima;
  String? alamatPenagihan;
  String tglTransaksi;
  String? caraPembayaran;    // Tunai, Transfer Bank, Cek & Giro
  String? tag;
  String? koordinat;
  String? memo;
  List<String>? lampiranPaths;
  String status;
  double taxPercentage;
  double grandTotal;
  List<BiayaItem> items;
}
```

### BiayaItem

```dart
class BiayaItem {
  int id;
  int biayaId;
  String? kategori;          // Transportasi, Operasional, dll (teks bebas)
  String? deskripsi;
  double jumlah;             // Nominal Rupiah (BUKAN qty × harga)
}
```

### Kunjungan

```dart
class Kunjungan {
  int id;
  String uuid;
  String nomor;              // VST-YYYYMMDD-USERID-NOURUT
  int userId;
  int? approverId;
  int? gudangId;
  int? kontakId;
  String? salesNama;
  String? salesEmail;
  String? salesAlamat;
  String tglKunjungan;       // Selalu hari pembuatan, locked
  String tujuan;             // Pemeriksaan Stock, Penagihan, Promo Gratis, Promo Sample
  String? koordinat;
  String? memo;
  List<String>? lampiranPaths;
  String status;
  Kontak? kontak;
  List<KunjunganItem> items; // Kosong jika tujuan = Penagihan
}
```

### KunjunganItem

```dart
class KunjunganItem {
  int id;
  int kunjunganId;
  int? produkId;
  int jumlah;
  String? keterangan;
  Produk? produk;
}
```

### Pembayaran

```dart
class Pembayaran {
  int id;
  String uuid;
  String nomor;              // PAY-YYYYMMDD-USERID-NOURUT
  int userId;
  int? approverId;
  int? gudangId;
  int? penjualanId;
  String tglPembayaran;
  String metodePembayaran;   // Cash, Transfer Bank, Giro, QRIS, Lainnya
  double jumlahBayar;
  String? buktiBayar;
  List<String>? lampiranPaths;
  String? keterangan;
  String status;
  Penjualan? penjualan;
}
```

### PenerimaanBarang

```dart
class PenerimaanBarang {
  int id;
  String uuid;
  String nomor;              // RCV-YYYYMMDD-USERID-NOURUT
  int userId;
  int? approverId;
  int? gudangId;
  int? pembelianId;
  String tglPenerimaan;
  String? noSuratJalan;
  List<String>? lampiranPaths;
  String? keterangan;
  String status;
  Pembelian? pembelian;
  List<PenerimaanBarangItem> items;
}
```

### PenerimaanBarangItem

```dart
class PenerimaanBarangItem {
  int id;
  int penerimaanBarangId;
  int? produkId;
  int qtyDiterima;
  int qtyReject;
  String tipeStok;           // penjualan, gratis, sample
  String? batchNumber;
  String? expiredDate;
  String? keterangan;
  Produk? produk;
}
```

### StokLog

```dart
class StokLog {
  int id;
  int gudangProdukId;
  int produkId;
  int gudangId;
  int userId;
  String produkNama;
  String gudangNama;
  String userNama;
  int stokSebelum;
  int stokSesudah;
  int selisih;
  String keterangan;
  String createdAt;
}
```

---

## 32. Navigasi & Flow Diagram

### App Flow Utama

```
┌──────┐     ┌───────────┐     ┌──────────┐
│Splash │ ──▶│ Cek Token  │──▶ │ Dashboard │
│Screen │     │ Valid?    │     │          │
│(grad) │     └───────────┘     └──────────┘
└──────┘          │ No              │
                  ▼                 ▼
             ┌─────────┐    ┌─────────────┐
             │  Login   │    │   Drawer    │
             │ (gradient)│    │   Menu      │
             └─────────┘    └─────────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    │              │              │
                    ▼              ▼              ▼
             ┌──────────┐  ┌──────────┐  ┌──────────┐
             │Penjualan │  │Pembelian │  │  Biaya   │
             │  List    │  │  List    │  │  List    │
             └──────────┘  └──────────┘  └──────────┘
                 │
          ┌──────┼───────┐
          │      │       │
          ▼      ▼       ▼
       Create  Detail   Edit
                │      (SA only)
         ┌──────┼──────┐
         │      │      │
         ▼      ▼      ▼
      Approve Print  Share
      Cancel   BT    QR/Email
      Lunas   Struk
```

### Transaction Lifecycle

```
┌──────────┐   Create    ┌──────────┐  Approve   ┌──────────┐
│  (none)  │ ─────────▶ │ Pending  │ ─────────▶ │ Approved │
└──────────┘             └──────────┘             └──────────┘
                              │                        │
                      Cancel  │                Cancel  │  (Penjualan)
                              ▼                        │  Mark Lunas
                         ┌──────────┐                  ▼
                         │ Canceled │            ┌──────────┐
                         └──────────┘            │  Lunas   │
                              │                  └──────────┘
                     Uncancel │
                     (SA only)│
                              ▼
                         ┌──────────┐
                         │ Pending  │
                         └──────────┘
```

### Stock Impact Flow

```
Approve Penerimaan Barang
  │ Per item:
  │ tipe_stok = penjualan → +stok_penjualan
  │ tipe_stok = gratis    → +stok_gratis
  │ tipe_stok = sample    → +stok_sample
  └→ StokLog created

Approve Kunjungan (Promo)
  │ tujuan = Promo Gratis → -stok_gratis
  │ tujuan = Promo Sample → -stok_sample
  └→ StokLog created

Create Penjualan
  │ Per item:
  │ stok_penjualan -= kuantitas
  └→ Validated: qty ≤ stok_penjualan

Manual Stok Update (SA)
  │ Direct set: stok_penjualan, stok_gratis, stok_sample
  └→ StokLog created (sebelum → sesudah + keterangan)
```

### Payment Flow

```
Penjualan (Approved)
  │ grand_total = Rp 10.000.000
  ▼
Pembayaran #1: Rp 3.000.000
  │ Total paid = 3jt < 10jt → Penjualan tetap Approved
  ▼
Pembayaran #2: Rp 7.000.000
  │ Total paid = 10jt ≥ 10jt → Penjualan auto LUNAS ✅
  ▼
Penjualan: Status = Lunas
```

---

## Pola Lintas-Modul (Cross-Cutting Patterns)

| Pattern              | Detail                                                                         |
| -------------------- | ------------------------------------------------------------------------------ |
| **Lampiran**         | Semua transaksi: jpg,jpeg,png,pdf,zip,doc,docx. Max 2MB. Multiple files        |
| **Gudang Selection** | super_admin=dropdown semua, admin/user=hidden (auto current gudang)            |
| **Koordinat GPS**    | Auto-capture via `navigator.geolocation` di create form. Readonly              |
| **Tag**              | Auto-fill `auth().user.name`. Readonly                                         |
| **Tanggal readonly** | `tgl_transaksi`/`tgl_kunjungan` readonly untuk role=user                       |
| **Nomor Otomatis**   | `{PREFIX}-YYYYMMDD-USERID-NOURUT`. Auto-generate. Readonly                     |
| **Edit Akses**       | HANYA super_admin bisa edit transaksi (penjualan, pembelian, biaya, kunjungan) |
| **Tanpa Edit**       | Pembayaran & Penerimaan Barang **TIDAK ADA** form edit                         |
| **Auto-Approve**     | Super Admin: transaksi langsung "Approved" saat create                         |
| **Pending Default**  | Role lain: semua transaksi mulai dari "Pending"                                |

---

## Catatan Implementasi

### Format Rupiah

```dart
String formatRupiah(double amount) {
  final formatter = NumberFormat.currency(
    locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0,
  );
  return formatter.format(amount);
}
// Output: Rp 10.545.000
```

### Format Tanggal

```dart
String formatTanggal(String dateStr) {
  final date = DateTime.parse(dateStr);
  return DateFormat('dd/MM/yyyy', 'id_ID').format(date);
}
// Output: 08/03/2026
```

### Error Handling

```json
// Success
{ "success": true, "message": "...", "data": { ... } }

// Error
{ "success": false, "message": "Pesan error", "errors": { "field": ["Pesan validasi"] } }
```

### Token Expiry

-   Token expires setelah **30 hari**
-   API return 401 → redirect login
-   Pesan: "Sesi Anda telah berakhir, silakan login kembali"

---

> **Dokumen ini mencakup 100% fitur website Hibiscus Efsya POS.**
> Semua form create, edit, field, validasi, API request/response, dan logika bisnis telah didokumentasikan.
> Gunakan sebagai satu-satunya referensi saat membangun aplikasi Flutter.
