# ESC/POS Printer Integration

## Overview
Sistem printing telah diubah untuk menggunakan format ESC/POS yang kompatibel dengan thermal printer. Layout dan isi konten tetap sama dengan format sebelumnya.

## Endpoints yang Tersedia

### 1. Penjualan (Sales)
- **URL**: `/penjualan/{id}/print-json`
- **Route Name**: `penjualan.printJson`
- **Method**: GET

### 2. Pembelian (Purchase)
- **URL**: `/pembelian/{id}/print-json`
- **Route Name**: `pembelian.printJson`
- **Method**: GET

### 3. Biaya (Expense)
- **URL**: `/biaya/{id}/print-json`
- **Route Name**: `biaya.printJson`
- **Method**: GET

## Format Response
Endpoint akan mengembalikan raw ESC/POS commands yang di-encode dengan base64. Content type: `text/plain`

## Cara Implementasi di Frontend

### Contoh 1: Direct Print ke Thermal Printer
```javascript
// Fungsi untuk print menggunakan ESC/POS
async function printEscPos(url) {
    try {
        // Fetch ESC/POS data
        const response = await fetch(url);
        const base64Data = await response.text();
        
        // Decode base64 to raw bytes
        const binaryString = atob(base64Data);
        const bytes = new Uint8Array(binaryString.length);
        for (let i = 0; i < binaryString.length; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        
        // Send to printer via Web USB atau Web Bluetooth
        // Contoh dengan Web Bluetooth:
        const device = await navigator.bluetooth.requestDevice({
            filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }]
        });
        
        const server = await device.gatt.connect();
        const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
        const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
        
        await characteristic.writeValue(bytes);
        
        console.log('Print berhasil!');
    } catch (error) {
        console.error('Error printing:', error);
    }
}

// Penggunaan
document.getElementById('btnPrint').addEventListener('click', function() {
    const penjualanId = 1; // ID penjualan
    printEscPos(`/penjualan/${penjualanId}/print-json`);
});
```

### Contoh 2: Menggunakan Plugin Print Cordova/Capacitor
```javascript
// Untuk mobile app dengan Cordova/Capacitor
function printViaMobile(url) {
    fetch(url)
        .then(response => response.text())
        .then(base64Data => {
            // Decode base64
            const rawData = atob(base64Data);
            
            // Gunakan plugin seperti cordova-plugin-sunmi-inner-printer
            // atau cordova-plugin-thermal-printer
            window.ThermalPrinter.printFormattedText(
                rawData,
                function(success) {
                    console.log('Print berhasil');
                },
                function(error) {
                    console.error('Print gagal:', error);
                }
            );
        });
}
```

### Contoh 3: Print via Android App
Jika menggunakan Android native atau hybrid app, Anda bisa:
```javascript
// Kirim raw ESC/POS ke Android printer service
function printViaAndroid(url) {
    fetch(url)
        .then(response => response.text())
        .then(base64Data => {
            // Panggil Android native method
            if (window.AndroidPrinter) {
                window.AndroidPrinter.printBase64(base64Data);
            }
        });
}
```

### Contoh 4: Save to File untuk Testing
```javascript
// Untuk testing, simpan raw data ke file
function saveEscPosToFile(url, filename) {
    fetch(url)
        .then(response => response.text())
        .then(base64Data => {
            const binaryString = atob(base64Data);
            const bytes = new Uint8Array(binaryString.length);
            for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            
            const blob = new Blob([bytes], { type: 'application/octet-stream' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename || 'print.bin';
            link.click();
        });
}

// Gunakan untuk testing
saveEscPosToFile('/penjualan/1/print-json', 'invoice.bin');
```

## ESC/POS Commands yang Digunakan
Helper class `App\Helpers\EscPosHelper` mengimplementasikan command berikut:

- **ESC @**: Initialize printer
- **ESC a n**: Set alignment (0=left, 1=center, 2=right)
- **ESC E n**: Set bold mode (0=off, 1=on)
- **GS ! n**: Set character size
- **LF**: Line feed
- **GS V m**: Cut paper

## Konten yang Dicetak

### Penjualan
1. Header dengan nama perusahaan dan email
2. "INVOICE PENJUALAN"
3. Nomor invoice, tanggal, pelanggan, gudang, sales, status
4. Approver (jika ada)
5. Daftar item (nama produk, kode, qty, harga, diskon, jumlah)
6. Subtotal, diskon akhir, pajak, grand total
7. Footer dengan ucapan terima kasih

### Pembelian
1. Header dengan nama perusahaan dan email
2. "PERMINTAAN PEMBELIAN"
3. Nomor PR, tanggal, supplier, gudang, sales, status
4. Approver (jika ada)
5. Daftar item (nama produk, kode, qty, harga, diskon, jumlah)
6. Subtotal, diskon akhir, pajak, grand total
7. Footer "Dokumen Internal"

### Biaya
1. Header dengan nama perusahaan dan email
2. "BUKTI PENGELUARAN"
3. Nomor expense, tanggal, penerima, sales, status
4. Approver (jika ada)
5. Daftar item (kategori, deskripsi, jumlah)
6. Subtotal, pajak (jika ada), grand total
7. Footer dengan ucapan terima kasih

## Testing
Untuk testing tanpa printer fisik, Anda bisa:
1. Save output ke file .bin
2. Buka dengan hex editor untuk lihat ESC/POS commands
3. Gunakan emulator printer seperti "Virtual Thermal Printer" atau "EscPos Printer Simulator"

## Troubleshooting

### Printer tidak mencetak
- Pastikan printer dalam keadaan online
- Check koneksi USB/Bluetooth
- Pastikan driver printer sudah terinstall

### Karakter tidak sesuai
- Pastikan encoding UTF-8
- Beberapa printer mungkin perlu konfigurasi character set

### Layout tidak sesuai
- Default width: 32 karakter
- Sesuaikan di `EscPosHelper::separator()` jika perlu

## Customization
Untuk mengubah layout atau format, edit class `App\Helpers\EscPosHelper` atau method `printJson` di masing-masing controller.
