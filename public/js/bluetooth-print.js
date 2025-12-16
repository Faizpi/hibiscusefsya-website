/**
 * Bluetooth Thermal Printer - Client Side Solution
 * 
 * Solusi print langsung dari browser ke printer thermal via Bluetooth.
 * Data dibangun di client-side untuk menghindari masalah transfer data
 * yang tidak lengkap dari server.
 * 
 * Features:
 * - Text printing with ESC/POS commands
 * - Logo printing (bitmap)
 * - QR Code printing
 */

class BluetoothThermalPrinter {
    constructor() {
        this.device = null;
        this.characteristic = null;
        this.WIDTH = 32; // Character width for 58mm printer
        this.PRINT_WIDTH = 384; // Pixel width for 58mm printer (48mm printable area @ 8 dots/mm)
        
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
        
        // Cache for loaded images
        this.imageCache = {};
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

    /**
     * Load image and convert to bitmap data for ESC/POS
     * @param {string} imageUrl - URL of the image
     * @param {number} maxWidth - Maximum width in pixels (default 384 for 58mm)
     * @returns {Promise<Uint8Array>} - ESC/POS bitmap command data
     */
    async loadImageAsBitmap(imageUrl, maxWidth = null) {
        maxWidth = maxWidth || this.PRINT_WIDTH;
        
        // Check cache
        const cacheKey = imageUrl + '_' + maxWidth;
        if (this.imageCache[cacheKey]) {
            return this.imageCache[cacheKey];
        }

        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            
            img.onload = () => {
                try {
                    // Calculate dimensions (maintain aspect ratio)
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > maxWidth) {
                        height = Math.floor(height * (maxWidth / width));
                        width = maxWidth;
                    }
                    
                    // Width must be multiple of 8 for ESC/POS
                    width = Math.floor(width / 8) * 8;
                    
                    // Create canvas
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    
                    // White background
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, width, height);
                    
                    // Draw image
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Get pixel data
                    const imageData = ctx.getImageData(0, 0, width, height);
                    const pixels = imageData.data;
                    
                    // Convert to monochrome bitmap
                    const bytesPerLine = width / 8;
                    const bitmapData = [];
                    
                    for (let y = 0; y < height; y++) {
                        for (let byteX = 0; byteX < bytesPerLine; byteX++) {
                            let byte = 0;
                            for (let bit = 0; bit < 8; bit++) {
                                const x = byteX * 8 + bit;
                                const pixelIndex = (y * width + x) * 4;
                                
                                // Convert to grayscale
                                const r = pixels[pixelIndex];
                                const g = pixels[pixelIndex + 1];
                                const b = pixels[pixelIndex + 2];
                                const gray = 0.299 * r + 0.587 * g + 0.114 * b;
                                
                                // Threshold (black if < 128)
                                if (gray < 128) {
                                    byte |= (0x80 >> bit);
                                }
                            }
                            bitmapData.push(byte);
                        }
                    }
                    
                    // Build ESC/POS raster bit image command
                    // GS v 0 m xL xH yL yH d1...dk
                    const xL = bytesPerLine & 0xFF;
                    const xH = (bytesPerLine >> 8) & 0xFF;
                    const yL = height & 0xFF;
                    const yH = (height >> 8) & 0xFF;
                    
                    const command = new Uint8Array(8 + bitmapData.length);
                    command[0] = 0x1D; // GS
                    command[1] = 0x76; // v
                    command[2] = 0x30; // 0
                    command[3] = 0x00; // m = 0 (normal mode)
                    command[4] = xL;
                    command[5] = xH;
                    command[6] = yL;
                    command[7] = yH;
                    
                    for (let i = 0; i < bitmapData.length; i++) {
                        command[8 + i] = bitmapData[i];
                    }
                    
                    // Cache and return
                    this.imageCache[cacheKey] = command;
                    resolve(command);
                    
                } catch (error) {
                    reject(error);
                }
            };
            
            img.onerror = () => {
                reject(new Error('Failed to load image: ' + imageUrl));
            };
            
