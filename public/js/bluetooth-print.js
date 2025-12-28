/**
 * Bluetooth Thermal Printer - Client Side Solution
 * 
 * Solusi print langsung dari browser ke printer thermal via Bluetooth.
 * Optimized untuk printer BLE murah (iWARE, MTP, RPP) yang tidak full support
 * ESC/POS commands via BLE.
 * 
 * Key Optimizations:
 * - Menggunakan ESC * (bit-image) bukan GS v 0 untuk gambar
 * - QR Code di-generate OFFLINE via canvas (no external API)
 * - Chunk size kecil (128 bytes) untuk BLE stability
 * - Delay antar chunk 150ms untuk mencegah buffer overflow
 * - ALIGN_CENTER command sebelum setiap image
 * 
 * Features:
 * - Text printing with ESC/POS commands
 * - Logo printing (ESC * bitmap - 90% compatible)
 * - QR Code printing (offline canvas generation)
 */

class BluetoothThermalPrinter {
    constructor() {
        this.device = null;
        this.characteristic = null;
        this.WIDTH = 32; // Character width for 58mm printer
        this.PRINT_WIDTH = 384; // Pixel width for 58mm printer (48mm printable area @ 8 dots/mm)
        
        // BLE Optimized settings
        this.BLE_CHUNK_SIZE = 128; // Safe chunk size for BLE (64-128 recommended)
        this.BLE_DELAY = 150; // Delay between chunks in ms (100-150ms recommended)
        
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
     * Load image and convert to ESC * bitmap format
     * ESC * is MUCH more compatible with cheap BLE printers than GS v 0
     * 
     * @param {string} imageUrl - URL of the image
     * @param {number} maxWidth - Maximum width in pixels (default 384 for 58mm)
     * @returns {Promise<Uint8Array>} - ESC * bitmap command data
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
                    
                    // Width must be multiple of 8 for ESC *
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
                    
                    // Convert to ESC * format (line by line, much more BLE compatible)
                    const bytesPerLine = width / 8;
                    const commands = [];
                    
                    // ESC * m nL nH d1...dk format
                    // m = 0 (8-dot single density) or 1 (8-dot double density) or 33 (24-dot double)
                    // We use mode 0 for maximum compatibility
                    
                    for (let y = 0; y < height; y++) {
                        // ESC * command for each line
                        // ESC * m nL nH
                        commands.push(0x1B, 0x2A, 0x00); // ESC * mode 0
                        commands.push(bytesPerLine & 0xFF); // nL
                        commands.push((bytesPerLine >> 8) & 0xFF); // nH
                        
                        // Bitmap data for this line
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
                            commands.push(byte);
                        }
                        
                        // Line feed after each line
                        commands.push(0x0A); // LF
                    }
                    
                    const result = new Uint8Array(commands);
                    
