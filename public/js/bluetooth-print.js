/**
 * Bluetooth Thermal Printer - Client Side Solution
 * 
 * Solusi print langsung dari browser ke printer thermal via Bluetooth.
 * Data dibangun di client-side untuk menghindari masalah transfer data
 * yang tidak lengkap dari server.
 */

class BluetoothThermalPrinter {
    constructor() {
        this.device = null;
        this.characteristic = null;
        this.WIDTH = 32; // Character width for 58mm printer
        
        // ESC/POS Commands
        this.ESC = '\x1B';
        this.GS = '\x1D';
        this.COMMANDS = {
            RESET: '\x1B\x40',
            BOLD_ON: '\x1B\x45\x01',
            BOLD_OFF: '\x1B\x45\x00',
            ALIGN_LEFT: '\x1B\x61\x00',
            ALIGN_CENTER: '\x1B\x61\x01',
            ALIGN_RIGHT: '\x1B\x61\x02',
            CUT: '\x1D\x56\x00',
            FEED: '\n'
        };
    }

    // Format currency to Rupiah
    formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Create divider line
    divider(char = '-') {
        return char.repeat(this.WIDTH) + '\n';
    }

    // Pad string for alignment
    padLine(left, right) {
        const totalLen = left.length + right.length;
        if (totalLen >= this.WIDTH) {
            return left.substring(0, this.WIDTH - right.length - 1) + ' ' + right + '\n';
        }
        return left + ' '.repeat(this.WIDTH - totalLen) + right + '\n';
    }

    // Format info row (Label: Value)
    formatInfoRow(label, value) {
        const cleanValue = String(value || '-').trim() || '-';
        const labelCol = 11;
        const valueCol = this.WIDTH - labelCol - 2;
        
        // Word wrap long values
        const words = cleanValue.split(' ');
        let lines = [];
        let currentLine = '';
        
        words.forEach(word => {
            if ((currentLine + ' ' + word).trim().length <= valueCol) {
                currentLine = (currentLine + ' ' + word).trim();
            } else {
                if (currentLine) lines.push(currentLine);
                currentLine = word.length > valueCol ? word.substring(0, valueCol) : word;
            }
        });
        if (currentLine) lines.push(currentLine);
        
        let output = '';
        lines.forEach((line, i) => {
            if (i === 0) {
                output += label.substring(0, labelCol).padEnd(labelCol) + ': ' + line + '\n';
            } else {
                output += ' '.repeat(labelCol + 2) + line + '\n';
            }
        });
        
        return output;
    }

    // Build receipt content for Penjualan
    buildPenjualanReceipt(data) {
        let receipt = '';
        
        // Header
        receipt += this.COMMANDS.RESET;
        receipt += this.COMMANDS.ALIGN_CENTER;
        receipt += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        receipt += 'INVOICE PENJUALAN\n';
        receipt += this.COMMANDS.ALIGN_LEFT;
        receipt += '\n';
        
        // Info Section
        receipt += this.formatInfoRow('Nomor', data.nomor);
        receipt += this.formatInfoRow('Tanggal', data.tanggal);
        receipt += this.formatInfoRow('Jatuh Tempo', data.jatuh_tempo);
        receipt += this.formatInfoRow('Pembayaran', data.pembayaran);
        receipt += this.formatInfoRow('Pelanggan', data.pelanggan);
        receipt += this.formatInfoRow('Sales', data.sales);
        receipt += this.formatInfoRow('Disetujui', data.approver);
        receipt += this.formatInfoRow('Gudang', data.gudang);
        receipt += this.formatInfoRow('Status', data.status);
        
        // Items
        receipt += this.divider();
        
        data.items.forEach(item => {
            receipt += this.COMMANDS.BOLD_ON + item.nama + this.COMMANDS.BOLD_OFF + '\n';
            receipt += 'Qty: ' + item.qty + ' ' + item.unit + '\n';
            receipt += this.padLine('Harga', this.formatRupiah(item.harga));
            if (item.diskon > 0) {
                receipt += this.padLine('Disc', item.diskon + '%');
            }
            receipt += this.padLine('Jumlah', this.formatRupiah(item.jumlah));
        });
        
        // Totals
        receipt += this.divider();
        receipt += this.padLine('Subtotal', this.formatRupiah(data.subtotal));
        
        if (data.diskon_akhir > 0) {
            receipt += this.padLine('Diskon Akhir', '- ' + this.formatRupiah(data.diskon_akhir));
        }
        
        if (data.tax_percentage > 0) {
            receipt += this.padLine('Pajak (' + data.tax_percentage + '%)', this.formatRupiah(data.pajak));
        }
        
        receipt += this.divider();
        receipt += this.COMMANDS.BOLD_ON;
        receipt += this.padLine('GRAND TOTAL', this.formatRupiah(data.grand_total));
        receipt += this.COMMANDS.BOLD_OFF;
        
        // Footer
        receipt += '\n' + this.divider('=') + '\n';
        receipt += this.COMMANDS.ALIGN_CENTER;
        receipt += 'marketing@hibiscusefsya.com\n';
        receipt += '-- Terima Kasih --\n';
        receipt += '\n\n\n\n';
        
        return receipt;
    }