            img.src = imageUrl;
        });
    }

    /**
     * Generate QR Code using ESC/POS native commands (more reliable than bitmap)
     * Uses GS ( k command for QR Code printing
     * @param {string} data - Data to encode in QR code
     * @param {number} size - Module size (1-8, default 4)
     * @returns {Uint8Array} - ESC/POS QR code command data
     */
    generateQRCodeNative(data, size = 4) {
        const encoder = new TextEncoder();
        const dataBytes = encoder.encode(data);
        const dataLen = dataBytes.length;
        
        // QR Code commands array
        const commands = [];
        
        // Function 165: Select QR Code model (Model 2)
        // GS ( k pL pH cn fn n1 n2
        commands.push(0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00);
        
        // Function 167: Set QR Code size (module size 1-8)
        // GS ( k pL pH cn fn n
        commands.push(0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, size);
        
        // Function 169: Set QR Code error correction level (L=48, M=49, Q=50, H=51)
        // GS ( k pL pH cn fn n
        commands.push(0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x31); // M level
        
        // Function 180: Store QR Code data
        // GS ( k pL pH cn fn m d1...dk
        const storeLen = dataLen + 3;
        const pL = storeLen & 0xFF;
        const pH = (storeLen >> 8) & 0xFF;
        commands.push(0x1D, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30);
        for (let i = 0; i < dataBytes.length; i++) {
            commands.push(dataBytes[i]);
        }
        
        // Function 181: Print QR Code
        // GS ( k pL pH cn fn m
        commands.push(0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30);
        
        return new Uint8Array(commands);
    }

    /**
     * Generate Code128 Barcode using ESC/POS native commands
     * More compatible with cheap thermal printers than QR code
     * @param {string} data - Data to encode (max ~20 chars for readability)
     * @param {number} height - Barcode height in dots (default 50)
     * @param {number} width - Barcode width 2-6 (default 2)
     * @returns {Uint8Array} - ESC/POS barcode command data
     */
    generateBarcode128(data, height = 50, width = 2) {
        const encoder = new TextEncoder();
        const dataBytes = encoder.encode(data);
        
        const commands = [];
        
        // GS h n - Set barcode height (n = 1-255 dots)
        commands.push(0x1D, 0x68, height);
        
        // GS w n - Set barcode width (n = 2-6)
        commands.push(0x1D, 0x77, Math.min(6, Math.max(2, width)));
        
        // GS H n - Set HRI (Human Readable Interpretation) position
        // 0 = not printed, 1 = above, 2 = below, 3 = both
        commands.push(0x1D, 0x48, 0x02); // Below barcode
        
        // GS f n - Set HRI font (0 = Font A, 1 = Font B)
        commands.push(0x1D, 0x66, 0x00);
        
        // GS k m n d1...dn - Print barcode
        // m = 73 (Code128), n = data length
        commands.push(0x1D, 0x6B, 73, dataBytes.length);
        for (let i = 0; i < dataBytes.length; i++) {
            commands.push(dataBytes[i]);
        }
        
        return new Uint8Array(commands);
    }

    /**
     * Generate a short code from URL for barcode
     * Extracts just the ID from invoice URL
     * @param {string} url - Full URL
     * @returns {string} - Short code for barcode
     */
    extractShortCode(url) {
        // Extract invoice ID from URL like "https://domain/invoice/penjualan/123"
        const match = url.match(/\/(\d+)$/);
        if (match) {
            return match[1]; // Just return the ID
        }
        // Fallback: return last 15 chars
        return url.slice(-15);
    }

    /**
     * Generate QR Code bitmap data using canvas (fallback method)
     * @param {string} data - Data to encode in QR code
     * @param {number} size - Size in pixels (default 200)
     * @returns {Promise<Uint8Array>} - ESC/POS bitmap command data
     */
    async generateQRCode(data, size = 200) {
        // Use QR Server API to generate QR code
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(data)}&margin=5`;
        return await this.loadImageAsBitmap(qrUrl, size);
    }

    /**
     * Print image/QR code (binary data)
     * @param {Uint8Array} imageData - ESC/POS bitmap command data
     */
    async printImage(imageData) {
        if (!this.characteristic) {
            throw new Error('Printer tidak terhubung');
        }

        const chunkSize = 512; // Larger chunks for image data
        
        for (let i = 0; i < imageData.byteLength; i += chunkSize) {
            const chunk = imageData.slice(i, Math.min(i + chunkSize, imageData.byteLength));
            
            try {
                if (this.characteristic.properties.writeWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                } else {
                    await this.characteristic.writeValue(chunk);
                }
            } catch (error) {
                console.error('Image write error at chunk', i / chunkSize, error);
                throw error;
            }
            
            // Longer delay for image data
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        
        return true;
    }

    // Build receipt content for Penjualan
    async buildPenjualanReceipt(data, options = {}) {
        const printLogo = options.printLogo !== false;
        const printQR = options.printQR !== false;
        // Use absolute URL for logo to ensure it loads correctly
        const baseUrl = window.location.origin;
        const logoUrl = options.logoUrl || (baseUrl + '/assets/img/logoHE1.png');
        const qrData = options.qrUrl || data.invoice_url || '';
        
        let parts = []; // Array of {type: 'text'|'image', data: ...}
        
        // Reset and center align
        let header = this.COMMANDS.RESET + this.COMMANDS.ALIGN_CENTER;
        
        // Logo (if enabled)
        if (printLogo) {
            try {
                parts.push({ type: 'text', data: header });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
                header = this.COMMANDS.ALIGN_CENTER; // Continue centered
            } catch (e) {
                console.warn('Could not load logo:', e);
                // Continue without logo
            }
        }
        
        // Header text
        header += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        header += 'INVOICE PENJUALAN\n';
        header += this.COMMANDS.ALIGN_LEFT + '\n';
        
        // Info Section
        let body = '';
        body += this.formatInfoRow('Nomor', data.nomor);
        body += this.formatInfoRow('Tanggal', data.tanggal);
        body += this.formatInfoRow('Jatuh Tempo', data.jatuh_tempo);
        body += this.formatInfoRow('Pembayaran', data.pembayaran);
        body += this.formatInfoRow('Pelanggan', data.pelanggan);
        body += this.formatInfoRow('Sales', data.sales);
        body += this.formatInfoRow('Disetujui', data.approver);
        body += this.formatInfoRow('Gudang', data.gudang);
        body += this.formatInfoRow('Status', data.status);
        
        // Items
        body += this.divider();
        
        data.items.forEach(item => {
            body += this.COMMANDS.BOLD_ON + item.nama + this.COMMANDS.BOLD_OFF + '\n';
            body += 'Qty: ' + item.qty + ' ' + item.unit + '\n';
            body += this.padLine('Harga', this.formatRupiah(item.harga));
            if (item.diskon > 0) {
                body += this.padLine('Disc', item.diskon + '%');
            }
            body += this.padLine('Jumlah', this.formatRupiah(item.jumlah));
        });
        
        // Totals
        body += this.divider();
        body += this.padLine('Subtotal', this.formatRupiah(data.subtotal));
        
        if (data.diskon_akhir > 0) {
            body += this.padLine('Diskon Akhir', '- ' + this.formatRupiah(data.diskon_akhir));
        }
        
        if (data.tax_percentage > 0) {
            body += this.padLine('Pajak (' + data.tax_percentage + '%)', this.formatRupiah(data.pajak));
        }
        
        body += this.divider();
        body += this.COMMANDS.BOLD_ON;
        body += this.padLine('GRAND TOTAL', this.formatRupiah(data.grand_total));
        body += this.COMMANDS.BOLD_OFF;
        
        // Footer with QR Code
        let footer = '\n' + this.divider('=');
        
        parts.push({ type: 'text', data: header + body + footer });
        
        // QR Code or Barcode (if enabled and URL provided)
        // Try QR first, fallback to barcode if QR fails
        if (printQR && qrData) {
            try {
                // First try QR Code (better for smartphones)
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER + '\nScan untuk invoice:\n' });
                const qrCommand = this.generateQRCodeNative(qrData, 5);
                parts.push({ type: 'binary', data: qrCommand });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('QR failed, trying barcode:', e);
            }
            
            // Also print barcode as fallback (more compatible with cheap printers)
            try {
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const barcodeCommand = this.generateBarcode128(shortCode, 40, 2);
                parts.push({ type: 'binary', data: barcodeCommand });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Barcode also failed:', e);
            }
        }
        
        // Final footer
        let finalFooter = '\n' + this.COMMANDS.ALIGN_CENTER;
        finalFooter += 'marketing@hibiscusefsya.com\n';
        finalFooter += '-- Terima Kasih --\n';
        finalFooter += '\n\n\n\n';
        
        parts.push({ type: 'text', data: finalFooter });
        
        return parts;
    }

    // Build receipt content for Pembelian
    async buildPembelianReceipt(data, options = {}) {
        const printLogo = options.printLogo !== false;
        const printQR = options.printQR !== false;
        // Use absolute URL for logo to ensure it loads correctly
        const baseUrl = window.location.origin;
        const logoUrl = options.logoUrl || (baseUrl + '/assets/img/logoHE1.png');
        const qrData = options.qrUrl || data.invoice_url || '';
        
        let parts = [];
        
        let header = this.COMMANDS.RESET + this.COMMANDS.ALIGN_CENTER;
        
        // Logo
        if (printLogo) {
            try {
                parts.push({ type: 'text', data: header });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
                header = this.COMMANDS.ALIGN_CENTER;
            } catch (e) {
                console.warn('Could not load logo:', e);
            }
        }
        
        header += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        header += 'PERMINTAAN PEMBELIAN\n';
        header += this.COMMANDS.ALIGN_LEFT + '\n';
        
        let body = '';
        body += this.formatInfoRow('Nomor', data.nomor);
        body += this.formatInfoRow('Tanggal', data.tanggal);
        body += this.formatInfoRow('Jatuh Tempo', data.jatuh_tempo);
        body += this.formatInfoRow('Pembayaran', data.pembayaran);
        body += this.formatInfoRow('Vendor', data.vendor);
        body += this.formatInfoRow('Sales', data.sales);
        body += this.formatInfoRow('Disetujui', data.approver);
        body += this.formatInfoRow('Gudang', data.gudang);
        body += this.formatInfoRow('Status', data.status);
        
        body += this.divider();
        
        data.items.forEach(item => {
            body += this.COMMANDS.BOLD_ON + item.nama + this.COMMANDS.BOLD_OFF + '\n';
            body += 'Qty: ' + item.qty + ' ' + item.unit + '\n';
            body += this.padLine('Harga', this.formatRupiah(item.harga));
            if (item.diskon > 0) {
                body += this.padLine('Disc', item.diskon + '%');
            }
            body += this.padLine('Jumlah', this.formatRupiah(item.jumlah));
        });
        
        body += this.divider();
        body += this.padLine('Subtotal', this.formatRupiah(data.subtotal));
        
        if (data.diskon_akhir > 0) {
            body += this.padLine('Diskon Akhir', '- ' + this.formatRupiah(data.diskon_akhir));
        }
        
        if (data.tax_percentage > 0) {
            body += this.padLine('Pajak (' + data.tax_percentage + '%)', this.formatRupiah(data.pajak));
        }
        
        body += this.divider();
        body += this.COMMANDS.BOLD_ON;
        body += this.padLine('GRAND TOTAL', this.formatRupiah(data.grand_total));
        body += this.COMMANDS.BOLD_OFF;
        
        let footer = '\n' + this.divider('=');
        
        parts.push({ type: 'text', data: header + body + footer });
        
        // QR Code or Barcode (if enabled and URL provided)
        if (printQR && qrData) {
            try {
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER + '\nScan untuk invoice:\n' });
                const qrCommand = this.generateQRCodeNative(qrData, 5);
                parts.push({ type: 'binary', data: qrCommand });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('QR failed, trying barcode:', e);
            }
            
            // Barcode fallback
            try {
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const barcodeCommand = this.generateBarcode128(shortCode, 40, 2);
                parts.push({ type: 'binary', data: barcodeCommand });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Barcode also failed:', e);
            }
        }
        
        let finalFooter = '\n' + this.COMMANDS.ALIGN_CENTER;
        finalFooter += 'marketing@hibiscusefsya.com\n';
        finalFooter += '-- Dokumen Internal --\n';
        finalFooter += '\n\n\n\n';
        
        parts.push({ type: 'text', data: finalFooter });
        
        return parts;
    }

    // Build receipt content for Biaya
    async buildBiayaReceipt(data, options = {}) {
        const printLogo = options.printLogo !== false;
        const printQR = options.printQR !== false;
        // Use absolute URL for logo to ensure it loads correctly
        const baseUrl = window.location.origin;
        const logoUrl = options.logoUrl || (baseUrl + '/assets/img/logoHE1.png');
        const qrData = options.qrUrl || data.invoice_url || '';
        
        let parts = [];
        
        let header = this.COMMANDS.RESET + this.COMMANDS.ALIGN_CENTER;
        
        // Logo
        if (printLogo) {
            try {
                parts.push({ type: 'text', data: header });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
                header = this.COMMANDS.ALIGN_CENTER;
            } catch (e) {
                console.warn('Could not load logo:', e);
            }
        }
        
        header += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        header += 'BUKTI PENGELUARAN\n';
        header += this.COMMANDS.ALIGN_LEFT + '\n';
        
        let body = '';
        body += this.formatInfoRow('Nomor', data.nomor);
        body += this.formatInfoRow('Tanggal', data.tanggal);
        body += this.formatInfoRow('Pembayaran', data.cara_pembayaran);
        body += this.formatInfoRow('Bayar Dari', data.bayar_dari);
        body += this.formatInfoRow('Penerima', data.penerima);
        body += this.formatInfoRow('Sales', data.sales);
        body += this.formatInfoRow('Disetujui', data.approver);
        body += this.formatInfoRow('Status', data.status);
        
        body += this.divider();
        
        data.items.forEach(item => {
            body += this.COMMANDS.BOLD_ON + item.kategori + this.COMMANDS.BOLD_OFF + '\n';
            if (item.deskripsi) {
                body += 'Ket: ' + item.deskripsi + '\n';
            }
            body += this.padLine('Jumlah', this.formatRupiah(item.jumlah));
        });
        
        body += this.divider();
        body += this.padLine('Subtotal', this.formatRupiah(data.subtotal));
        
        if (data.tax_percentage > 0) {
            body += this.padLine('Pajak (' + data.tax_percentage + '%)', this.formatRupiah(data.pajak));
        }
        
        body += this.divider();
        body += this.COMMANDS.BOLD_ON;
        body += this.padLine('GRAND TOTAL', this.formatRupiah(data.grand_total));
        body += this.COMMANDS.BOLD_OFF;
        
        let footer = '\n' + this.divider('=');
        
        parts.push({ type: 'text', data: header + body + footer });
        
        // QR Code or Barcode (if enabled and URL provided)
        if (printQR && qrData) {
            try {
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER + '\nScan untuk bukti:\n' });
                const qrCommand = this.generateQRCodeNative(qrData, 5);
                parts.push({ type: 'binary', data: qrCommand });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('QR failed, trying barcode:', e);
            }
            
            // Barcode fallback
            try {
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const barcodeCommand = this.generateBarcode128(shortCode, 40, 2);
                parts.push({ type: 'binary', data: barcodeCommand });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Barcode also failed:', e);
            }
        }
        
        let finalFooter = '\n' + this.COMMANDS.ALIGN_CENTER;
        finalFooter += 'marketing@hibiscusefsya.com\n';
        finalFooter += '-- Terima Kasih --\n';
        finalFooter += '\n\n\n\n';
        
        parts.push({ type: 'text', data: finalFooter });
        
        return parts;
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

    // Print text data with chunking for reliability
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

    /**
     * Print receipt with mixed content (text + images + binary)
     * @param {Array} parts - Array of {type: 'text'|'image'|'binary', data: string|Uint8Array}
     */
    async printMixed(parts) {
        for (const part of parts) {
            if (part.type === 'text') {
                await this.print(part.data);
            } else if (part.type === 'image') {
                await this.printImage(part.data);
            } else if (part.type === 'binary') {
                // Binary data (like native QR code commands)
                await this.printImage(part.data);
            }
            // Small delay between parts
            await new Promise(resolve => setTimeout(resolve, 100));
        }
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
async function printViaBluetooth(button, type, jsonUrl, options = {}) {
    const originalHtml = button.innerHTML;
    
    // Default options - use absolute URL for logo
    const baseUrl = window.location.origin;
    options = {
        printLogo: true,
        printQR: true,
        logoUrl: baseUrl + '/assets/img/logoHE1.png',
        ...options
    };
    
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

        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing...';

        // Build receipt based on type (now async because of image loading)
        let parts = [];
        switch (type) {
            case 'penjualan':
                parts = await window.BluetoothPrinter.buildPenjualanReceipt(data, options);
                break;
            case 'pembelian':
                parts = await window.BluetoothPrinter.buildPembelianReceipt(data, options);
                break;
            case 'biaya':
                parts = await window.BluetoothPrinter.buildBiayaReceipt(data, options);
                break;
            default:
                throw new Error('Tipe tidak dikenali: ' + type);
        }

        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Printing...';

        // Print mixed content (text + images)
        await window.BluetoothPrinter.printMixed(parts);

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
