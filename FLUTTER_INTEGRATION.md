# 🔗 Flutter Mobile App - Panduan Integrasi

## Arsitektur

```
Website Laravel 7 ←── REST API ──→ Flutter Mobile App
      ↕                                    ↕
   Database MySQL              SharedPreferences (cache)
```

---

## 📱 LANGKAH 1: Setup Laravel API Backend

### 1.1 Jalankan Migration
```bash
php artisan migrate
```
Ini akan membuat tabel `personal_access_tokens` untuk menyimpan token autentikasi mobile.

### 1.2 Update CORS (jika needed)
Buka `config/cors.php`, pastikan:
```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],  // Atau domain spesifik
'allowed_headers' => ['*'],
```

### 1.3 Test API
```bash
# Jalankan server lokal
php artisan serve

# Test login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Test authenticated endpoint
curl http://localhost:8000/api/v1/dashboard \
  -H "Authorization: Bearer {TOKEN_DARI_LOGIN}"
```

---

## 📱 LANGKAH 2: Setup Flutter Project

### 2.1 Install Flutter
- Download Flutter SDK: https://flutter.dev/docs/get-started/install
- Pastikan `flutter doctor` tidak ada error

### 2.2 Create Project
```bash
cd flutter_app

# Get dependencies
flutter pub get

# Buat folder assets
mkdir -p assets/images assets/fonts
```

### 2.3 Konfigurasi API URL
Edit file `lib/config/app_config.dart`:
```dart
class AppConfig {
  // Ganti dengan URL website Anda
  static const String baseUrl = 'https://sales.hibiscusefsya.com/api/v1';
  
  // Untuk development:
  // Android Emulator → http://10.0.2.2:8000/api/v1
  // iOS Simulator   → http://localhost:8000/api/v1  
  // Physical Device → http://192.168.x.x:8000/api/v1
}
```

### 2.4 Run
```bash
# Android
flutter run

# iOS
flutter run -d ios

# Build APK
flutter build apk --release
```

---

## 🔐 API Endpoints (v1)

