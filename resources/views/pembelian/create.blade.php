@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Buat Permintaan Pembelian</h1>
            {{-- TOTAL ATAS (ID: grand-total-display) --}}
            <h3 class="font-weight-bold text-right text-primary" id="grand-total-display">Total Rp0,00</h3>
        </div>

        {{-- ALERT JIKA USER BIASA TIDAK PUNYA GUDANG --}}
        @php
            $userHasGudang = true;
            if (auth()->user()->role == 'user' && !auth()->user()->gudang_id) {
                $userHasGudang = false;
            }
        @endphp

        @if(!$userHasGudang)
            <div class="alert alert-danger font-weight-bold">
                <i class="fas fa-exclamation-triangle"></i> PERHATIAN: Akun Anda belum terhubung ke Gudang manapun. Hubungi
                Super Admin agar akun Anda di-assign ke Gudang sebelum membuat transaksi.
            </div>
        @endif

        {{-- PENAMPIL ERROR VALIDASI --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Gagal Menyimpan!</strong> Periksa input berikut:
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PENAMPIL ERROR SESSION --}}
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card shadow mb-4">
                <div class="card-body">
                    {{-- BAGIAN ATAS FORM --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tgl_transaksi">Tgl. Transaksi *</label>
                                        <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror"
                                            id="tgl_transaksi" name="tgl_transaksi"
                                            value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                                        @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="syarat_pembayaran">Syarat Pembayaran *</label>
                                        <select class="form-control @error('syarat_pembayaran') is-invalid @enderror"
                                            id="syarat_pembayaran" name="syarat_pembayaran" required>
                                            <option value="Cash" {{ old('syarat_pembayaran') == 'Cash' ? 'selected' : '' }}>
                                                Cash</option>
                                            <option value="Net 7" {{ old('syarat_pembayaran') == 'Net 7' ? 'selected' : '' }}>
                                                Net 7 Days</option>
                                            <option value="Net 14" {{ old('syarat_pembayaran') == 'Net 14' ? 'selected' : '' }}>Net 14 Days</option>
                                            <option value="Net 30" {{ old('syarat_pembayaran') == 'Net 30' ? 'selected' : '' }}>Net 30 Days</option>
                                            <option value="Net 60" {{ old('syarat_pembayaran') == 'Net 60' ? 'selected' : '' }}>Net 60 Days</option>
                                        </select>
                                        @error('syarat_pembayaran') <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tgl_jatuh_tempo_display">Jatuh Tempo (Auto)</label>
                                        <input type="text" class="form-control bg-light" id="tgl_jatuh_tempo_display"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_transaksi">No Transaksi (Preview)</label>
                                <input type="text" class="form-control bg-light text-primary font-weight-bold" id="no_transaksi" value="{{ $previewNomor ?? '[Auto]' }}" readonly>
                                <small class="text-muted">Nomor invoice yang akan digenerate</small>
                            </div>
                            <div class="form-group">
                                <label for="urgensi">Urgensi *</label>
                                <select class="form-control @error('urgensi') is-invalid @enderror" id="urgensi"
                                    name="urgensi" required>
                                    <option value="Rendah" {{ old('urgensi') == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                                    <option value="Sedang" {{ old('urgensi', 'Sedang') == 'Sedang' ? 'selected' : '' }}>Sedang
                                    </option>
                                    <option value="Tinggi" {{ old('urgensi') == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                                </select>
                                @error('urgensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- LOGIKA GUDANG --}}
                            <div class="form-group">
                                <label for="gudang_id">Gudang *</label>
                                @if(auth()->user()->role == 'super_admin')
                                    {{-- Super Admin bisa pilih semua gudang --}}
                                    <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id"
                                        name="gudang_id" required>
                                        <option value="">Pilih Gudang...</option>
                                        @foreach($gudangs as $g)
                                            <option value="{{ $g->id }}" {{ old('gudang_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama_gudang }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @elseif(auth()->user()->role == 'admin')
                                    {{-- Admin readonly, hanya gudang yang ditugaskan --}}
                                    @php
                                        $adminGudang = auth()->user()->getCurrentGudang();
                                    @endphp
                                    <input type="text" class="form-control"
                                        value="{{ $adminGudang->nama_gudang ?? 'Admin tidak terhubung ke gudang' }}" readonly>
                                    <input type="hidden" id="gudang_id" name="gudang_id"
                                        value="{{ $adminGudang->id ?? '' }}">
                                    @error('gudang_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    {{-- User Biasa (Readonly) --}}
                                    <input type="text" class="form-control"
                                        value="{{ auth()->user()->gudang->nama_gudang ?? '-' }}" readonly>
                                    <input type="hidden" id="gudang_id" name="gudang_id"
                                        value="{{ auth()->user()->gudang_id }}">
                                    @error('gudang_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="tahun_anggaran">Tahun Anggaran</label>
                                <input type="text" class="form-control @error('tahun_anggaran') is-invalid @enderror"
                                    id="tahun_anggaran" name="tahun_anggaran" value="{{ old('tahun_anggaran') }}">
                            </div>

                            <div class="form-group">
                                <label for="tag">Tag (Pembuat)</label>
                                <input type="text" class="form-control" id="tag" name="tag"
                                    value="{{ auth()->user()->name }}" readonly>
                            </div>

                            {{-- KOORDINAT LOKASI (AUTO) --}}
                            <div class="form-group">
                                <label for="koordinat">Koordinat Lokasi</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="koordinat" name="koordinat"
                                        value="{{ old('koordinat') }}" placeholder="-6.123456, 106.123456" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" id="btn-get-location"
                                            title="Refresh Lokasi">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                        <a href="#" class="btn btn-outline-success" id="btn-open-maps" target="_blank"
                                            title="Buka di Google Maps">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                                <small class="text-muted">Otomatis terisi saat halaman dimuat</small>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL PRODUK (DESKTOP) --}}
                    <div class="table-responsive mt-3 desktop-product-table">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th width="22%">Produk</th>
                                    <th width="3%"></th>
                                    <th>Deskripsi</th>
                                    <th width="10%">Qty</th>
                                    <th width="10%">Unit</th>
                                    <th width="15%">Harga</th>
                                    <th width="10%">Disc%</th>
                                    <th width="15%" class="text-right">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                {{-- REPOPULATE OLD DATA --}}
                                @if(old('produk_id'))
                                    @foreach(old('produk_id') as $index => $oldPid)
                                        <tr>
                                            <td>
                                                <select class="form-control product-select" name="produk_id[]" required>
                                                    <option value="">Pilih...</option>
                                                    @php
                                                        $renderProduks = $produks;
                                                        $oldGudang = old('gudang_id');
                                                        if(auth()->user()->role == 'super_admin' && isset($gudangProduks) && $gudangProduks && $oldGudang){
                                                            $allowedIds = $gudangProduks[$oldGudang] ?? [];
                                                            $renderProduks = $produks->whereIn('id', $allowedIds);
                                                        } elseif(auth()->user()->role == 'super_admin') {
                                                            $renderProduks = collect();
                                                        }
                                                    @endphp
                                                    @foreach($renderProduks as $p)
                                                        <option value="{{ $p->id }}" data-kode="{{ $p->item_code ?? '' }}" data-harga="{{ $p->harga }}"
                                                            data-deskripsi="{{ $p->deskripsi }}" {{ $oldPid == $p->id ? 'selected' : '' }}>
                                                            [{{ $p->item_code }}] {{ $p->nama_produk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center"><button type="button" class="btn btn-outline-info btn-sm btn-scan-produk" title="Scan Barcode"><i class="fas fa-camera"></i></button></td>
                                            <td><input type="text" class="form-control product-desc" name="deskripsi[]"
                                                    value="{{ old('deskripsi.' . $index) }}"></td>
                                            <td><input type="number" class="form-control product-qty" name="kuantitas[]"
                                                    value="{{ old('kuantitas.' . $index) }}" min="1" required></td>
                                            <td>
                                                <select class="form-control" name="unit[]">
                                                    <option value="Pcs" {{ old('unit.' . $index) == 'Pcs' ? 'selected' : '' }}>Pcs
                                                    </option>
                                                    <option value="Box" {{ old('unit.' . $index) == 'Box' ? 'selected' : '' }}>Box
                                                    </option>
                                                    <option value="Karton" {{ old('unit.' . $index) == 'Karton' ? 'selected' : '' }}>
                                                        Karton
                                                    </option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control text-right product-price"
                                                    name="harga_satuan[]" value="{{ old('harga_satuan.' . $index) }}" required></td>
                                            <td><input type="number" class="form-control text-right product-disc" name="diskon[]"
                                                    value="{{ old('diskon.' . $index) }}" min="0"></td>
                                            <td><input type="text" class="form-control text-right product-total" readonly></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-btn">X</button></td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- BARIS DEFAULT --}}
                                    <tr>
                                        <td>
                                            <select class="form-control product-select" name="produk_id[]" required>
                                                <option value="">Pilih...</option>
                                                @php
                                                    $renderProduks = $produks;
                                                    if(auth()->user()->role == 'super_admin') {
                                                        $renderProduks = collect();
                                                    }
                                                @endphp
                                                @foreach($renderProduks as $p)
                                                    <option value="{{ $p->id }}" data-kode="{{ $p->item_code ?? '' }}" data-harga="{{ $p->harga }}"
                                                        data-deskripsi="{{ $p->deskripsi }}">[{{ $p->item_code }}] {{ $p->nama_produk }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-outline-info btn-sm btn-scan-produk" title="Scan Barcode"><i class="fas fa-camera"></i></button></td>
                                        <td><input type="text" class="form-control product-desc" name="deskripsi[]"></td>
                                        <td><input type="number" class="form-control product-qty" name="kuantitas[]" value="1"
                                                min="1" required></td>
                                        <td>
                                            <select class="form-control" name="unit[]">
                                                <option value="Pcs">Pcs</option>
                                                <option value="Box">Box</option>
                                                <option value="Karton">Karton</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control text-right product-price"
                                                name="harga_satuan[]" value="0" required></td>
                                        <td><input type="number" class="form-control text-right product-disc" name="diskon[]"
                                                value="0" min="0"></td>
                                        <td><input type="text" class="form-control text-right product-total" readonly></td>
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- MOBILE CARDS --}}
                    <div class="mobile-product-cards mt-3" id="mobile-product-cards">
                        {{-- Cards akan di-generate via JavaScript --}}
                    </div>

                    <button type="button" class="btn btn-link pl-0" id="add-row-btn">+ Tambah Baris</button>
                    @error('produk_id.*') <div class="text-danger small mt-2">Error: Produk wajib dipilih</div> @enderror
                    @error('kuantitas.*') <div class="text-danger small mt-2">Error: Kuantitas wajib diisi</div> @enderror

                    {{-- TOTAL & PAJAK --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="memo">Memo</label>
                                <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo"
                                    rows="3">{{ old('memo') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="lampiran">Lampiran</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror"
                                        id="lampiran" name="lampiran" data-preview-nomor="{{ $previewNomor ?? '' }}">
                                    <label class="custom-file-label" for="lampiran">Pilih file...</label>
                                </div>
                                <div id="lampiran-feedback" class="mt-2" style="display: none;">
                                    <div class="alert alert-info py-2 mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <small>File akan disimpan sebagai: <strong id="lampiran-preview-name"></strong></small>
                                    </div>
                                </div>
                                @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless text-right">
                                <tbody>
                                    <tr>
                                        <td><strong>Subtotal</strong></td>
                                        <td id="subtotal-display">Rp0</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="diskon_akhir_input" class="mb-0"><strong>Diskon Akhir
                                                    (Rp)</strong></label>
                                        </td>
                                        <td>
                                            {{-- ID: diskon_akhir_input --}}
                                            <input type="number" class="form-control text-right" id="diskon_akhir_input"
                                                name="diskon_akhir" value="{{ old('diskon_akhir', 0) }}" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="tax_percentage_input" class="mb-0"><strong>Pajak
                                                    (%)</strong></label>
                                        </td>
                                        <td style="width: 40%;">
                                            {{-- ID: tax_percentage_input --}}
                                            <input type="number" class="form-control text-right" id="tax_percentage_input"
                                                name="tax_percentage" value="{{ old('tax_percentage', 0) }}" min="0"
                                                step="0.01">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah Pajak</td>
                                        <td id="tax-amount-display">Rp0</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="h5"><strong>Grand Total</strong></td>
                                        {{-- ID: grand-total-bottom --}}
                                        <td class="h5" id="grand-total-bottom">Rp0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-right">
                {{-- Tombol Simpan --}}
                @if($userHasGudang || in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                @else
                    <button type="button" class="btn btn-secondary" disabled>Simpan (Non-Aktif)</button>
                @endif
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // JATUH TEMPO
            function updateDueDate() {
                let tgl = document.getElementById('tgl_transaksi').value;
                let term = document.getElementById('syarat_pembayaran').value;
                if (!tgl) return;
                let date = moment(tgl);
                if (term === 'Net 7') date.add(7, 'days');
                else if (term === 'Net 14') date.add(14, 'days');
                else if (term === 'Net 30') date.add(30, 'days');
                else if (term === 'Net 60') date.add(60, 'days');
                document.getElementById('tgl_jatuh_tempo_display').value = date.format('YYYY-MM-DD');
            }
            document.getElementById('tgl_transaksi').addEventListener('change', updateDueDate);
            document.getElementById('syarat_pembayaran').addEventListener('change', updateDueDate);
            updateDueDate();

            // 3. KALKULASI
            const tableBody = document.getElementById('product-table-body');
            const addRowBtn = document.getElementById('add-row-btn');
            const mobileCardsContainer = document.getElementById('mobile-product-cards');
            const taxInput = document.getElementById('tax_percentage_input');
            const discAkhirInput = document.getElementById('diskon_akhir_input');
            const gudangSelect = document.getElementById('gudang_id');

            // Data produk per gudang (untuk admin/super_admin)
            @if(isset($gudangProduks) && $gudangProduks)
                const gudangProduks = @json($gudangProduks);
            @else
                const gudangProduks = null;
            @endif

            // Semua produk dengan data lengkap
            const allProduks = [
                @foreach($produks as $p)
                    { id: {{ $p->id }}, nama: "{{ addslashes($p->nama_produk) }}", harga: {{ $p->harga }}, deskripsi: "{{ addslashes($p->deskripsi ?? '') }}" },
                @endforeach
            ];

            // Function untuk generate options HTML berdasarkan gudang
            function getProductOptionsHtml(gudangId = null) {
                let options = '<option value="">Pilih...</option>';

                // Super admin wajib pilih gudang dulu
                if (gudangProduks && !gudangId) {
                    return options;
                }

                allProduks.forEach(p => {
                    // Jika ada filter gudang dan ada data gudangProduks
                    if (gudangId && gudangProduks && gudangProduks[gudangId]) {
                        // Hanya tampilkan produk yang ada di gudang tersebut
                        if (gudangProduks[gudangId].includes(p.id)) {
                            options += `<option value="${p.id}" data-harga="${p.harga}" data-deskripsi="${p.deskripsi}">${p.nama}</option>`;
                        }
                    } else if (!gudangProduks) {
                        // User/admin - data sudah difilter dari controller
                        options += `<option value="${p.id}" data-harga="${p.harga}" data-deskripsi="${p.deskripsi}">${p.nama}</option>`;
                    }
                });

                return options;
            }

            // Default product options
            let productOptionsHtml = getProductOptionsHtml(gudangSelect ? gudangSelect.value : null);

            // Event listener untuk perubahan gudang (super admin)
            if (gudangSelect && gudangProduks) {
                gudangSelect.addEventListener('change', function() {
                    const selectedGudang = this.value;
                    productOptionsHtml = getProductOptionsHtml(selectedGudang);

                    // Update semua dropdown produk
                    document.querySelectorAll('.product-select').forEach(select => {
                        const currentValue = $(select).val();
                        $(select).select2('destroy');
                        select.innerHTML = productOptionsHtml;
                        if (currentValue) {
                            const optionExists = select.querySelector(`option[value="${currentValue}"]`);
                            if (optionExists) {
                                select.value = currentValue;
                            } else {
                                select.value = '';
                                const row = select.closest('tr');
                                if (row) {
                                    row.querySelector('.product-price').value = 0;
                                    row.querySelector('.product-desc').value = '';
                                    row.querySelector('.product-total').value = 0;
                                }
                            }
                        }
                        initSelect2(select);
                    });

                    syncMobileCards();
                });
            }

            // --- INISIALISASI SELECT2 ---
            function initSelect2(selectElement) {
                $(selectElement).select2({
                    placeholder: 'Cari produk...',
                    allowClear: true,
                    width: '100%'
                }).on('select2:select', function (e) {
                    let option = this.options[this.selectedIndex];
                    let row = this.closest('tr');
                    if (row) {
                        row.querySelector('.product-price').value = option.dataset.harga || 0;
                        row.querySelector('.product-desc').value = option.dataset.deskripsi || '';
                        calculateTotal();
                    }
                });
            }

            // Init Select2 untuk semua dropdown produk yang sudah ada
            $('.product-select').each(function () {
                initSelect2(this);
            });

            function formatRupiah(num) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num); }

            function calculateTotal(skipMobileSync = false) {
                let subtotal = 0;
                tableBody.querySelectorAll('tr').forEach(row => {
                    let qty = parseFloat(row.querySelector('.product-qty').value) || 0;
                    let price = parseFloat(row.querySelector('.product-price').value) || 0;
                    let disc = parseFloat(row.querySelector('.product-disc').value) || 0;
                    let total = (qty * price) * (1 - (disc / 100));
                    row.querySelector('.product-total').value = total.toFixed(0);
                    subtotal += total;
                });

                let diskonAkhir = parseFloat(discAkhirInput.value) || 0;
                let kenaPajak = Math.max(0, subtotal - diskonAkhir);
                let taxPercent = parseFloat(taxInput.value) || 0;
                let taxAmount = kenaPajak * (taxPercent / 100);
                let grandTotal = kenaPajak + taxAmount;

                document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
                document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
                document.getElementById('grand-total-bottom').innerText = formatRupiah(grandTotal);
                document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(grandTotal)}`;

                // Hanya sync mobile cards jika tidak di-skip (untuk mencegah rebuild saat input)
                if (!skipMobileSync) {
                    syncMobileCards();
                }
            }

            // --- MOBILE CARDS SYNC ---
            function syncMobileCards() {
                if (!mobileCardsContainer) return;

                mobileCardsContainer.innerHTML = '';
                const rows = tableBody.querySelectorAll('tr');

                rows.forEach((row, index) => {
                    const select = row.querySelector('.product-select');
                    const desc = row.querySelector('.product-desc').value || '-';
                    const qty = row.querySelector('.product-qty').value || 0;
                    const unit = row.querySelector('select[name="unit[]"]').value || 'Pcs';
                    const price = row.querySelector('.product-price').value || 0;
                    const disc = row.querySelector('.product-disc').value || 0;
                    const total = row.querySelector('.product-total').value || 0;

                    const card = document.createElement('div');
                    card.className = 'product-card-mobile';
                    card.dataset.rowIndex = index;
                    card.innerHTML = `
                        <div class="card-header-mobile">
                            <div class="d-flex flex-grow-1">
                                <select class="form-control product-select-mobile" data-row="${index}">
                                    <option value="">Pilih...</option>
                                    ${productOptionsHtml}
                                </select>
                                <button type="button" class="btn btn-outline-info btn-sm ml-1 btn-scan-produk-mobile" data-row="${index}" title="Scan Barcode">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            ${rows.length > 1 ? `<button type="button" class="btn btn-danger btn-sm ml-1 remove-btn-mobile" data-row="${index}"><i class="fas fa-times"></i></button>` : ''}
                        </div>
                        <div class="card-body-mobile">
                            <div class="field-group full-width">
                                <span class="field-label">Deskripsi</span>
                                <input type="text" class="form-control product-desc-mobile" data-row="${index}" value="${desc}" placeholder="Deskripsi">
                            </div>
                            <div class="field-group">
                                <span class="field-label">Qty</span>
                                <input type="number" class="form-control product-qty-mobile" data-row="${index}" value="${qty}" min="1">
                            </div>
                            <div class="field-group">
                                <span class="field-label">Unit</span>
                                <select class="form-control product-unit-mobile" data-row="${index}">
                                    <option value="Pcs" ${unit === 'Pcs' ? 'selected' : ''}>Pcs</option>
                                    <option value="Box" ${unit === 'Box' ? 'selected' : ''}>Box</option>
                                    <option value="Karton" ${unit === 'Karton' ? 'selected' : ''}>Karton</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <span class="field-label">Harga</span>
                                <input type="number" class="form-control product-price-mobile" data-row="${index}" value="${price}">
                            </div>
                            <div class="field-group">
                                <span class="field-label">Disc%</span>
                                <input type="number" class="form-control product-disc-mobile" data-row="${index}" value="${disc}" min="0" max="100">
                            </div>
                        </div>
                        <div class="total-row">
                            <span class="total-label">Total</span>
                            <span class="total-value">${formatRupiah(total)}</span>
                        </div>
                    `;
                    mobileCardsContainer.appendChild(card);

                    const mobileSelect = card.querySelector('.product-select-mobile');
                    mobileSelect.value = select.value;

                    // Init Select2 untuk mobile product select
                    $(mobileSelect).select2({
                        placeholder: 'Cari produk...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $(card)
                    }).on('select2:select', function (e) {
                        const rowIdx = $(this).data('row');
                        const tableRow = tableBody.querySelectorAll('tr')[rowIdx];
                        if (tableRow) {
                            const opt = e.params.data.element;
                            $(tableRow).find('.product-select').val(e.params.data.id).trigger('change');
                            tableRow.querySelector('.product-price').value = opt?.dataset?.harga || 0;
                            tableRow.querySelector('.product-desc').value = opt?.dataset?.deskripsi || '';
                            calculateRow(tableRow);
                        }
                    });
                });
            }

            // Mobile card event listeners
            if (mobileCardsContainer) {
                mobileCardsContainer.addEventListener('change', function (e) {
                    const rowIndex = e.target.dataset.row;
                    if (!rowIndex) return;
                    const row = tableBody.querySelectorAll('tr')[rowIndex];
                    if (!row) return;
                    const card = e.target.closest('.product-card-mobile');

                    if (e.target.classList.contains('product-select-mobile')) {
                        const tableSelect = row.querySelector('.product-select');
                        tableSelect.value = e.target.value;
                        
                        // Get product data dan auto-fill ke mobile card
                        const selectedOption = e.target.options[e.target.selectedIndex];
                        const harga = selectedOption.dataset.harga || 0;
                        const deskripsi = selectedOption.dataset.deskripsi || '';
                        
                        // Update desktop table
                        row.querySelector('.product-price').value = harga;
                        row.querySelector('.product-desc').value = deskripsi;
                        
                        // Update mobile card langsung
                        if (card) {
                            const priceMobile = card.querySelector('.product-price-mobile');
                            const descMobile = card.querySelector('.product-desc-mobile');
                            if (priceMobile) priceMobile.value = harga;
                            if (descMobile) descMobile.value = deskripsi;
                        }
                        
                        // Calculate total dan update di mobile card
                        calculateTotal(true);
                        if (card) {
                            const totalValue = card.querySelector('.total-value');
                            if (totalValue) {
                                const qty = parseFloat(row.querySelector('.product-qty').value) || 0;
                                const price = parseFloat(row.querySelector('.product-price').value) || 0;
                                const disc = parseFloat(row.querySelector('.product-disc').value) || 0;
                                const total = qty * price - disc;
                                totalValue.textContent = formatRupiah(total);
                            }
                        }
                    }
                    if (e.target.classList.contains('product-unit-mobile')) {
                        row.querySelector('select[name="unit[]"]').value = e.target.value;
                    }
                });

                mobileCardsContainer.addEventListener('input', function (e) {
                    const rowIndex = e.target.dataset.row;
                    if (!rowIndex) return;
                    const row = tableBody.querySelectorAll('tr')[rowIndex];
                    if (!row) return;

                    if (e.target.classList.contains('product-desc-mobile')) {
                        row.querySelector('.product-desc').value = e.target.value;
                    }
                    if (e.target.classList.contains('product-qty-mobile')) {
                        row.querySelector('.product-qty').value = e.target.value;
                        calculateTotal(true); // Skip mobile sync untuk mencegah rebuild
                        // Update total di card ini saja
                        const card = e.target.closest('.product-card-mobile');
                        if (card) {
                            const totalValue = card.querySelector('.total-value');
                            if (totalValue) {
                                totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-total').value) || 0);
                            }
                        }
                    }
                    if (e.target.classList.contains('product-price-mobile')) {
                        row.querySelector('.product-price').value = e.target.value;
                        calculateTotal(true); // Skip mobile sync untuk mencegah rebuild
                        // Update total di card ini saja
                        const card = e.target.closest('.product-card-mobile');
                        if (card) {
                            const totalValue = card.querySelector('.total-value');
                            if (totalValue) {
                                totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-total').value) || 0);
                            }
                        }
                    }
                    if (e.target.classList.contains('product-disc-mobile')) {
                        row.querySelector('.product-disc').value = e.target.value;
                        calculateTotal(true); // Skip mobile sync untuk mencegah rebuild
                        // Update total di card ini saja
                        const card = e.target.closest('.product-card-mobile');
                        if (card) {
                            const totalValue = card.querySelector('.total-value');
                            if (totalValue) {
                                totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-total').value) || 0);
                            }
                        }
                    }
                });

                mobileCardsContainer.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-btn-mobile')) {
                        const rowIndex = e.target.closest('.remove-btn-mobile').dataset.row;
                        const row = tableBody.querySelectorAll('tr')[rowIndex];
                        if (row) {
                            row.remove();
                            calculateTotal();
                        }
                    }
                    
                    // Handle scan produk mobile
                    if (e.target.closest('.btn-scan-produk-mobile')) {
                        const btn = e.target.closest('.btn-scan-produk-mobile');
                        const rowIndex = btn.dataset.row;
                        scanProduk(function(produkId) {
                            // Update select di mobile card
                            const card = mobileCardsContainer.querySelector(`.product-card-mobile[data-row-index="${rowIndex}"]`);
                            if (card) {
                                const select = card.querySelector('.product-select-mobile');
                                if (select) {
                                    $(select).val(produkId).trigger('change');
                                }
                            }
                            // Update select di table row
                            const rows = tableBody.querySelectorAll('tr');
                            if (rows[rowIndex]) {
                                const tableSelect = rows[rowIndex].querySelector('.product-select');
                                if (tableSelect) {
                                    $(tableSelect).val(produkId).trigger('change');
                                }
                            }
                        });
                    }
                });
            }

            // Event Listener Global untuk Kalkulasi
            document.addEventListener('input', function (e) {
                if (e.target.matches('.product-qty, .product-price, .product-disc, #diskon_akhir_input, #tax_percentage_input')) {
                    calculateTotal(true); // Skip mobile sync untuk mencegah rebuild dan keyboard close
                    // Update mobile card values tanpa rebuild
                    if (e.target.matches('.product-qty, .product-price, .product-disc')) {
                        const row = e.target.closest('tr');
                        if (row && mobileCardsContainer) {
                            const rowIndex = Array.from(tableBody.rows).indexOf(row);
                            const card = mobileCardsContainer.querySelector(`.product-card-mobile[data-row-index="${rowIndex}"]`);
                            if (card) {
                                const totalValue = card.querySelector('.total-value');
                                if (totalValue) {
                                    // Calculate row total
                                    const qty = parseFloat(row.querySelector('.product-qty').value) || 0;
                                    const price = parseFloat(row.querySelector('.product-price').value) || 0;
                                    const disc = parseFloat(row.querySelector('.product-disc').value) || 0;
                                    const total = qty * price - disc;
                                    totalValue.textContent = formatRupiah(total);
                                }
                            }
                        }
                    }
                }
            });

            // Autofill Produk
            tableBody.addEventListener('change', function (e) {
                if (e.target.classList.contains('product-select')) {
                    let option = e.target.options[e.target.selectedIndex];
                    let row = e.target.closest('tr');
                    row.querySelector('.product-price').value = option.dataset.harga || 0;
                    row.querySelector('.product-desc').value = option.dataset.deskripsi || '';
                    calculateTotal();
                }
            });

            // Tambah Baris
            const productDropdownHtml = `
                <select class="form-control product-select" name="produk_id[]" required>
                    <option value="">Pilih...</option>
                    @foreach($produks as $p) <option value="{{ $p->id }}" data-harga="{{ $p->harga }}" data-deskripsi="{{ $p->deskripsi }}">{{ $p->nama_produk }}</option> @endforeach
                </select>
            `;

            addRowBtn.addEventListener('click', function () {
                const newRow = tableBody.insertRow();
                newRow.innerHTML = `
                    <td>${productDropdownHtml}</td>
                    <td><input type="text" class="form-control product-desc" name="deskripsi[]"></td>
                    <td><input type="number" class="form-control product-qty" name="kuantitas[]" value="1" min="1" required></td>
                    <td>
                        <select class="form-control" name="unit[]">
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Karton">Karton</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" value="0" required></td>
                    <td><input type="number" class="form-control text-right product-disc" name="diskon[]" value="0" min="0"></td>
                    <td><input type="text" class="form-control text-right product-total" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-btn">X</button></td>
                `;
                // Init Select2 untuk dropdown baru
                initSelect2(newRow.querySelector('.product-select'));
                syncMobileCards();
            });

            // Hapus Baris
            tableBody.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-btn')) {
                    e.target.closest('tr').remove();
                    calculateTotal();
                }
                // Scan barcode produk
                if (e.target.closest('.btn-scan-produk')) {
                    const row = e.target.closest('tr');
                    const select = row.querySelector('.product-select');
                    scanProduk(select);
                }
            });

            // Init - Kalkulasi awal
            calculateTotal();
            syncMobileCards();

            document.querySelectorAll('.custom-file-input').forEach(input => {
                input.addEventListener('change', function (e) {
                    if (e.target.files.length > 0) {
                        e.target.nextElementSibling.innerText = e.target.files[0].name;
                    }
                });
            });

            // --- KOORDINAT LOKASI ---
            const koordinatInput = document.getElementById('koordinat');
            const btnGetLocation = document.getElementById('btn-get-location');
            const btnOpenMaps = document.getElementById('btn-open-maps');

            function updateMapsLink() {
                const coords = koordinatInput.value.trim();
                if (coords && coords.includes(',')) {
                    btnOpenMaps.href = 'https://www.google.com/maps?q=' + coords.replace(' ', '');
                    btnOpenMaps.classList.remove('disabled');
                } else {
                    btnOpenMaps.href = '#';
                    btnOpenMaps.classList.add('disabled');
                }
            }

            function getLocation() {
                if (navigator.geolocation) {
                    btnGetLocation.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const lat = position.coords.latitude.toFixed(6);
                            const lng = position.coords.longitude.toFixed(6);
                            koordinatInput.value = lat + ', ' + lng;
                            btnGetLocation.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                            updateMapsLink();
                        },
                        function (error) {
                            console.log('Location error: ' + error.message);
                            btnGetLocation.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                        }
                    );
                }
            }

            if (btnGetLocation) {
                btnGetLocation.addEventListener('click', getLocation);
            }

            koordinatInput.addEventListener('input', updateMapsLink);
            updateMapsLink();

            // Auto-get location saat halaman load
            if (!koordinatInput.value) {
                getLocation();
            }

            // Lampiran upload feedback
            const lampiranInput = document.getElementById('lampiran');
            const lampiranFeedback = document.getElementById('lampiran-feedback');
            const lampiranPreviewName = document.getElementById('lampiran-preview-name');
            const previewNomor = lampiranInput ? lampiranInput.dataset.previewNomor : '';

            if (lampiranInput) {
                lampiranInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        const file = this.files[0];
                        const extension = file.name.split('.').pop().toLowerCase();
                        const expectedFilename = previewNomor + '.' + extension;
                        
                        lampiranPreviewName.textContent = expectedFilename;
                        lampiranFeedback.style.display = 'block';
                        
                        // Update custom file label
                        const label = this.nextElementSibling;
                        if (label) {
                            label.textContent = file.name;
                        }
                    } else {
                        lampiranFeedback.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endpush

@section('modals')
    @include('partials.barcode-scanner-modal')
@endsection