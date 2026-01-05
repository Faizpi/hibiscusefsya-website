# Dokumentasi Deployment - Hibiscus Efsya

## 📌 Struktur Branch & Server

| Branch    | Server     | Subdomain               | Database         | Tujuan                |
| --------- | ---------- | ----------------------- | ---------------- | --------------------- |
| `main`    | Production | sales.hibiscusefsya.com | u983003565_sales | Live/Production       |
| `staging` | Testing    | test.hibiscusefsya.com  | u983003565_test  | Development & Testing |

## 🔗 Informasi Server

-   **Host**: 145.79.14.218
-   **Port SSH**: 65002
-   **Username**: u983003565
-   **Path Production**: `/home/u983003565/domains/hibiscusefsya.com/public_html/sales`
-   **Path Staging**: `/home/u983003565/domains/hibiscusefsya.com/public_html/test`

---

## 🚀 Workflow Development

### 1. Development (Lokal)

```bash
# Pastikan di branch staging untuk development
git checkout staging

# Buat perubahan code...

# Commit changes
git add -A
git commit -m "Deskripsi perubahan"

# Push ke remote staging
git push origin staging
```

### 2. Deploy ke Testing Server (test.hibiscusefsya.com)

```bash
# SSH ke server dan deploy staging
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/test && git pull origin staging && php artisan cache:clear"
```

**Jika ada migration baru:**

```bash
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/test && git pull origin staging && php artisan migrate && php artisan cache:clear"
```

### 3. Deploy ke Production (sales.hibiscusefsya.com)

**Opsi A: Merge staging ke main (untuk semua perubahan)**

```bash
# Di lokal
git checkout main
git pull origin main
git merge staging -m "Merge staging to production"
git push origin main

# Deploy ke server
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && git pull origin main && php artisan migrate && php artisan cache:clear"
```

**Opsi B: Cherry-pick commit tertentu (untuk bugfix saja)**

```bash
# Di lokal - ambil commit hash dari staging
git log staging --oneline -5

# Cherry-pick ke main
git checkout main
git cherry-pick <commit-hash>
git push origin main

# Deploy ke server
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && git pull origin main && php artisan cache:clear"
```

---

## 📦 Perintah Artisan di Server

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Migration

```bash
# Jalankan migration baru
php artisan migrate

# Rollback migration terakhir
php artisan migrate:rollback

# Rollback beberapa step
php artisan migrate:rollback --step=2

# Lihat status migration
php artisan migrate:status
```

### Maintenance Mode

```bash
# Aktifkan maintenance mode
php artisan down

# Nonaktifkan maintenance mode
php artisan up
```

---

## ⚠️ Skenario Khusus

### Rollback Deployment di Production

Jika ada kesalahan deploy ke production:

```bash
# 1. Di lokal, reset main ke commit sebelumnya
git checkout main
git log --oneline -5  # Cari commit hash sebelum error
git reset --hard <commit-hash-sebelumnya>
git push origin main --force

# 2. Di server, reset ke origin/main
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && git fetch origin && git reset --hard origin/main && php artisan cache:clear"

# 3. Rollback migration jika perlu
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && php artisan migrate:rollback --step=<jumlah_migration>"
```

### Deploy Bugfix ke Production (Tanpa Fitur Baru di Staging)

```bash
# 1. Commit bugfix di staging
git checkout staging
# ... fix bug ...
git add -A
git commit -m "Fix: deskripsi bug"
git push origin staging

# 2. Deploy ke test dulu
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/test && git pull origin staging && php artisan cache:clear"

# 3. Cherry-pick hanya bugfix ke main (bukan merge)
git checkout main
git cherry-pick <commit-hash-bugfix>
git push origin main

# 4. Deploy ke production
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && git pull origin main && php artisan cache:clear"

# 5. Kembali ke staging
git checkout staging
```

### Sync File Tertentu dari Staging ke Main

```bash
git checkout main
git checkout staging -- path/to/file1.php path/to/file2.blade.php
git commit -m "Sync specific files from staging"
git push origin main
```

---

## 🗄️ Database

### Konfigurasi Database

**Production (.env di sales)**

```
DB_DATABASE=u983003565_sales
DB_USERNAME=u983003565_sales
DB_PASSWORD=<password>
```

**Staging (.env di test)**

```
DB_DATABASE=u983003565_test
DB_USERNAME=u983003565_test
DB_PASSWORD=<password>
```

### Backup Database (via phpMyAdmin atau SSH)

```bash
# Export database
mysqldump -u u983003565_sales -p u983003565_sales > backup_sales_$(date +%Y%m%d).sql

# Import database
mysql -u u983003565_test -p u983003565_test < backup_sales_20260105.sql
```

---

## 📋 Checklist Deployment

### Sebelum Deploy ke Production

-   [ ] Test di staging/test subdomain
-   [ ] Cek tidak ada syntax error
-   [ ] Cek migration berjalan dengan baik
-   [ ] Test fitur utama yang berubah
-   [ ] Backup database production (jika perlu)

### Setelah Deploy ke Production

-   [ ] Clear cache
-   [ ] Test halaman utama
-   [ ] Test fitur yang baru di-deploy
-   [ ] Monitor error log

---

## 🔧 Troubleshooting

### Error: Permission Denied saat git pull

```bash
# Set permission folder
chmod -R 755 /home/u983003565/domains/hibiscusefsya.com/public_html/sales
chown -R u983003565:u983003565 /home/u983003565/domains/hibiscusefsya.com/public_html/sales
```

### Error: Class not found setelah deploy

```bash
composer dump-autoload
php artisan cache:clear
```

### Error: View not found

```bash
php artisan view:clear
php artisan cache:clear
```

### Error: Route not found

```bash
php artisan route:clear
php artisan cache:clear
```

---

## 📝 Catatan Penting

1. **Jangan langsung merge staging ke main** jika ada fitur yang belum siap untuk production
2. **Gunakan cherry-pick** untuk bugfix yang perlu segera di-deploy ke production
3. **Selalu test di staging** sebelum deploy ke production
4. **Backup database** sebelum menjalankan migration yang destructive
5. **Cek error log** setelah deploy: `storage/logs/laravel.log`

---

## 📞 Quick Commands

```bash
# Deploy ke TEST
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/test && git pull origin staging && php artisan cache:clear"

# Deploy ke SALES (dengan migration)
ssh -p 65002 u983003565@145.79.14.218 "cd /home/u983003565/domains/hibiscusefsya.com/public_html/sales && git pull origin main && php artisan migrate && php artisan cache:clear"

# Cek error log
ssh -p 65002 u983003565@145.79.14.218 "tail -50 /home/u983003565/domains/hibiscusefsya.com/public_html/sales/storage/logs/laravel.log"
```
