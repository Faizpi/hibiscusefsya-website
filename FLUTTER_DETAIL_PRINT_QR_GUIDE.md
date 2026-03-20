# Flutter Detail Screen: Print Struk, Bluetooth, dan QR

Panduan ini untuk menambah 3 tombol di detail screen Flutter:

-   Cetak Struk
-   Print Bluetooth
-   QR Code Invoice

## 1. Endpoint backend yang sudah disiapkan

Endpoint baru (Bearer token):

-   GET `/api/v1/print/{type}/{id}/qr`
-   GET `/api/v1/print/{type}/{id}/bluetooth`

`type` yang didukung:

-   penjualan
-   pembelian
-   biaya
-   kunjungan
-   pembayaran (qr only)
-   penerimaan-barang (qr only)

Bluetooth saat ini didukung untuk:

-   penjualan
-   pembelian
-   biaya
-   kunjungan

## 2. Alur per tombol

## 2.1 Tombol QR Code

1. Call endpoint `.../qr`.
2. Ambil field `qr_payload` atau `invoice_url`.
3. Render dengan package `qr_flutter`.
4. Optional tombol `Open Invoice` pakai `url_launcher`.

## 2.2 Tombol Print Bluetooth

1. Call endpoint `.../bluetooth`.
2. Response berisi item struk siap format.
3. Format ke ESC/POS command (package `esc_pos_utils_plus` + plugin bluetooth printer).
4. Kirim byte ke printer.

## 2.3 Tombol Cetak Struk

Ada 2 opsi:

-   Opsi A (disarankan mobile): gunakan `invoice_url` dari endpoint QR lalu buka external browser untuk print/share PDF.
-   Opsi B: bangun halaman struk Flutter dari data detail + data bluetooth, lalu pakai package `printing` untuk preview/cetak.

## 3. Integrasi Flutter cepat

File template siap pakai:

-   flutter_templates/detail_print_actions_template.dart

Isi file tersebut mencakup:

-   API call QR data
-   API call Bluetooth data
-   helper open invoice URL
-   config visibility tombol

## 4. Package Flutter yang dibutuhkan

Tambahkan di pubspec.yaml:

-   http
-   url_launcher
-   qr_flutter
-   esc_pos_utils_plus
-   flutter_bluetooth_serial atau plugin bluetooth printer yang kamu pakai
-   printing (opsional jika print PDF/layout langsung di app)

## 5. Contoh UI tombol di detail app bar

-   Icon button `receipt_long` -> Cetak Struk
-   Icon button `bluetooth` -> Print Bluetooth
-   Icon button `qr_code` -> QR Invoice

Semua tombol bisa tampil untuk semua role, karena backend sudah memvalidasi akses per transaksi dan akan return 403 jika tidak berhak.

## 6. Error handling yang wajib

-   401: token expired -> force relogin
-   403: unauthorized -> tampilkan snackbar "Anda tidak punya akses"
-   400 pada bluetooth: tipe belum didukung -> disable tombol bluetooth untuk tipe tersebut

## 7. Catatan penting

-   Endpoint print web lama (`/{module}/{id}/print`) menggunakan session web, bukan bearer token API.
-   Karena itu, Flutter sebaiknya pakai endpoint API baru untuk QR/bluetooth.
-   Untuk struk, gunakan URL invoice public (`/invoice/{type}/{uuid}`) dari endpoint QR.
