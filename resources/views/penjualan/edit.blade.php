@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Penagihan Penjualan #{{ $penjualan->custom_number ?? $penjualan->id }}
            </h1>
            <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle mr-2"></i>Gagal!</strong><br>
                {!! session('error') !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form action="{{ route('penjualan.update', $penjualan->id) }}" method="POST" enctype="multipart/form-data">
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
                                        <label>Pelanggan *</label>
                                        <select class="form-control" id="kontak-select" name="pelanggan" required>
                                            <option value="">Pilih kontak...</option>
                                            @foreach($kontaks as $kontak)
                                                <option value="{{ $kontak->nama }}" data-email="{{ $kontak->email }}"
                                                    data-alamat="{{ $kontak->alamat }}"
                                                    data-diskon="{{ $kontak->diskon_persen }}" {{ old('pelanggan', $penjualan->pelanggan) == $kontak->nama ? 'selected' : '' }}>
                                                    {{ $kontak->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Email</label><input type="email" class="form-control"
                                            id="email-input" name="email" value="{{ old('email', $penjualan->email) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group"><label>Alamat Penagihan</label><textarea class="form-control"
                                    id="alamat-input" name="alamat_penagihan"
                                    rows="2">{{ old('alamat_penagihan', $penjualan->alamat_penagihan) }}</textarea></div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group"><label>Tgl. Transaksi *</label><input type="date"
                                            class="form-control" id="tgl_transaksi" name="tgl_transaksi"
                                            value="{{ old('tgl_transaksi', $penjualan->tgl_transaksi->format('Y-m-d')) }}"
                                            required></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Syarat Pembayaran *</label>
                                        <select class="form-control" id="syarat_pembayaran" name="syarat_pembayaran"
                                            required>
                                            @foreach(['Cash', 'Net 7', 'Net 14', 'Net 30', 'Net 60'] as $opt)
                                                <option value="{{ $opt }}" {{ old('syarat_pembayaran', $penjualan->syarat_pembayaran) == $opt ? 'selected' : '' }}>{{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group"><label>Jatuh Tempo (Auto)</label><input type="text"
                                            class="form-control bg-light" id="tgl_jatuh_tempo_display" readonly><input
                                            type="hidden" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo"
                                            value="{{ old('tgl_jatuh_tempo', $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('Y-m-d') : '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group"><label>No Transaksi</label><input type="text" class="form-control"
                                    value="{{ $penjualan->custom_number ?? '[Auto]' }}" disabled></div>
                            <div class="form-group"><label>No. Referensi</label><input type="text" class="form-control"
                                    name="no_referensi" value="{{ old('no_referensi', $penjualan->no_referensi) }}"></div>
                            <div class="form-group"><label>Tag</label><input type="text" class="form-control" name="tag"
                                    value="{{ old('tag', $penjualan->tag) }}" readonly></div>
                            <div class="form-group">
                                <label>Koordinat Lokasi</label>
                                <input type="text" class="form-control bg-light" name="koordinat"
                                    value="{{ old('koordinat', $penjualan->koordinat) }}" readonly>
                                @if($penjualan->koordinat)
                                    <small class="text-muted">Koordinat diambil saat transaksi dibuat</small>
                                @endif
                            </div>

                            <div class="form-group">
                                <label>Gudang *</label>
                                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                    <select class="form-control" name="gudang_id" required>
                                        @foreach($gudangs as $g) <option value="{{ $g->id }}" {{ old('gudang_id', $penjualan->gudang_id) == $g->id ? 'selected' : '' }}>{{ $g->nama_gudang }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="{{ $penjualan->gudang->nama_gudang ?? '-' }}"
                                        readonly>
                                    <input type="hidden" name="gudang_id" value="{{ $penjualan->gudang_id }}">
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- TABEL PRODUK (DESKTOP) --}}
                    <div class="table-responsive mt-3 desktop-product-table">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th width="20%">Produk</th>
                                    <th>Deskripsi</th>
                                    <th width="8%">Qty</th>
                                    <th width="10%">Unit</th>
                                    <th width="12%">Harga</th>
                                    <th width="10%">Disc%</th>
                                    <th width="15%" class="text-right">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                @php $items = old('produk_id') ? old('produk_id') : $penjualan->items; @endphp
                                @foreach($items as $index => $item)
                                    @php
                                        $isOld = old('produk_id') ? true : false;
                                        $oldProd = $isOld ? $item : $item->produk_id;
                                        $oldDesc = $isOld ? old('deskripsi.' . $index) : $item->deskripsi;
                                        $oldQty = $isOld ? old('kuantitas.' . $index) : $item->kuantitas;
                                        $oldUnit = $isOld ? old('unit.' . $index) : $item->unit;
                                        $oldPrice = $isOld ? old('harga_satuan.' . $index) : $item->harga_satuan;
                                        $oldDisc = $isOld ? old('diskon.' . $index) : $item->diskon;
                                    @endphp
                                    <tr>
                                        <td>
                                            <select class="form-control product-select" name="produk_id[]" required>
                                                <option value="">Pilih...</option>
                                                @foreach($produks as $p) <option value="{{ $p->id }}"
                                                    data-harga="{{ $p->harga }}" data-deskripsi="{{ $p->deskripsi }}"
                                                    data-satuan="{{ $p->satuan ?? 'Pcs' }}" {{ $oldProd == $p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control product-description" name="deskripsi[]"
                                                value="{{ $oldDesc }}"></td>
                                        <td><input type="number" class="form-control product-quantity" name="kuantitas[]"
                                                value="{{ $oldQty }}" min="1" required></td>
                                        <td><input type="text" class="form-control product-unit" name="unit[]"
                                                value="{{ $oldUnit }}" readonly></td>
                                        <td><input type="number" class="form-control text-right product-price"
                                                name="harga_satuan[]" value="{{ $oldPrice }}" required></td>
                                        <td><input type="number" class="form-control text-right product-discount"
                                                name="diskon[]" value="{{ $oldDisc }}" min="0" max="100"></td>
                                        <td><input type="text" class="form-control text-right product-line-total" readonly></td>
                                        <td>@if($index > 0 || count($items) > 1)<button type="button"
                                        class="btn btn-danger btn-sm remove-row-btn">X</button>@endif</td>
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

                    {{-- TOTAL --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group"><label>Memo</label><textarea class="form-control" name="memo"
                                    rows="3">{{ old('memo', $penjualan->memo) }}</textarea></div>
                            <div class="form-group">
                                <label for="lampiran">Lampiran Tambahan <small class="text-muted">(dapat memilih banyak
                                        file)</small></label>
                                @php
                                    $allLampiran = [];
                                    if ($penjualan->lampiran_path) {
                                        $allLampiran[] = $penjualan->lampiran_path;
                                    }
                                    if ($penjualan->lampiran_paths) {
                                        $allLampiran = array_merge($allLampiran, $penjualan->lampiran_paths);
                                    }
                                @endphp
                                @if(count($allLampiran) > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">File saat ini:</small>
                                        <ul class="list-unstyled mb-0 mt-1">
                                            @foreach($allLampiran as $lampiran)
                                                <li><i class="fas fa-file mr-1"></i> <a href="{{ asset('storage/' . $lampiran) }}"
                                                        target="_blank">{{ basename($lampiran) }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file"
                                        class="custom-file-input @error('lampiran') is-invalid @enderror @error('lampiran.*') is-invalid @enderror"
                                        id="lampiran" name="lampiran[]" multiple
                                        accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx">
                                    <label class="custom-file-label" for="lampiran">Pilih file baru...</label>
                                </div>
                                <small class="form-text text-muted">Format: jpg, jpeg, png, pdf, zip, doc, docx (max 2MB per file)</small>
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
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td id="subtotal-display">Rp0</td>
                                </tr>
                                <tr>
                                    <td><strong>Diskon Akhir (Rp)</strong></td>
                                    <td><input type="number" class="form-control text-right" id="diskon_akhir_input"
                                            name="diskon_akhir" value="{{ old('diskon_akhir', $penjualan->diskon_akhir) }}">
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pajak (%)</strong></td>
                                    <td width="50%"><input type="number" class="form-control text-right"
                                            id="tax_percentage_input" name="tax_percentage"
                                            value="{{ old('tax_percentage', $penjualan->tax_percentage) }}"></td>
                                </tr>
                                <tr>
                                    <td>Jumlah Pajak</td>
                                    <td id="tax-amount-display">Rp0</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="h5"><strong>Total</strong></td>
                                    <td class="h5" id="grand-total-bottom">Rp0</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-right"><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
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
            const kontakSelect = document.getElementById('kontak-select');
            const emailInput = document.getElementById('email-input');
            const alamatInput = document.getElementById('alamat-input');

            // Product Options HTML for mobile cards
            const productOptionsHtml = `@foreach($produks as $produk)<option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}">{{ $produk->nama_produk }}</option>@endforeach`;

            // --- JATUH TEMPO AUTO ---
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
                document.getElementById('tgl_jatuh_tempo').value = date.format('YYYY-MM-DD');
            }
            document.getElementById('tgl_transaksi').addEventListener('change', updateDueDate);
            document.getElementById('syarat_pembayaran').addEventListener('change', updateDueDate);
            updateDueDate();

            if (kontakSelect) {
                kontakSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    emailInput.value = selectedOption.dataset.email || '';
                    alamatInput.value = selectedOption.dataset.alamat || '';
                    tableBody.querySelectorAll('tr').forEach(row => {
                        const diskonInput = row.querySelector('.product-discount');
                        if (diskonInput) {
                            diskonInput.value = selectedOption.dataset.diskon || 0;
                            calculateRow(row);
                        }
                    });
                });
            }

            const productDropdownHtml = `
                                            <select class="form-control product-select" name="produk_id[]" required>
                                                <option value="">Pilih...</option>
                                                @foreach($produks as $produk)
                                                    <option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}" data-satuan="{{ $produk->satuan ?? 'Pcs' }}">{{ $produk->nama_produk }}</option>
                                                @endforeach
                                            </select>
                                        `;

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
                        row.querySelector('.product-description').value = option.dataset.deskripsi || '';
                        row.querySelector('.product-unit').value = option.dataset.satuan || 'Pcs';
                        if (kontakSelect) {
                            const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
                            if (kontakOption) {
                                row.querySelector('.product-discount').value = kontakOption.dataset.diskon || 0;
                            }
                        }
                        calculateRow(row);
                    }
                });
            }

            // Init Select2 untuk semua dropdown produk yang sudah ada
            $('.product-select').each(function () {
                initSelect2(this);
            });

            const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

            const calculateRow = (row, skipMobileSync = false) => {
                const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.product-price').value) || 0;
                const discount = parseFloat(row.querySelector('.product-discount').value) || 0;
                const total = quantity * price * (1 - (discount / 100));
                row.querySelector('.product-line-total').value = total.toFixed(0);
                calculateGrandTotal();

                // Hanya sync mobile cards jika tidak di-skip (untuk mencegah rebuild saat input)
                if (!skipMobileSync) {
                    // Update hanya total di mobile card tanpa rebuild
                    const rowIndex = Array.from(tableBody.rows).indexOf(row);
                    const mobileCard = mobileCardsContainer?.querySelector(`.product-card-mobile[data-row-index="${rowIndex}"]`);
                    if (mobileCard) {
                        const totalValue = mobileCard.querySelector('.total-value');
                        if (totalValue) {
                            totalValue.textContent = formatRupiah(total);
                        }
                    }
                }
            };

            const calculateGrandTotal = () => {
                let subtotal = 0;
                tableBody.querySelectorAll('tr').forEach(row => {
                    const lineTotal = parseFloat(row.querySelector('.product-line-total').value) || 0;
                    subtotal += lineTotal;
                });
                let diskonAkhir = parseFloat(discAkhirInput.value) || 0;
                let kenaPajak = Math.max(0, subtotal - diskonAkhir);
                let taxPercentage = parseFloat(taxInput.value) || 0;
                let taxAmount = kenaPajak * (taxPercentage / 100);
                const total = kenaPajak + taxAmount;
                document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
                document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
                document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
                document.getElementById('grand-total-bottom').innerText = formatRupiah(total);
            };

            // --- MOBILE CARDS SYNC ---
            function syncMobileCards() {
                if (!mobileCardsContainer) return;

                mobileCardsContainer.innerHTML = '';
                const rows = tableBody.querySelectorAll('tr');

                rows.forEach((row, index) => {
                    const select = row.querySelector('.product-select');
                    const produkName = select.options[select.selectedIndex]?.text || 'Pilih Produk';
                    const desc = row.querySelector('.product-description').value || '-';
                    const qty = row.querySelector('.product-quantity').value || 0;
                    const unit = row.querySelector('.product-unit').value || 'Pcs';
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
                                                                <input type="text" class="form-control product-unit-mobile" data-row="${index}" value="${unit}" readonly>
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

                    // Set selected product
                    const mobileSelect = card.querySelector('.product-select-mobile');
                    mobileSelect.value = select.value;
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
                        row.querySelector('.product-description').value = deskripsi;

                        // Check dan apply discount dari kontak
                        if (kontakSelect && kontakSelect.selectedIndex > 0) {
                            const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
                            if (kontakOption && kontakOption.dataset.diskon) {
                                row.querySelector('.product-discount').value = kontakOption.dataset.diskon;
                            }
                        }

                        // Update mobile card langsung
                        if (card) {
                            const priceMobile = card.querySelector('.product-price-mobile');
                            const descMobile = card.querySelector('.product-desc-mobile');
                            const discMobile = card.querySelector('.product-disc-mobile');
                            if (priceMobile) priceMobile.value = harga;
                            if (descMobile) descMobile.value = deskripsi;
                            if (discMobile && kontakSelect && kontakSelect.selectedIndex > 0) {
                                const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
                                if (kontakOption && kontakOption.dataset.diskon) {
                                    discMobile.value = kontakOption.dataset.diskon;
                                }
                            }
                        }

                        // Calculate row dan update total di mobile card
                        calculateRow(row, true);
                        if (card) {
                            const totalValue = card.querySelector('.total-value');
                            if (totalValue) {
                                totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-line-total').value) || 0);
                            }
                        }
                    }
                });

                mobileCardsContainer.addEventListener('input', function (e) {
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

                mobileCardsContainer.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-btn-mobile')) {
                        const rowIndex = e.target.closest('.remove-btn-mobile').dataset.row;
                        const row = tableBody.querySelectorAll('tr')[rowIndex];
                        if (row) {
                            row.remove();
                            calculateGrandTotal();
                            syncMobileCards();
                        }
                    }
                });
            }

            const handleProductChange = (event) => {
                if (!event.target.classList.contains('product-select')) return;
                const selectedOption = event.target.options[event.target.selectedIndex];
                const row = event.target.closest('tr');
                row.querySelector('.product-price').value = selectedOption.dataset.harga || 0;
                row.querySelector('.product-description').value = selectedOption.dataset.deskripsi || '';
                const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
                if (kontakOption) {
                    row.querySelector('.product-discount').value = kontakOption.dataset.diskon || 0;
                }
                calculateRow(row); // Full sync OK untuk perubahan produk
                syncMobileCards(); // Rebuild cards karena data produk berubah
            };

            tableBody.addEventListener('input', function (event) {
                if (event.target.matches('.product-quantity, .product-price, .product-discount')) {
                    const row = event.target.closest('tr');
                    calculateRow(row, true); // Skip mobile sync untuk mencegah keyboard close
                    // Update mobile card values tanpa rebuild
                    if (mobileCardsContainer) {
                        const rowIndex = Array.from(tableBody.rows).indexOf(row);
                        const card = mobileCardsContainer.querySelector(`.product-card-mobile[data-row-index="${rowIndex}"]`);
                        if (card) {
                            const totalValue = card.querySelector('.total-value');
                            if (totalValue) {
                                totalValue.textContent = formatRupiah(parseFloat(row.querySelector('.product-total').value) || 0);
                            }
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
                                                <td><input type="text" class="form-control product-unit" name="unit[]" value="" readonly></td>
                                                <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
                                                <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
                                                <td><input type="text" class="form-control text-right product-line-total" readonly></td>
                                                <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
                                            `;
                const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
                if (kontakOption) newRow.querySelector('.product-discount').value = kontakOption.dataset.diskon || 0;
                // Init Select2 untuk dropdown baru
                initSelect2(newRow.querySelector('.product-select'));
                syncMobileCards();
            });

            tableBody.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-row-btn')) {
                    event.target.closest('tr').remove();
                    calculateGrandTotal();
                    syncMobileCards();
                }
            });

            // INIT: Hitung semua baris saat halaman dimuat
            setTimeout(function () {
                tableBody.querySelectorAll('tr').forEach(row => calculateRow(row));
                syncMobileCards();
            }, 100);
        });
    </script>
@endpush