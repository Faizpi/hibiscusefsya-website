@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Permintaan Pembelian #{{ $pembelian->custom_number ?? $pembelian->id }}</h1>
        <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
    </div>

    {{-- Penampil Error Validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pembelian.update', $pembelian->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="card shadow mb-4">
            <div class="card-body">
                {{-- BAGIAN ATAS FORM --}}
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl. Transaksi *</label>
                                    <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', $pembelian->tgl_transaksi->format('Y-m-d')) }}" required>
                                    @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_jatuh_tempo_display">Jatuh Tempo (Auto)</label>
                                    <input type="text" class="form-control bg-light" id="tgl_jatuh_tempo_display" readonly>
                                    <input type="hidden" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo" value="{{ old('tgl_jatuh_tempo', $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                         <div class="form-group">
                            <label for="urgensi">Urgensi *</label>
                            <select class="form-control @error('urgensi') is-invalid @enderror" id="urgensi" name="urgensi" required>
                                <option value="Rendah" {{ old('urgensi', $pembelian->urgensi) == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="Sedang" {{ old('urgensi', $pembelian->urgensi) == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="Tinggi" {{ old('urgensi', $pembelian->urgensi) == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                            @error('urgensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="gudang_id">Gudang *</label>
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id" name="gudang_id" required>
                                    <option value="">Pilih gudang...</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ old('gudang_id', $pembelian->gudang_id) == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama_gudang }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ $pembelian->gudang->nama_gudang ?? '-' }}" readonly>
                                <input type="hidden" name="gudang_id" value="{{ $pembelian->gudang_id }}">
                            @endif
                            @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="syarat_pembayaran">Syarat Pembayaran *</label>
                            <select class="form-control" id="syarat_pembayaran" name="syarat_pembayaran" required>
                                <option value="Cash" {{ old('syarat_pembayaran', $pembelian->syarat_pembayaran) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Net 7" {{ old('syarat_pembayaran', $pembelian->syarat_pembayaran) == 'Net 7' ? 'selected' : '' }}>Net 7 Days</option>
                                <option value="Net 14" {{ old('syarat_pembayaran', $pembelian->syarat_pembayaran) == 'Net 14' ? 'selected' : '' }}>Net 14 Days</option>
                                <option value="Net 30" {{ old('syarat_pembayaran', $pembelian->syarat_pembayaran) == 'Net 30' ? 'selected' : '' }}>Net 30 Days</option>
                                <option value="Net 60" {{ old('syarat_pembayaran', $pembelian->syarat_pembayaran) == 'Net 60' ? 'selected' : '' }}>Net 60 Days</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tahun_anggaran">Tahun Anggaran</label>
                            <input type="text" class="form-control @error('tahun_anggaran') is-invalid @enderror" id="tahun_anggaran" name="tahun_anggaran" value="{{ old('tahun_anggaran', $pembelian->tahun_anggaran) }}">
                            @error('tahun_anggaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tag">Tag</label>
                            <input type="text" class="form-control" id="tag" name="tag" value="{{ old('tag', $pembelian->tag) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Koordinat Lokasi</label>
                            <input type="text" class="form-control bg-light" name="koordinat" value="{{ old('koordinat', $pembelian->koordinat) }}" readonly>
                            @if($pembelian->koordinat)
                                <small class="text-muted">Koordinat diambil saat transaksi dibuat</small>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TABEL PRODUK/JASA (DESKTOP) --}}
                <div class="table-responsive mt-3 desktop-product-table">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 20%;">Produk</th>
                                <th>Deskripsi</th>
                                <th style="width: 8%;">Qty</th>
                                <th style="width: 10%;">Unit</th>
                                <th style="width: 12%;">Harga</th>
                                <th style="width: 10%;">Disc%</th>
                                <th class="text-right" style="width: 15%;">Jumlah</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            {{-- Logic Repopulate: Gunakan 'old' jika ada, jika tidak gunakan data DB --}}
                            @php
                                $items = old('produk_id') ? old('produk_id') : $pembelian->items;
                            @endphp

                            @foreach($items as $index => $item)
                                @php
                                    // Jika dari old (array), ambil via index. Jika dari DB (objek), ambil via properti.
                                    $isOld = old('produk_id') ? true : false;
                                    
                                    $oldProdukId = $isOld ? $item : $item->produk_id;
                                    $oldDeskripsi = $isOld ? old('deskripsi.'.$index) : $item->deskripsi;
                                    $oldKuantitas = $isOld ? old('kuantitas.'.$index) : $item->kuantitas;
                                    $oldUnit = $isOld ? old('unit.'.$index) : $item->unit;
                                    $oldHarga = $isOld ? old('harga_satuan.'.$index) : $item->harga_satuan;
                                    $oldDiskon = $isOld ? old('diskon.'.$index) : $item->diskon;
                                @endphp
                                <tr>
                                    <td>
                                        <select class="form-control product-select" name="produk_id[]" required>
                                            <option value="">Pilih...</option>
                                            @foreach($produks as $produk)
                                                <option value="{{ $produk->id }}" 
                                                        data-harga="{{ $produk->harga }}" 
                                                        data-deskripsi="{{ $produk->deskripsi }}"
                                                        {{ $oldProdukId == $produk->id ? 'selected' : '' }}>
                                                    {{ $produk->nama_produk }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control product-description" name="deskripsi[]" value="{{ $oldDeskripsi }}"></td>
                                    <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="{{ $oldKuantitas }}" min="1" required></td>
                                    <td>
                                        <select class="form-control" name="unit[]">
                                            <option value="Pcs" {{ $oldUnit == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                            <option value="Karton" {{ $oldUnit == 'Karton' ? 'selected' : '' }}>Karton</option>
                                            <option value="Box" {{ $oldUnit == 'Box' ? 'selected' : '' }}>Box</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" value="{{ $oldHarga }}" placeholder="0" required></td>
                                    <td><input type="number" class="form-control text-right product-discount" name="diskon[]" value="{{ $oldDiskon }}" placeholder="0" min="0" max="100"></td>
                                    <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
                                    <td>
                                        @if($index > 0 || count($items) > 1)
                                            <button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS --}}
                <div class="mobile-product-cards mt-3" id="mobile-product-cards">
                    {{-- Cards akan di-generate via JavaScript --}}
                </div>

                <button type="button" class="btn btn-link pl-0" id="add-product-row">+ Tambah Data</button>
                @error('produk_id.*') <div class="text-danger small mt-2">Error di baris Produk: {{ $message }}</div> @enderror
                @error('kuantitas.*') <div class="text-danger small mt-2">Error di baris Kuantitas: {{ $message }}</div> @enderror

                {{-- BAGIAN BAWAH (MEMO & TOTAL) --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="4">{{ old('memo', $pembelian->memo) }}</textarea>
                            @error('memo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="form-group">
                            <label for="lampiran">Lampiran Tambahan <small class="text-muted">(dapat memilih banyak file)</small></label>
                            @php
                                $allLampiran = [];
                                if($pembelian->lampiran_path) {
                                    $allLampiran[] = $pembelian->lampiran_path;
                                }
                                if($pembelian->lampiran_paths) {
                                    $allLampiran = array_merge($allLampiran, $pembelian->lampiran_paths);
                                }
                            @endphp
                            @if(count($allLampiran) > 0)
                                <div class="mb-2">
                                    <small class="text-muted">File saat ini:</small>
                                    <ul class="list-unstyled mb-0 mt-1">
                                        @foreach($allLampiran as $lampiran)
                                            <li><i class="fas fa-file mr-1"></i> <a href="{{ asset('storage/' . $lampiran) }}" target="_blank">{{ basename($lampiran) }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror @error('lampiran.*') is-invalid @enderror" id="lampiran" name="lampiran[]" multiple accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx">
                                <label class="custom-file-label" for="lampiran">Pilih file baru...</label>
                            </div>
                            <div id="lampiran-list" class="mt-2" style="display: none;">
                                <small class="text-muted">File baru terpilih:</small>
                                <ul id="lampiran-file-list" class="list-unstyled mb-0 mt-1"></ul>
                            </div>
                            @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @error('lampiran.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless text-right">
                            <tbody>
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td id="subtotal-display">Rp0,00</td>
                                </tr>
                                <tr>
                                    <td><strong>Diskon Akhir (Rp)</strong></td>
                                    <td>
                                        <input type="number" class="form-control text-right" id="diskon_akhir_input" name="diskon_akhir" value="{{ old('diskon_akhir', $pembelian->diskon_akhir) }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="tax_percentage_input" class="mb-0"><strong>Pajak (%)</strong></label></td>
                                    <td style="width: 50%;">
                                        <input type="number" class="form-control text-right @error('tax_percentage') is-invalid @enderror" 
                                               id="tax_percentage_input" name="tax_percentage" value="{{ old('tax_percentage', $pembelian->tax_percentage) }}" min="0" step="0.01">
                                        @error('tax_percentage') 
                                            <div class="invalid-feedback d-block text-right">{{ $message }}</div> 
                                        @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jumlah Pajak</td>
                                    <td id="tax-amount-display">Rp0,00</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="h5"><strong>Total</strong></td>
                                    <td class="h5" id="grand-total-bottom">Rp0,00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-right">
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('product-table-body');
    const addRowBtn = document.getElementById('add-product-row');
    const mobileCardsContainer = document.getElementById('mobile-product-cards');
    const taxInput = document.getElementById('tax_percentage_input');
    const discAkhirInput = document.getElementById('diskon_akhir_input');

    // Product Options HTML for mobile cards
    const productOptionsHtml = `@foreach($produks as $produk)<option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}">{{ $produk->nama_produk }}</option>@endforeach`;

    // --- JATUH TEMPO AUTO ---
    function updateDueDate() {
        let tgl = document.getElementById('tgl_transaksi').value;
        let term = document.getElementById('syarat_pembayaran').value;
        if(!tgl) return;
        let date = moment(tgl);
        if(term === 'Net 7') date.add(7, 'days');
        else if(term === 'Net 14') date.add(14, 'days');
        else if(term === 'Net 30') date.add(30, 'days');
        else if(term === 'Net 60') date.add(60, 'days');
        document.getElementById('tgl_jatuh_tempo_display').value = date.format('YYYY-MM-DD');
        document.getElementById('tgl_jatuh_tempo').value = date.format('YYYY-MM-DD');
    }
    document.getElementById('tgl_transaksi').addEventListener('change', updateDueDate);
    document.getElementById('syarat_pembayaran').addEventListener('change', updateDueDate);
    updateDueDate();

    const productDropdownHtml = `
        <select class="form-control product-select" name="produk_id[]" required>
            <option value="">Pilih...</option>
            @foreach($produks as $produk)
                <option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}">
                    {{ $produk->nama_produk }}
                </option>
            @endforeach
        </select>
    `;

    // --- INISIALISASI SELECT2 ---
    function initSelect2(selectElement) {
        $(selectElement).select2({
            placeholder: 'Cari produk...',
            allowClear: true,
            width: '100%'
        }).on('select2:select', function(e) {
            let option = this.options[this.selectedIndex];
            let row = this.closest('tr');
            if(row) {
                row.querySelector('.product-price').value = option.dataset.harga || 0;
                row.querySelector('.product-description').value = option.dataset.deskripsi || '';
                calculateRow(row);
            }
        });
    }

    // Init Select2 untuk semua dropdown produk yang sudah ada
    $('.product-select').each(function() {
        initSelect2(this);
    });

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    const calculateRow = (row, skipMobileSync = false) => {
        const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.product-price').value) || 0;
        const discount = parseFloat(row.querySelector('.product-discount').value) || 0;
        const total = quantity * price * (1 - (discount / 100));
        row.querySelector('.product-line-total').value = total.toFixed(0);
        calculateGrandTotal(skipMobileSync);
    };

    const calculateGrandTotal = (skipMobileSync = false) => {
        let subtotal = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const lineTotal = parseFloat(row.querySelector('.product-line-total').value) || 0;
            subtotal += lineTotal;
        });

        let diskonAkhir = parseFloat(discAkhirInput.value) || 0;
        let kenaPajak = subtotal - diskonAkhir;
        if(kenaPajak < 0) kenaPajak = 0;

        let taxPercentage = parseFloat(taxInput.value) || 0;
        let taxAmount = kenaPajak * (taxPercentage / 100);
        const total = kenaPajak + taxAmount;
        
        document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
        document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
        document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
        
        const grandTotalBottom = document.getElementById('grand-total-bottom');
        if(grandTotalBottom) grandTotalBottom.innerText = formatRupiah(total);
        
        // Hanya sync mobile cards jika tidak di-skip (untuk mencegah rebuild saat input)
        if (!skipMobileSync) {
            syncMobileCards();
        }
    };

    // --- MOBILE CARDS SYNC ---
    function syncMobileCards() {
        if (!mobileCardsContainer) return;
        
        mobileCardsContainer.innerHTML = '';
        const rows = tableBody.querySelectorAll('tr');
        
        rows.forEach((row, index) => {
            const select = row.querySelector('.product-select');
            const desc = row.querySelector('.product-description').value || '-';
            const qty = row.querySelector('.product-quantity').value || 0;
            const unit = row.querySelector('select[name="unit[]"]').value || 'Pcs';
            const price = row.querySelector('.product-price').value || 0;
            const disc = row.querySelector('.product-discount').value || 0;
            const total = row.querySelector('.product-line-total').value || 0;
            
            const card = document.createElement('div');
            card.className = 'product-card-mobile';
            card.dataset.rowIndex = index;
            card.innerHTML = `
                <div class="card-header-mobile">
                    <select class="form-control product-select-mobile" data-row="${index}">
                        <option value="">Pilih...</option>
                        ${productOptionsHtml}
                    </select>
                    ${rows.length > 1 ? `<button type="button" class="btn btn-danger btn-sm remove-btn-mobile" data-row="${index}"><i class="fas fa-times"></i></button>` : ''}
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
        });
    }

    // Mobile card event listeners
    if (mobileCardsContainer) {
        mobileCardsContainer.addEventListener('change', function(e) {
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
                row.querySelector('.product-description').value = deskripsi;
                
                // Update mobile card langsung
                if (card) {
                    const priceMobile = card.querySelector('.product-price-mobile');
                    const descMobile = card.querySelector('.product-desc-mobile');
                    if (priceMobile) priceMobile.value = harga;
                    if (descMobile) descMobile.value = deskripsi;
                }
                
                // Calculate total dan update di mobile card
                calculateRow(row, true);
                if (card) {
                    const totalValue = card.querySelector('.total-value');
                    if (totalValue) {
                        totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-line-total').value) || 0);
                    }
                }
            }
            if (e.target.classList.contains('product-unit-mobile')) {
                row.querySelector('select[name="unit[]"]').value = e.target.value;
            }
        });

        mobileCardsContainer.addEventListener('input', function(e) {
            const rowIndex = e.target.dataset.row;
            if (!rowIndex) return;
            const row = tableBody.querySelectorAll('tr')[rowIndex];
            if (!row) return;

            if (e.target.classList.contains('product-desc-mobile')) {
                row.querySelector('.product-description').value = e.target.value;
            }
            if (e.target.classList.contains('product-qty-mobile')) {
                row.querySelector('.product-quantity').value = e.target.value;
                calculateRow(row, true); // Skip mobile sync untuk mencegah rebuild
                // Update total di card ini saja
                const card = e.target.closest('.product-card-mobile');
                if (card) {
                    const totalValue = card.querySelector('.total-value');
                    if (totalValue) {
                        totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-line-total').value) || 0);
                    }
                }
            }
            if (e.target.classList.contains('product-price-mobile')) {
                row.querySelector('.product-price').value = e.target.value;
                calculateRow(row, true); // Skip mobile sync untuk mencegah rebuild
                // Update total di card ini saja
                const card = e.target.closest('.product-card-mobile');
                if (card) {
                    const totalValue = card.querySelector('.total-value');
                    if (totalValue) {
                        totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-line-total').value) || 0);
                    }
                }
            }
            if (e.target.classList.contains('product-disc-mobile')) {
                row.querySelector('.product-discount').value = e.target.value;
                calculateRow(row, true); // Skip mobile sync untuk mencegah rebuild
                // Update total di card ini saja
                const card = e.target.closest('.product-card-mobile');
                if (card) {
                    const totalValue = card.querySelector('.total-value');
                    if (totalValue) {
                        totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-line-total').value) || 0);
                    }
                }
            }
        });

        mobileCardsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn-mobile')) {
                const rowIndex = e.target.closest('.remove-btn-mobile').dataset.row;
                const row = tableBody.querySelectorAll('tr')[rowIndex];
                if (row) {
                    row.remove();
                    calculateGrandTotal();
                }
            }
        });
    }

    const handleProductChange = (event) => {
        if (!event.target.classList.contains('product-select')) return;
        const selectedOption = event.target.options[event.target.selectedIndex];
        const row = event.target.closest('tr');
        const harga = selectedOption.dataset.harga || 0;
        const deskripsi = selectedOption.dataset.deskripsi || '';
        row.querySelector('.product-price').value = harga;
        row.querySelector('.product-description').value = deskripsi;
        calculateRow(row);
    };

    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('product-quantity') || event.target.classList.contains('product-price') || event.target.classList.contains('product-discount')) {
            calculateRow(event.target.closest('tr'), true); // Skip mobile sync untuk mencegah rebuild
            // Update mobile card total jika ada
            const row = event.target.closest('tr');
            if (row && mobileCardsContainer) {
                const rowIndex = Array.from(tableBody.rows).indexOf(row);
                const card = mobileCardsContainer.querySelector(`.product-card-mobile[data-row-index="${rowIndex}"]`);
                if (card) {
                    const totalValue = card.querySelector('.total-value');
                    if (totalValue) {
                        totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-line-total').value) || 0);
                    }
                    // Sync input values ke mobile card
                    const qtyMobile = card.querySelector('.product-qty-mobile');
                    const priceMobile = card.querySelector('.product-price-mobile');
                    const discMobile = card.querySelector('.product-disc-mobile');
                    if (qtyMobile && event.target.classList.contains('product-quantity')) qtyMobile.value = event.target.value;
                    if (priceMobile && event.target.classList.contains('product-price')) priceMobile.value = event.target.value;
                    if (discMobile && event.target.classList.contains('product-discount')) discMobile.value = event.target.value;
                }
            }
        }
    });

    taxInput.addEventListener('input', calculateGrandTotal);
    discAkhirInput.addEventListener('input', calculateGrandTotal);
    tableBody.addEventListener('change', handleProductChange);

    addRowBtn.addEventListener('click', function () {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td>${productDropdownHtml}</td>
            <td><input type="text" class="form-control product-description" name="deskripsi[]"></td>
            <td><input type="number" class="form-control product-quantity" name="kuantitas[]" value="1" min="1"></td>
            <td>
                <select class="form-control" name="unit[]">
                    <option value="Pcs">Pcs</option>
                    <option value="Karton">Karton</option>
                    <option value="Box">Box</option>
                </select>
            </td>
            <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
            <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
            <td><input type="text" class="form-control text-right product-line-total" name="jumlah[]" placeholder="0" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
        // Init Select2 untuk dropdown baru
        initSelect2(newRow.querySelector('.product-select'));
        syncMobileCards();
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateGrandTotal();
        }
    });

    // INIT: Hitung semua baris saat halaman dimuat
    setTimeout(function() {
        tableBody.querySelectorAll('tr').forEach(row => calculateRow(row));
        syncMobileCards();
    }, 100);

    // Lampiran upload feedback - multiple files
    const lampiranInput = document.getElementById('lampiran');
    const lampiranList = document.getElementById('lampiran-list');
    const lampiranFileList = document.getElementById('lampiran-file-list');

    if (lampiranInput) {
        lampiranInput.addEventListener('change', function() {
            lampiranFileList.innerHTML = '';
            if (this.files && this.files.length > 0) {
                lampiranList.style.display = 'block';
                for (let i = 0; i < this.files.length; i++) {
                    const li = document.createElement('li');
                    li.innerHTML = '<i class="fas fa-file mr-1 text-primary"></i> ' + this.files[i].name;
                    lampiranFileList.appendChild(li);
                }
                
                // Update custom file label
                const label = this.nextElementSibling;
                if (label) {
                    label.textContent = this.files.length + ' file dipilih';
                }
            } else {
                lampiranList.style.display = 'none';
                const label = this.nextElementSibling;
                if (label) {
                    label.textContent = 'Pilih file baru...';
                }
            }
        });
    }
});
</script>
@endpush