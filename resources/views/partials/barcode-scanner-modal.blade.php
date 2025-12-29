{{-- Modal Scanner Barcode/QR Code --}}
<div class="modal fade" id="barcodeScannerModal" tabindex="-1" role="dialog" aria-labelledby="barcodeScannerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="barcodeScannerModalLabel">
                    <i class="fas fa-camera"></i> Scan Barcode / QR Code
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <small class="text-muted">Arahkan kamera ke barcode atau QR code</small>
                </div>
                <div id="scanner-container">
                    {{-- Untuk HTML5-QRCode (kontak) --}}
                    <div id="reader" style="width: 100%; display: none;"></div>
                    {{-- Untuk ZXing (produk EAN-13) --}}
                    <video id="zxing-video" style="width: 100%; border-radius: 8px; display: none;"></video>
                </div>
                <div id="scanner-loading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 mb-0 text-muted">Memuat kamera...</p>
                </div>
                <div id="scanner-result" class="mt-3" style="display: none;">
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> <span id="result-text"></span>
                    </div>
                </div>
                <div id="scanner-error" class="mt-3" style="display: none;">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle"></i> <span id="error-text"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Libraries: ZXing (UMD) for EAN-13, HTML5-QR for QR fallback --}}
<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    // Global scanner instance
    let html5QrCode = null;
    let currentScanTarget = null; // 'kontak' atau 'produk'
    let currentScanCallback = null;

    // Data kontak dan produk untuk lookup
    const scannerData = {
        kontaks: @json(isset($kontaks) ? collect($kontaks)->map(function ($k) {
            return ['id' => $k->id, 'kode' => $k->kode_kontak ?? '', 'nama' => $k->nama];
        })->values() : []),
        produks: @json(isset($produks) ? collect($produks)->map(function ($p) {
            return ['id' => $p->id, 'kode' => $p->item_code ?? '', 'nama' => $p->nama_produk ?? ''];
        })->values() : []),
        // Data produk yang tersedia per gudang (untuk penjualan)
        gudangProduks: @json(isset($gudangProduks) ? $gudangProduks : null)
    };

    // Variabel untuk menyimpan gudang yang sedang dipilih
    let currentGudangId = null;

    // Fungsi untuk set gudang yang aktif
    function setCurrentGudang(gudangId) {
        currentGudangId = gudangId;
    }

    // Fungsi untuk membuka scanner
    function openBarcodeScanner(targetType, callback) {
        currentScanTarget = targetType;
        currentScanCallback = callback;

        // Reset UI
        document.getElementById('scanner-result').style.display = 'none';
        document.getElementById('scanner-error').style.display = 'none';

        // Update modal title
        const title = targetType === 'kontak' ? 'Scan Kode Kontak' : 'Scan Kode Produk';
        document.getElementById('barcodeScannerModalLabel').innerHTML = `<i class="fas fa-camera"></i> ${title}`;

        // Show modal
        $('#barcodeScannerModal').modal('show');
    }

    // Inisialisasi scanner saat modal dibuka
    $('#barcodeScannerModal').on('shown.bs.modal', function () {
        startScanner();
    });

    // Stop scanner saat modal ditutup
    $('#barcodeScannerModal').on('hidden.bs.modal', function () {
        stopScanner();
    });

    function startScanner() {
        // Reset tampilan
        document.getElementById('reader').style.display = 'none';
        document.getElementById('zxing-video').style.display = 'none';
        document.getElementById('scanner-loading').style.display = 'block';
        document.getElementById('scanner-error').style.display = 'none';
        document.getElementById('scanner-result').style.display = 'none';

        // Produk: gunakan ZXing untuk EAN-13
        if (currentScanTarget === 'produk') {
            startZxingScanner();
        } else {
            // Kontak: tetap gunakan QR scanner
            startQrScanner();
        }
    }

    function startQrScanner() {
        if (html5QrCode) {
            stopScanner();
        }

        // Tampilkan reader div untuk HTML5-QRCode
        document.getElementById('reader').style.display = 'block';
        document.getElementById('scanner-loading').style.display = 'none';

        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error("Scanner error:", err);
            document.getElementById('error-text').textContent = 'Tidak dapat mengakses kamera. Pastikan browser memiliki izin kamera.';
            document.getElementById('scanner-error').style.display = 'block';
        });
    }

    let zxingReader = null;
    let zxingControls = null;
    function startZxingScanner() {
        // Inisialisasi ZXing untuk EAN-13 dengan pengecekan lingkungan
        const videoElem = document.getElementById('zxing-video');

        try {
            // Cek secure context (HTTPS atau localhost)
            if (!window.isSecureContext) {
                document.getElementById('scanner-loading').style.display = 'none';
                document.getElementById('error-text').textContent = 'Scanner membutuhkan HTTPS. Buka halaman ini via https:// dan beri izin kamera.';
                document.getElementById('scanner-error').style.display = 'block';
                return;
            }

            // Cek library ZXing termuat
            if (typeof ZXing === 'undefined' || !ZXing.BrowserMultiFormatReader) {
                document.getElementById('scanner-loading').style.display = 'none';
                document.getElementById('error-text').textContent = 'Library ZXing tidak termuat. Periksa koneksi internet.';
                document.getElementById('scanner-error').style.display = 'block';
                return;
            }

            // Batasi ke format barcode retail (EAN-13, EAN-8, UPC-A, UPC-E, Code128)
            const hints = new Map();
            hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                ZXing.BarcodeFormat.EAN_13,
                ZXing.BarcodeFormat.EAN_8,
                ZXing.BarcodeFormat.UPC_A,
                ZXing.BarcodeFormat.UPC_E,
                ZXing.BarcodeFormat.CODE_128
            ]);
            hints.set(ZXing.DecodeHintType.TRY_HARDER, true);

            zxingReader = new ZXing.BrowserMultiFormatReader(hints);

            // Langsung minta akses kamera dan mulai decode
            zxingReader.decodeFromConstraints(
                { video: { facingMode: 'environment' } },
                videoElem,
                (result, err) => {
                    if (result) {
                        onScanSuccess(result.getText(), result);
                    }
                    // err biasa terjadi saat tidak ada barcode, abaikan
                }
            ).then(controls => {
                zxingControls = controls;
                // Kamera berhasil dibuka
                document.getElementById('scanner-loading').style.display = 'none';
                videoElem.style.display = 'block';
            }).catch(err => {
                console.error('ZXing camera error', err);
                document.getElementById('scanner-loading').style.display = 'none';
                let msg = 'Tidak dapat mengakses kamera.';
                if (err.name === 'NotAllowedError') {
                    msg = 'Izin kamera ditolak. Silakan izinkan akses kamera di browser.';
                } else if (err.name === 'NotFoundError') {
                    msg = 'Kamera tidak ditemukan di perangkat ini.';
                } else if (err.name === 'NotReadableError') {
                    msg = 'Kamera sedang digunakan aplikasi lain.';
                }
                document.getElementById('error-text').textContent = msg;
                document.getElementById('scanner-error').style.display = 'block';
            });
        } catch (e) {
            console.error('ZXing init error', e);
            document.getElementById('scanner-loading').style.display = 'none';
            document.getElementById('error-text').textContent = 'Scanner barcode tidak tersedia di browser ini.';
            document.getElementById('scanner-error').style.display = 'block';
        }
    }

    function stopScanner() {
        // Stop HTML5-QRCode
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => { html5QrCode = null; }).catch(err => {
                console.error("Error stopping QR scanner:", err);
            });
        }
        // Stop ZXing
        if (zxingControls) {
            try { zxingControls.stop(); } catch (e) {}
            zxingControls = null;
        }
        if (zxingReader) {
            try { zxingReader.reset(); } catch (e) {}
            zxingReader = null;
        }
        // Reset video
        const videoElem = document.getElementById('zxing-video');
        if (videoElem) {
            videoElem.srcObject = null;
            videoElem.style.display = 'none';
        }
        document.getElementById('reader').style.display = 'none';
    }

    function onScanSuccess(decodedText, decodedResult) {
        console.log("Scanned:", decodedText);

        // Normalize scanned text (trim whitespace)
        const scannedCode = decodedText.trim();

        // Cari data berdasarkan target type
        let foundItem = null;
        const dataList = currentScanTarget === 'kontak' ? scannerData.kontaks : scannerData.produks;

        // Cari berdasarkan kode - EXACT MATCH FIRST
        for (let item of dataList) {
            // Skip item tanpa kode
            if (!item.kode || item.kode === '') continue;

            // 1. Cek exact match (prioritas tertinggi)
            if (item.kode === scannedCode) {
                foundItem = item;
                break;
            }
        }

        // Jika tidak exact match, coba parse format QR code
        if (!foundItem) {
            const kodeMatch = scannedCode.match(/Kode:\s*([^\n\r]+)/i);
            if (kodeMatch) {
                const extractedKode = kodeMatch[1].trim();
                for (let item of dataList) {
                    if (!item.kode || item.kode === '') continue;
                    if (item.kode === extractedKode) {
                        foundItem = item;
                        break;
                    }
                }
            }
        }

        if (foundItem) {
            // Untuk produk, cek apakah ada di gudang yang dipilih (jika dalam konteks penjualan)
            if (currentScanTarget === 'produk' && scannerData.gudangProduks && currentGudangId) {
                const produkIdsInGudang = scannerData.gudangProduks[currentGudangId] || [];
                if (!produkIdsInGudang.includes(foundItem.id)) {
                    // Produk tidak ada di gudang yang dipilih
                    document.getElementById('error-text').textContent = `Stok produk "${foundItem.nama}" (${foundItem.kode}) tidak tersedia di gudang yang dipilih.`;
                    document.getElementById('scanner-error').style.display = 'block';
                    document.getElementById('scanner-result').style.display = 'none';
                    return;
                }
            }

            // Success - item ditemukan
            document.getElementById('result-text').textContent = `Ditemukan: ${foundItem.nama} (${foundItem.kode})`;
            document.getElementById('scanner-result').style.display = 'block';
            document.getElementById('scanner-error').style.display = 'none';

            // Panggil callback dengan item yang ditemukan
            if (currentScanCallback) {
                currentScanCallback(foundItem);
            }

            // Auto close modal setelah 1 detik
            setTimeout(() => {
                $('#barcodeScannerModal').modal('hide');
            }, 1000);
        } else {
            // Error - item tidak ditemukan
            document.getElementById('error-text').textContent = `Kode "${decodedText}" tidak ditemukan dalam database.`;
            document.getElementById('scanner-error').style.display = 'block';
            document.getElementById('scanner-result').style.display = 'none';
        }
    }

    function onScanFailure(error) {
        // Ini dipanggil terus-menerus saat tidak ada barcode, jadi tidak perlu log
    }

    // Helper function untuk scan kontak
    function scanKontak(selectElement) {
        openBarcodeScanner('kontak', function (item) {
            // Cari option yang sesuai (bisa by id, nama, atau kode)
            const options = selectElement.options;
            let found = false;

            for (let i = 0; i < options.length; i++) {
                const opt = options[i];
                // Cek by value (bisa id atau nama)
                if (opt.value == item.id || opt.value == item.nama) {
                    selectElement.selectedIndex = i;
                    found = true;
                    break;
                }
                // Cek by data-id atau data-kode
                if (opt.dataset.id == item.id || opt.dataset.kode == item.kode) {
                    selectElement.selectedIndex = i;
                    found = true;
                    break;
                }
            }

            if (found) {
                // Trigger change event
                $(selectElement).trigger('change');
                // Trigger Select2 jika ada
                if ($(selectElement).hasClass('select2-hidden-accessible')) {
                    $(selectElement).trigger('change.select2');
                }
            }
        });
    }

    // Helper function untuk scan produk
    function scanProduk(selectElement) {
        openBarcodeScanner('produk', function (item) {
            // Cari option yang sesuai
            const options = selectElement.options;
            let found = false;

            for (let i = 0; i < options.length; i++) {
                const opt = options[i];
                // Cek by value (id)
                if (opt.value == item.id) {
                    selectElement.selectedIndex = i;
                    found = true;
                    break;
                }
                // Cek by data-kode
                if (opt.dataset.kode == item.kode) {
                    selectElement.selectedIndex = i;
                    found = true;
                    break;
                }
            }

            if (found) {
                // Trigger change event untuk autofill harga dll
                selectElement.dispatchEvent(new Event('change', { bubbles: true }));
                // Trigger Select2 jika ada
                if ($(selectElement).hasClass('select2-hidden-accessible')) {
                    $(selectElement).trigger('change.select2');
                }
            }
        });
    }
</script>

<style>
    #reader {
        border: 2px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }

    #reader video {
        border-radius: 6px;
    }

    #zxing-video {
        border: 2px solid #ddd;
        border-radius: 8px;
        background: #000;
    }

    .btn-scan-barcode {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-scan-barcode:hover {
        background-color: #17a2b8;
        color: white;
    }

    /* Style untuk icon camera di samping dropdown */
    .input-with-scanner {
        position: relative;
    }

    .input-with-scanner .btn-scan {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        z-index: 10;
    }

    .input-with-scanner select,
    .input-with-scanner .select2-container {
        padding-right: 45px !important;
    }

    .input-with-scanner .select2-container .select2-selection {
        padding-right: 40px !important;
    }
</style>