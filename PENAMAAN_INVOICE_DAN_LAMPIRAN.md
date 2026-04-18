# Panduan Penamaan Invoice dan Lampiran

Dokumen ini merangkum aturan penamaan nomor transaksi (invoice) dan nama file lampiran untuk semua menu transaksi di aplikasi.

## 1) Pola Umum

### 1.1 Format nomor transaksi

Format umum:

PREFIX-YYYYMMDD-USERID-NOURUT

Keterangan:

-   PREFIX: kode jenis transaksi
-   YYYYMMDD: tanggal dalam format tahun-bulan-hari
-   USERID: id user pembuat transaksi
-   NOURUT: nomor urut harian, dipad jadi 3 digit (001, 002, dst)

Contoh:

INV-20260320-7-001

### 1.2 Format nama file lampiran

Format umum:

NOMOR_TRANSAKSI-URUT_FILE.EXT

Keterangan:

-   NOMOR_TRANSAKSI: nomor transaksi yang sudah tergenerate
-   URUT_FILE: urutan file lampiran dalam transaksi (1, 2, 3, dst)
-   EXT: ekstensi file asli (jpg, png, pdf, zip, doc, docx, dll)

Contoh:

INV-20260320-7-001-1.jpg
INV-20260320-7-001-2.pdf

## 2) Penamaan per Menu

### 2.1 Penjualan

-   Prefix nomor: INV
-   Format nomor: INV-YYYYMMDD-USERID-NOURUT
-   Format lampiran: INV-YYYYMMDD-USERID-NOURUT-URUT_FILE.EXT
-   Folder lampiran: public/storage/lampiran_penjualan

Referensi kode:

-   app/Penjualan.php:144
-   app/Http/Controllers/PenjualanController.php:267
-   app/Http/Controllers/PenjualanController.php:278

### 2.2 Pembelian

-   Prefix nomor: PR
-   Format nomor: PR-YYYYMMDD-USERID-NOURUT
-   Format lampiran: PR-YYYYMMDD-USERID-NOURUT-URUT_FILE.EXT
-   Folder lampiran: public/storage/lampiran_pembelian

Referensi kode:

-   app/Pembelian.php:148
-   app/Http/Controllers/PembelianController.php:207
-   app/Http/Controllers/PembelianController.php:218

### 2.3 Biaya

-   Prefix nomor: EXP
-   Format nomor: EXP-YYYYMMDD-USERID-NOURUT
-   Format lampiran: EXP-YYYYMMDD-USERID-NOURUT-URUT_FILE.EXT
-   Folder lampiran: public/storage/lampiran_biaya

Referensi kode:

-   app/Biaya.php:118
-   app/Http/Controllers/BiayaController.php:232
-   app/Http/Controllers/BiayaController.php:238
-   app/Http/Controllers/BiayaController.php:249

Catatan khusus Biaya:

-   Pada proses create, bagian YYYYMMDD di nomor memakai tgl_transaksi dari request.
-   Jadi tanggal pada nomor mengikuti tanggal transaksi yang dipilih user.

### 2.4 Kunjungan

-   Prefix nomor: VST
-   Format nomor: VST-YYYYMMDD-USERID-NOURUT
-   Format lampiran: VST-YYYYMMDD-USERID-NOURUT-URUT_FILE.EXT
-   Folder lampiran: public/storage/lampiran_kunjungan

Referensi kode:

-   app/Kunjungan.php:121
-   app/Http/Controllers/KunjunganController.php:337
-   app/Http/Controllers/KunjunganController.php:348

### 2.5 Pembayaran

-   Prefix nomor: PAY
-   Format nomor: PAY-YYYYMMDD-USERID-NOURUT
-   Format lampiran: PAY-YYYYMMDD-USERID-NOURUT-URUT_FILE.EXT
-   Folder lampiran: public/storage/lampiran_pembayaran

Referensi kode:

-   app/Pembayaran.php:83
-   app/Http/Controllers/PembayaranController.php:229
-   app/Http/Controllers/PembayaranController.php:238

### 2.6 Penerimaan Barang

-   Prefix nomor: RCV
-   Format nomor: RCV-YYYYMMDD-USERID-NOURUT
-   Format lampiran: RCV-YYYYMMDD-USERID-NOURUT-URUT_FILE.EXT
-   Folder lampiran: public/storage/lampiran_penerimaan

Referensi kode:

-   app/PenerimaanBarang.php:88
-   app/Http/Controllers/PenerimaanBarangController.php:250
-   app/Http/Controllers/PenerimaanBarangController.php:259

## 3) Cara Hitung Nomor Urut Harian

Nomor urut harian dihitung per user per hari:

1. Hitung jumlah transaksi milik user pada tanggal hari ini berdasarkan created_at.
2. Tambahkan 1.
3. Pad ke 3 digit.

Contoh implementasi:

-   app/Http/Controllers/PenjualanController.php:258
-   app/Http/Controllers/PembelianController.php:198
-   app/Http/Controllers/BiayaController.php:227
-   app/Http/Controllers/KunjunganController.php:326
-   app/Http/Controllers/PembayaranController.php:222
-   app/Http/Controllers/PenerimaanBarangController.php:243

## 4) Perilaku Saat Edit Transaksi

-   Lampiran baru tidak mengganti lampiran lama.
-   Lampiran baru di-append ke lampiran_paths.
-   Nomor urut file dimulai dari jumlah lampiran existing + 1.

Contoh:

-   Jika sudah ada 2 lampiran, file baru pertama saat edit akan memakai suffix -3.

Referensi kode:

-   app/Http/Controllers/PenjualanController.php:509
-   app/Http/Controllers/PembelianController.php:412
-   app/Http/Controllers/BiayaController.php:543
-   app/Http/Controllers/KunjunganController.php:491

## 5) Ringkasan Cepat Prefix

-   Penjualan: INV
-   Pembelian: PR
-   Biaya: EXP
-   Kunjungan: VST
-   Pembayaran: PAY
-   Penerimaan Barang: RCV
