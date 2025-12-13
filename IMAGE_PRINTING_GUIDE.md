# 🖨️ Image Printing System untuk Thermal Printer 58mm

## 📌 Arsitektur
```
HTML (384px) → Browsershot → PNG → iWare Image Mode
```

## 🎯 Endpoint Routes

### Penjualan
```
GET /penjualan/{id}/struk-image
```
Contoh: `https://hibiscusefsya.com/dev/penjualan/123/struk-image`

### Pembelian
```
GET /pembelian/{id}/struk-image
```
Contoh: `https://hibiscusefsya.com/dev/pembelian/123/struk-image`

### Biaya
```
GET /biaya/{id}/struk-image
```
Contoh: `https://hibiscusefsya.com/dev/biaya/123/struk-image`

## 📱 Cara Print di iWare App

1. Buka **iWare** app
2. Pilih mode **Image**
3. Masukkan URL endpoint (contoh di atas)
4. Klik **Print**

## ⚙️ Setup Server (jika belum)

### 1. Install Browsershot
```bash
composer require spatie/browsershot
```

### 2. Install Chrome/Chromium di Server
```bash
# Ubuntu/Debian
sudo apt-get install chromium-browser

# atau
sudo apt-get install google-chrome-stable
```

### 3. Cek Path Chrome
```bash
which chromium-browser
# atau
which google-chrome
```

### 4. Set Chrome Path di Controller (jika perlu)
Edit `app/Http/Controllers/PrintImageController.php`:

```php
Browsershot::html($html)
    ->setChromePath('/usr/bin/chromium-browser')  // sesuaikan path
    ->windowSize(384, 2000)
    ->deviceScaleFactor(2)
    ->fullPage()
    ->save($path);
```

## 🔧 Troubleshooting

### Error: "Could not find Chrome"
Server belum ada Chrome/Chromium. Install dengan:
```bash
sudo apt-get update
sudo apt-get install chromium-browser
```

### Error: Permission denied
Folder storage tidak writable:
```bash
chmod -R 775 storage/app/public
chown -R www-data:www-data storage
```

### Image tidak keluar
Cek apakah file generated:
```bash
ls -la storage/app/public/struk-*.png
```

### Logo tidak muncul
Path logo di blade pakai `public_path()`:
```php
<img src="{{ public_path('assets/img/logoHE1.png') }}" class="logo">
```

## 📝 File Structure

```
resources/views/print/
├── penjualan-image.blade.php  (384px width)
├── pembelian-image.blade.php  (384px width)
└── biaya-image.blade.php      (384px width)

app/Http/Controllers/
└── PrintImageController.php

routes/web.php (3 routes baru)
```

## ✅ Keuntungan Sistem Ini

- ✅ **1 gambar = 1 struk** (tidak akan split jadi 3 halaman)
- ✅ **Cross-platform** (Android & iOS)
- ✅ **Format PNG** yang pasti di-support semua printer thermal
- ✅ **Width 384px** = pas untuk 58mm thermal (203 DPI)
- ✅ **deviceScaleFactor(2)** = kualitas tinggi
- ✅ **Layout tetap sama** seperti yang sudah ada

## 🚀 Next Enhancement (Optional)

1. **Auto-crop whitespace** agar file lebih kecil
2. **Caching** untuk ID yang sama
3. **Queue** untuk heavy load
4. **Auto-cleanup** old PNG files

---
**Dibuat:** 13 Desember 2025  
**Status:** ✅ PRODUCTION READY