### Authentication
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/v1/login` | Login, mendapat token |
| POST | `/api/v1/logout` | Logout, hapus token |
| GET | `/api/v1/profile` | Profil user & gudang |

### Dashboard
| GET | `/api/v1/dashboard` | Data ringkasan dashboard |

### Penjualan
| GET | `/api/v1/penjualan` | List (paginated, filter status/search) |
| GET | `/api/v1/penjualan/{id}` | Detail + items |
| POST | `/api/v1/penjualan` | Buat baru |
| POST | `/api/v1/penjualan/{id}/approve` | Approve (admin/super_admin) |
| POST | `/api/v1/penjualan/{id}/cancel` | Batalkan |

### Pembelian
| GET | `/api/v1/pembelian` | List |
| GET | `/api/v1/pembelian/{id}` | Detail |
| POST | `/api/v1/pembelian` | Buat baru |
| POST | `/api/v1/pembelian/{id}/approve` | Approve |
| POST | `/api/v1/pembelian/{id}/cancel` | Batalkan |

### Produk & Stok
| GET | `/api/v1/produk` | List produk |
| GET | `/api/v1/produk/{id}` | Detail produk |
| GET | `/api/v1/produk/stok/{gudangId}` | Stok per gudang |

### Kontak/Pelanggan
| GET | `/api/v1/kontak` | List kontak |
| POST | `/api/v1/kontak` | Buat kontak baru |
| PUT | `/api/v1/kontak/{id}` | Update kontak |

### Gudang & Stok
| GET | `/api/v1/gudang` | List gudang user |
| POST | `/api/v1/gudang/switch` | Ganti gudang aktif |
| GET | `/api/v1/gudang/stok` | Cek stok |
| GET | `/api/v1/gudang/stok-log` | Riwayat stok |

### Biaya, Kunjungan, Pembayaran
| GET/POST | `/api/v1/biaya` | List / Buat biaya |
| GET/POST | `/api/v1/kunjungan` | List / Buat kunjungan |
| GET/POST | `/api/v1/pembayaran` | List / Buat pembayaran |

### Headers (semua request authenticated)
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 📁 Struktur Flutter Project

```
flutter_app/
├── lib/
│   ├── main.dart                    # Entry point + provider setup
│   ├── config/
│   │   └── app_config.dart          # Base URL & konstanta
│   ├── models/
│   │   ├── user_model.dart          # User + role
│   │   ├── produk_model.dart        # Produk, Gudang, Stok
│   │   ├── kontak_model.dart        # Kontak/pelanggan
│   │   ├── penjualan_model.dart     # Penjualan + items
│   │   └── pembelian_model.dart     # Pembelian
│   ├── services/
│   │   └── api_service.dart         # HTTP client (GET/POST/PUT)
│   ├── providers/
│   │   ├── auth_provider.dart       # Login/logout/auto-login
│   │   ├── dashboard_provider.dart  # Statistik dashboard
│   │   ├── penjualan_provider.dart  # CRUD penjualan
│   │   ├── pembelian_provider.dart  # CRUD pembelian
│   │   ├── produk_provider.dart     # List produk
│   │   ├── kontak_provider.dart     # CRUD kontak
│   │   └── kunjungan_provider.dart  # CRUD kunjungan
│   ├── screens/
│   │   ├── splash_screen.dart       # Auto-login check
│   │   ├── login_screen.dart        # Login form
│   │   ├── home_screen.dart         # Bottom nav + scaffold
│   │   ├── dashboard_screen.dart    # Stat cards + recent
│   │   ├── penjualan/
│   │   │   ├── penjualan_list_screen.dart
│   │   │   ├── penjualan_detail_screen.dart
│   │   │   └── penjualan_create_screen.dart
│   │   ├── pembelian/
│   │   │   └── pembelian_list_screen.dart
│   │   ├── produk/
│   │   │   └── produk_list_screen.dart
│   │   └── kontak/
│   │       └── kontak_list_screen.dart
│   └── utils/
│       ├── app_theme.dart           # Theme & warna
│       └── formatters.dart          # Format Rp, tanggal
└── pubspec.yaml
```

---

## 🔄 Alur Sinkronisasi

### Login
1. User masukkan email + password di Flutter
2. Flutter POST ke `/api/v1/login`
3. Laravel validate, generate token random 64 char, hash dengan SHA256, simpan ke DB
4. Return plain token ke Flutter
5. Flutter simpan token di SecureStorage

### Transaksi
1. Flutter ambil data dari API → tampilkan di UI
2. User buat penjualan → POST ke API → simpan ke DB yang sama dengan website
3. Website dan mobile selalu baca dari DB yang sama → **otomatis sinkron**

### Role-based UI
- **Sales (user)**: Lihat & buat transaksi sendiri
- **Admin**: Lihat transaksi gudangnya, approve
- **Spectator**: Read-only
- **Super Admin**: Akses semua

---

## ⚙️ File yang Ditambahkan di Laravel

| File | Fungsi |
|------|--------|
| `database/migrations/2026_03_06_000001_create_personal_access_tokens_table.php` | Migration tabel token |
| `app/PersonalAccessToken.php` | Model token |
| `app/Http/Middleware/ApiTokenAuth.php` | Middleware autentikasi API |
| `app/Http/Controllers/Api/AuthController.php` | Login/logout/profile |
| `app/Http/Controllers/Api/DashboardController.php` | Dashboard API |
| `app/Http/Controllers/Api/PenjualanController.php` | CRUD penjualan |
| `app/Http/Controllers/Api/PembelianController.php` | CRUD pembelian |
| `app/Http/Controllers/Api/BiayaController.php` | CRUD biaya |
| `app/Http/Controllers/Api/KunjunganController.php` | CRUD kunjungan |
| `app/Http/Controllers/Api/PembayaranController.php` | CRUD pembayaran |
| `app/Http/Controllers/Api/ProdukController.php` | List produk & stok |
| `app/Http/Controllers/Api/KontakController.php` | CRUD kontak |
| `app/Http/Controllers/Api/GudangController.php` | Gudang & stok |
| `routes/api.php` | API routes v1 |

### File yang Dimodifikasi
| File | Perubahan |
|------|-----------|
| `app/Http/Kernel.php` | Registrasi middleware `api.token` |