    // Build receipt content for Pembelian
    buildPembelianReceipt(data) {
        let receipt = '';
        
        // Header
        receipt += this.COMMANDS.RESET;
        receipt += this.COMMANDS.ALIGN_CENTER;
        receipt += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        receipt += 'PERMINTAAN PEMBELIAN\n';
        receipt += this.COMMANDS.ALIGN_LEFT;
        receipt += '\n';
        
        // Info Section
        receipt += this.formatInfoRow('Nomor', data.nomor);
        receipt += this.formatInfoRow('Tanggal', data.tanggal);
        receipt += this.formatInfoRow('Jatuh Tempo', data.jatuh_tempo);
        receipt += this.formatInfoRow('Pembayaran', data.pembayaran);
        receipt += this.formatInfoRow('Vendor', data.vendor);
        receipt += this.formatInfoRow('Sales', data.sales);
        receipt += this.formatInfoRow('Disetujui', data.approver);
        receipt += this.formatInfoRow('Gudang', data.gudang);
        receipt += this.formatInfoRow('Status', data.status);
        
        // Items
        receipt += this.divider();
        
        data.items.forEach(item => {
            receipt += this.COMMANDS.BOLD_ON + item.nama + this.COMMANDS.BOLD_OFF + '\n';
            receipt += 'Qty: ' + item.qty + ' ' + item.unit + '\n';
            receipt += this.padLine('Harga', this.formatRupiah(item.harga));
            if (item.diskon > 0) {
                receipt += this.padLine('Disc', item.diskon + '%');
            }
            receipt += this.padLine('Jumlah', this.formatRupiah(item.jumlah));
        });
        
        // Totals
        receipt += this.divider();
        receipt += this.padLine('Subtotal', this.formatRupiah(data.subtotal));
        
        if (data.diskon_akhir > 0) {
            receipt += this.padLine('Diskon Akhir', '- ' + this.formatRupiah(data.diskon_akhir));
        }
        
        if (data.tax_percentage > 0) {
            receipt += this.padLine('Pajak (' + data.tax_percentage + '%)', this.formatRupiah(data.pajak));
        }
        
        receipt += this.divider();
        receipt += this.COMMANDS.BOLD_ON;
        receipt += this.padLine('GRAND TOTAL', this.formatRupiah(data.grand_total));
        receipt += this.COMMANDS.BOLD_OFF;
        
        // Footer
        receipt += '\n' + this.divider('=') + '\n';
        receipt += this.COMMANDS.ALIGN_CENTER;
        receipt += 'marketing@hibiscusefsya.com\n';
        receipt += '-- Dokumen Internal --\n';
        receipt += '\n\n\n\n';
        
        return receipt;
    }

    // Build receipt content for Biaya
    buildBiayaReceipt(data) {
        let receipt = '';
        
        // Header
        receipt += this.COMMANDS.RESET;
        receipt += this.COMMANDS.ALIGN_CENTER;
        receipt += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        receipt += 'BUKTI PENGELUARAN\n';
        receipt += this.COMMANDS.ALIGN_LEFT;
        receipt += '\n';
        
        // Info Section
        receipt += this.formatInfoRow('Nomor', data.nomor);
        receipt += this.formatInfoRow('Tanggal', data.tanggal);
        receipt += this.formatInfoRow('Pembayaran', data.cara_pembayaran);
        receipt += this.formatInfoRow('Bayar Dari', data.bayar_dari);
        receipt += this.formatInfoRow('Penerima', data.penerima);
        receipt += this.formatInfoRow('Sales', data.sales);
        receipt += this.formatInfoRow('Disetujui', data.approver);
        receipt += this.formatInfoRow('Status', data.status);
        
        // Items
        receipt += this.divider();
        
        data.items.forEach(item => {
            receipt += this.COMMANDS.BOLD_ON + item.kategori + this.COMMANDS.BOLD_OFF + '\n';
            if (item.deskripsi) {
                receipt += 'Ket: ' + item.deskripsi + '\n';
            }
            receipt += this.padLine('Jumlah', this.formatRupiah(item.jumlah));
        });
        
        // Totals
        receipt += this.divider();
        receipt += this.padLine('Subtotal', this.formatRupiah(data.subtotal));
        
        if (data.tax_percentage > 0) {
            receipt += this.padLine('Pajak (' + data.tax_percentage + '%)', this.formatRupiah(data.pajak));
        }
        
        receipt += this.divider();
        receipt += this.COMMANDS.BOLD_ON;
        receipt += this.padLine('GRAND TOTAL', this.formatRupiah(data.grand_total));
        receipt += this.COMMANDS.BOLD_OFF;
        
        // Footer
        receipt += '\n' + this.divider('=') + '\n';
        receipt += this.COMMANDS.ALIGN_CENTER;
        receipt += 'marketing@hibiscusefsya.com\n';
        receipt += '-- Terima Kasih --\n';
        receipt += '\n\n\n\n';
        
        return receipt;
    }