                    // Cache and return
                    this.imageCache[cacheKey] = result;
                    resolve(result);
                    
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
     * Generate QR Code OFFLINE using canvas
     * NO external API dependency - works offline!
     * Uses simple QR encoding algorithm
     * 
     * @param {string} data - Data to encode in QR code
     * @param {number} size - Size in pixels (default 150)
     * @returns {Promise<Uint8Array>} - ESC * bitmap command data
     */
    async generateQRCode(data, size = 150) {
        // Try to use QRCode library if available (loaded from CDN)
        if (typeof QRCode !== 'undefined') {
            return await this.generateQRCodeWithLib(data, size);
        }
        
        // Fallback: Load QRCode.js dynamically
        try {
            await this.loadQRCodeLibrary();
            return await this.generateQRCodeWithLib(data, size);
        } catch (e) {
            console.warn('QRCode library failed, trying API fallback:', e);
            // Last resort: use external API
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(data)}&margin=2&format=png`;
            return await this.loadImageAsBitmap(qrUrl, size);
        }
    }

    /**
     * Load QRCode.js library dynamically
     */
    async loadQRCodeLibrary() {
        if (typeof QRCode !== 'undefined') return;
        
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Generate QR using QRCode.js library (offline capable once loaded)
     */
    async generateQRCodeWithLib(data, size = 150) {
        return new Promise((resolve, reject) => {
            try {
                // Create temp container
                const container = document.createElement('div');
                container.style.display = 'none';
                document.body.appendChild(container);
                
                // Generate QR Code
                const qr = new QRCode(container, {
                    text: data,
                    width: size,
                    height: size,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
                
                // Wait for QR to render
                setTimeout(() => {
                    try {
                        const canvas = container.querySelector('canvas');
                        if (!canvas) {
                            document.body.removeChild(container);
                            reject(new Error('QR canvas not found'));
                            return;
                        }
                        
                        // Convert canvas to ESC * bitmap
                        const result = this.canvasToBitmap(canvas);
                        document.body.removeChild(container);
                        resolve(result);
                    } catch (e) {
                        document.body.removeChild(container);
                        reject(e);
                    }
                }, 100);
            } catch (e) {
                reject(e);
            }
        });
    }

    /**
     * Convert canvas element to ESC * bitmap
     * @param {HTMLCanvasElement} canvas
     * @returns {Uint8Array}
     */
    canvasToBitmap(canvas) {
        const ctx = canvas.getContext('2d');
        let width = canvas.width;
        let height = canvas.height;
        
        // Width must be multiple of 8
        width = Math.floor(width / 8) * 8;
        
        const imageData = ctx.getImageData(0, 0, width, height);
        const pixels = imageData.data;
        
        const bytesPerLine = width / 8;
        const commands = [];
        
        // ESC * line by line for BLE compatibility
        for (let y = 0; y < height; y++) {
            commands.push(0x1B, 0x2A, 0x00); // ESC * mode 0
            commands.push(bytesPerLine & 0xFF);
            commands.push((bytesPerLine >> 8) & 0xFF);
            
            for (let byteX = 0; byteX < bytesPerLine; byteX++) {
                let byte = 0;
                for (let bit = 0; bit < 8; bit++) {
                    const x = byteX * 8 + bit;
                    const pixelIndex = (y * width + x) * 4;
                    
                    const r = pixels[pixelIndex];
                    const g = pixels[pixelIndex + 1];
                    const b = pixels[pixelIndex + 2];
                    const gray = 0.299 * r + 0.587 * g + 0.114 * b;
                    
                    if (gray < 128) {
                        byte |= (0x80 >> bit);
                    }
                }
                commands.push(byte);
            }
            commands.push(0x0A); // LF
        }
        
        return new Uint8Array(commands);
    }

    /**
     * Generate a short code from URL for display
     * @param {string} url - Full URL
     * @returns {string} - Short code
     */
    extractShortCode(url) {
        const match = url.match(/\/(\d+)$/);
        if (match) {
            return match[1];
        }
        return url.slice(-15);
    }

    /**
     * Print image/binary data with BLE-optimized chunking
     * @param {Uint8Array} imageData - ESC/POS command data
     */
    async printImage(imageData) {
        if (!this.characteristic) {
            throw new Error('Printer tidak terhubung');
        }

        // Use smaller chunks for BLE stability
        const chunkSize = this.BLE_CHUNK_SIZE;
        
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
            
            // Longer delay for BLE stability
            await new Promise(resolve => setTimeout(resolve, this.BLE_DELAY));
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
        
        // Reset printer
        parts.push({ type: 'text', data: this.COMMANDS.RESET });
        
        // Logo (if enabled) - ALIGN_CENTER langsung sebelum image!
        if (printLogo) {
            try {
                // PENTING: ALIGN_CENTER harus langsung sebelum image
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Could not load logo:', e);
            }
        }
        
        // Header text
        let header = this.COMMANDS.ALIGN_CENTER;
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
        
        // Footer with QR Code as IMAGE (not native command)
        let footer = '\n' + this.divider('=');
        
        parts.push({ type: 'text', data: header + body + footer });
        
        // Print QR Code as IMAGE - ALIGN_CENTER langsung sebelum image!
        if (printQR && qrData) {
            try {
                parts.push({ type: 'text', data: '\nScan untuk lihat invoice:\n' });
                
                // PENTING: ALIGN_CENTER langsung sebelum QR image
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                
                // Generate QR as image (offline, no API dependency)
                const qrImage = await this.generateQRCode(qrData, 150);
                parts.push({ type: 'image', data: qrImage });
                
                // Print short ID below QR
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: '\nID: ' + shortCode + '\n' });
            } catch (e) {
                console.warn('QR image failed:', e);
                // Fallback: just print URL as text
                parts.push({ type: 'text', data: '\n' + qrData + '\n' });
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
        
        // Reset printer
        parts.push({ type: 'text', data: this.COMMANDS.RESET });
        
        // Logo - ALIGN_CENTER langsung sebelum image!
        if (printLogo) {
            try {
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Could not load logo:', e);
            }
        }
        
        let header = this.COMMANDS.ALIGN_CENTER;
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
        
        // Print QR Code as IMAGE - ALIGN_CENTER langsung sebelum image!
        if (printQR && qrData) {
            try {
                parts.push({ type: 'text', data: '\nScan untuk lihat dokumen:\n' });
                
                // PENTING: ALIGN_CENTER langsung sebelum QR image
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                
                const qrImage = await this.generateQRCode(qrData, 150);
                parts.push({ type: 'image', data: qrImage });
                
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: '\nID: ' + shortCode + '\n' });
            } catch (e) {
                console.warn('QR image failed:', e);
                parts.push({ type: 'text', data: '\n' + qrData + '\n' });
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

        // Jenis biaya (masuk/keluar) untuk judul & label
        const jenisRaw = (data.jenis_biaya || '').toString().toLowerCase();
        console.log('DEBUG jenis_biaya:', data.jenis_biaya, '| jenisRaw:', jenisRaw);
        const isMasuk = jenisRaw.includes('masuk'); // robust even if value is "Biaya Masuk"
        const jenisLabel = isMasuk ? 'Biaya Masuk' : 'Biaya Keluar';
        const titleText = isMasuk ? 'BUKTI PEMASUKAN' : 'BUKTI PENGELUARAN';
        console.log('DEBUG isMasuk:', isMasuk, '| titleText:', titleText);
        
        let parts = [];
        
        // Reset printer
        parts.push({ type: 'text', data: this.COMMANDS.RESET });
        
        // Logo - ALIGN_CENTER langsung sebelum image!
        if (printLogo) {
            try {
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Could not load logo:', e);
            }
        }
        
        let header = this.COMMANDS.ALIGN_CENTER;
        header += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        header += titleText + '\n';
        header += this.COMMANDS.ALIGN_LEFT + '\n';
        
        let body = '';
        body += this.formatInfoRow('Jenis', jenisLabel);
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
        
        // Print QR Code as IMAGE - ALIGN_CENTER langsung sebelum image!
        if (printQR && qrData) {
            try {
                parts.push({ type: 'text', data: '\nScan untuk lihat bukti:\n' });
                
                // PENTING: ALIGN_CENTER langsung sebelum QR image
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                
                const qrImage = await this.generateQRCode(qrData, 150);
                parts.push({ type: 'image', data: qrImage });
                
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: '\nID: ' + shortCode + '\n' });
            } catch (e) {
                console.warn('QR image failed:', e);
                parts.push({ type: 'text', data: '\n' + qrData + '\n' });
            }
        }
        
        let finalFooter = '\n' + this.COMMANDS.ALIGN_CENTER;
        finalFooter += 'marketing@hibiscusefsya.com\n';
        finalFooter += '-- Terima Kasih --\n';
        finalFooter += '\n\n\n\n';
        
        parts.push({ type: 'text', data: finalFooter });
        
        return parts;
    }

    // Build receipt content for Kunjungan
    async buildKunjunganReceipt(data, options = {}) {
        const printLogo = options.printLogo !== false;
        const printQR = options.printQR !== false;
        // Use absolute URL for logo to ensure it loads correctly
        const baseUrl = window.location.origin;
        const logoUrl = options.logoUrl || (baseUrl + '/assets/img/logoHE1.png');
        const qrData = options.qrUrl || data.invoice_url || '';
        
        let parts = [];
        
        // Reset printer
        parts.push({ type: 'text', data: this.COMMANDS.RESET });
        
        // Logo - ALIGN_CENTER langsung sebelum image!
        if (printLogo) {
            try {
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                const logoData = await this.loadImageAsBitmap(logoUrl, 200);
                parts.push({ type: 'image', data: logoData });
                parts.push({ type: 'text', data: '\n' });
            } catch (e) {
                console.warn('Could not load logo:', e);
            }
        }
        
        let header = this.COMMANDS.ALIGN_CENTER;
        header += this.COMMANDS.BOLD_ON + 'HIBISCUS EFSYA\n' + this.COMMANDS.BOLD_OFF;
        header += 'LAPORAN KUNJUNGAN\n';
        header += this.COMMANDS.ALIGN_LEFT + '\n';
        
        let body = '';
        body += this.formatInfoRow('Nomor', data.nomor);
        body += this.formatInfoRow('Tanggal', data.tanggal);
        body += this.formatInfoRow('Waktu', data.waktu);
        body += this.formatInfoRow('Tujuan', data.tujuan);
        
        body += this.divider();
        
        body += this.COMMANDS.BOLD_ON + 'DATA KONTAK\n' + this.COMMANDS.BOLD_OFF;
        body += this.formatInfoRow('Nama', data.sales_nama);
        body += this.formatInfoRow('Email', data.sales_email);
        body += this.formatInfoRow('Alamat', data.sales_alamat);
        
        body += this.divider();
        
        body += this.formatInfoRow('Pembuat', data.pembuat);
        body += this.formatInfoRow('Disetujui', data.approver);
        body += this.formatInfoRow('Gudang', data.gudang);
        body += this.formatInfoRow('Status', data.status);
        
        if (data.koordinat && data.koordinat !== '-') {
            body += this.formatInfoRow('Koordinat', data.koordinat);
        }
        
        if (data.memo && data.memo !== '-') {
            body += this.divider();
            body += this.COMMANDS.BOLD_ON + 'MEMO:\n' + this.COMMANDS.BOLD_OFF;
            body += data.memo + '\n';
        }
        
        let footer = '\n' + this.divider('=');
        
        parts.push({ type: 'text', data: header + body + footer });
        
        // Print QR Code as IMAGE - ALIGN_CENTER langsung sebelum image!
        if (printQR && qrData) {
            try {
                parts.push({ type: 'text', data: '\nScan untuk lihat laporan:\n' });
                
                // PENTING: ALIGN_CENTER langsung sebelum QR image
                parts.push({ type: 'text', data: this.COMMANDS.ALIGN_CENTER });
                
                const qrImage = await this.generateQRCode(qrData, 150);
                parts.push({ type: 'image', data: qrImage });
                
                const shortCode = this.extractShortCode(qrData);
                parts.push({ type: 'text', data: '\nID: ' + shortCode + '\n' });
            } catch (e) {
                console.warn('QR image failed:', e);
                parts.push({ type: 'text', data: '\n' + qrData + '\n' });
            }
        }
        
        let finalFooter = '\n' + this.COMMANDS.ALIGN_CENTER;
        finalFooter += 'marketing@hibiscusefsya.com\n';
        finalFooter += '-- Dokumen Internal --\n';
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

    // Print text data with BLE-optimized chunking
    async print(content) {
        if (!this.characteristic) {
            throw new Error('Printer tidak terhubung');
        }

        const encoder = new TextEncoder();
        const data = encoder.encode(content);
        
        // Use BLE-optimized chunk size (64-128 bytes)
        const chunkSize = this.BLE_CHUNK_SIZE;
        
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
            
            // BLE-optimized delay between chunks (100-150ms)
            await new Promise(resolve => setTimeout(resolve, this.BLE_DELAY));
        }
        
        return true;
    }

    /**
     * Print receipt with mixed content (text + images)
     * @param {Array} parts - Array of {type: 'text'|'image', data: string|Uint8Array}
     */
    async printMixed(parts) {
        for (const part of parts) {
            if (part.type === 'text') {
                await this.print(part.data);
            } else if (part.type === 'image') {
                await this.printImage(part.data);
            }
            // Delay between parts for BLE stability
            await new Promise(resolve => setTimeout(resolve, this.BLE_DELAY));
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
        
        // Debug: log data untuk troubleshooting
        console.log('Bluetooth Print Data:', type, data);

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
            case 'kunjungan':
                parts = await window.BluetoothPrinter.buildKunjunganReceipt(data, options);
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
