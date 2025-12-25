{{-- Modal Scanner Barcode/QR Code --}}
<div class="modal fade" id="barcodeScannerModal" tabindex="-1" role="dialog" aria-labelledby="barcodeScannerModalLabel" aria-hidden="true">
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
                    <div id="reader" style="width: 100%;"></div>
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

{{-- HTML5 QR Code Library --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// Global scanner instance
let html5QrCode = null;
let currentScanTarget = null; // 'kontak' atau 'produk'
let currentScanCallback = null;

// Data kontak dan produk untuk lookup
const scannerData = {
    kontaks: @json(isset($kontaks) ? $kontaks->map(function($k) { 
        return ['id' => $k->id, 'kode' => $k->kode_kontak, 'nama' => $k->nama]; 
    }) : []),
    produks: @json(isset($produks) ? $produks->map(function($p) { 
        return ['id' => $p->id, 'kode' => $p->item_kode ?? $p->item_code ?? '', 'nama' => $p->item_nama ?? $p->nama_produk]; 
    }) : [])
};

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
    if (html5QrCode) {
        stopScanner();
    }
    
    html5QrCode = new Html5Qrcode("reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrCode.start(
        { facingMode: "environment" }, // Kamera belakang
        config,
        onScanSuccess,
        onScanFailure
    ).catch(err => {
        console.error("Scanner error:", err);
        document.getElementById('error-text').textContent = 'Tidak dapat mengakses kamera. Pastikan browser memiliki izin kamera.';
        document.getElementById('scanner-error').style.display = 'block';
    });
}

function stopScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            html5QrCode = null;
        }).catch(err => {
            console.error("Error stopping scanner:", err);
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    console.log("Scanned:", decodedText);
    
    // Cari data berdasarkan target type
    let foundItem = null;
    const dataList = currentScanTarget === 'kontak' ? scannerData.kontaks : scannerData.produks;
    
    // Cari berdasarkan kode
    foundItem = dataList.find(item => {
        // Cek exact match
        if (item.kode === decodedText) return true;
        // Cek jika QR code mengandung kode
        if (decodedText.includes(item.kode)) return true;
        // Cek jika QR code format "KONTAK\nKode: xxx" atau "PRODUK\nKode: xxx"
        const kodeMatch = decodedText.match(/Kode:\s*([^\n\r]+)/i);
        if (kodeMatch && kodeMatch[1].trim() === item.kode) return true;
        return false;
    });
    
    if (foundItem) {
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
    openBarcodeScanner('kontak', function(item) {
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
    openBarcodeScanner('produk', function(item) {
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