    // Connect to Bluetooth printer
    async connect() {
        if (!navigator.bluetooth) {
            throw new Error('Bluetooth tidak didukung di browser ini. Gunakan Chrome/Edge di Android.');
        }

        try {
            // Request Bluetooth device with various service filters
            this.device = await navigator.bluetooth.requestDevice({
                filters: [
                    { services: ['000018f0-0000-1000-8000-00805f9b34fb'] },
                    { namePrefix: 'POS' },
                    { namePrefix: 'Thermal' },
                    { namePrefix: 'Printer' },
                    { namePrefix: 'RPP' },
                    { namePrefix: 'PT-' },
                    { namePrefix: 'MTP' },
                    { namePrefix: 'BlueTooth' },
                    { namePrefix: 'BT' }
                ],
                optionalServices: [
                    '000018f0-0000-1000-8000-00805f9b34fb',
                    '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                    'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
                ]
            });

            // Connect to GATT server
            const server = await this.device.gatt.connect();
            
            // Try different services
            let service = null;
            const serviceUUIDs = [
                '000018f0-0000-1000-8000-00805f9b34fb',
                '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
            ];
            
            for (const uuid of serviceUUIDs) {
                try {
                    service = await server.getPrimaryService(uuid);
                    break;
                } catch (e) {
                    continue;
                }
            }
            
            if (!service) {
                throw new Error('Tidak dapat menemukan service printer');
            }

            // Try different characteristics
            const characteristicUUIDs = [
                '00002af1-0000-1000-8000-00805f9b34fb',
                '49535343-8841-43f4-a8d4-ecbe34729bb3',
                'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f'
            ];
            
            for (const uuid of characteristicUUIDs) {
                try {
                    this.characteristic = await service.getCharacteristic(uuid);
                    break;
                } catch (e) {
                    continue;
                }
            }
            
            if (!this.characteristic) {
                // Fallback: get all characteristics and use first writable one
                const characteristics = await service.getCharacteristics();
                for (const char of characteristics) {
                    if (char.properties.write || char.properties.writeWithoutResponse) {
                        this.characteristic = char;
                        break;
                    }
                }
            }
            
            if (!this.characteristic) {
                throw new Error('Tidak dapat menemukan characteristic untuk menulis');
            }

            return true;
        } catch (error) {
            console.error('Bluetooth connection error:', error);
            throw error;
        }
    }

    // Print data with chunking for reliability
    async print(content) {
        if (!this.characteristic) {
            throw new Error('Printer tidak terhubung');
        }

        const encoder = new TextEncoder();
        const data = encoder.encode(content);
        
        // Send in smaller chunks with delays for reliability
        const chunkSize = 100; // Smaller chunks for better reliability
        
        for (let i = 0; i < data.byteLength; i += chunkSize) {
            const chunk = data.slice(i, Math.min(i + chunkSize, data.byteLength));
            
            try {
                if (this.characteristic.properties.writeWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                } else {
                    await this.characteristic.writeValue(chunk);
                }
            } catch (error) {
                console.error('Write error at chunk', i / chunkSize, error);
                throw error;
            }
            
            // Add delay between chunks to prevent buffer overflow
            await new Promise(resolve => setTimeout(resolve, 50));
        }
        
        return true;
    }

    // Disconnect from printer
    disconnect() {
        if (this.device && this.device.gatt.connected) {
            this.device.gatt.disconnect();
        }
        this.device = null;
        this.characteristic = null;
    }
}

// Initialize global instance
window.BluetoothPrinter = new BluetoothThermalPrinter();

// Helper function for printing with button feedback
async function printViaBluetooth(button, type, jsonUrl) {
    const originalHtml = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connecting...';
        button.disabled = true;

        // Connect to printer
        await window.BluetoothPrinter.connect();

        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading data...';

        // Fetch JSON data from server
        const response = await fetch(jsonUrl);
        if (!response.ok) {
            throw new Error('Gagal mengambil data dari server');
        }
        const data = await response.json();

        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Printing...';

        // Build and print receipt based on type
        let content = '';
        switch (type) {
            case 'penjualan':
                content = window.BluetoothPrinter.buildPenjualanReceipt(data);
                break;
            case 'pembelian':
                content = window.BluetoothPrinter.buildPembelianReceipt(data);
                break;
            case 'biaya':
                content = window.BluetoothPrinter.buildBiayaReceipt(data);
                break;
            default:
                throw new Error('Tipe tidak dikenali: ' + type);
        }

        await window.BluetoothPrinter.print(content);

        // Success feedback
        button.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
        button.classList.remove('btn-primary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
            button.disabled = false;
        }, 2000);

    } catch (error) {
        console.error('Print error:', error);
        button.innerHTML = '<i class="fas fa-times"></i> Gagal';
        button.classList.remove('btn-primary');
        button.classList.add('btn-danger');
        
        alert('Gagal print via Bluetooth: ' + error.message);
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-danger');
            button.classList.add('btn-primary');
            button.disabled = false;
        }, 2000);
    } finally {
        // Disconnect after printing
        window.BluetoothPrinter.disconnect();
    }
}
