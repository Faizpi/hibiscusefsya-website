@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Biaya #{{ $biaya->custom_number ?? $biaya->id }}</h1>
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

    <form action="{{ route('biaya.update', $biaya->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="card shadow mb-4">
            <div class="card-body">
                {{-- BAGIAN ATAS FORM --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bayar_dari">Bayar Dari *</label>
                            <select class="form-control @error('bayar_dari') is-invalid @enderror" name="bayar_dari" required>
                                <option value="Kas (1-10001)" {{ old('bayar_dari', $biaya->bayar_dari) == 'Kas (1-10001)' ? 'selected' : '' }}>Kas (1-10001)</option>
                                <option value="Bank (1-10002)" {{ old('bayar_dari', $biaya->bayar_dari) == 'Bank (1-10002)' ? 'selected' : '' }}>Bank (1-10002)</option>
                            </select>
                            @error('bayar_dari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Approver akan ditentukan otomatis berdasarkan gudang Anda
                        </div>
                    </div>
                </div>
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penerima">Penerima (Kontak)</label>
                            {{-- Dropdown Kontak --}}
                            <select class="form-control @error('penerima') is-invalid @enderror" id="kontak-select" name="penerima">
                                <option value="">Pilih kontak...</option>
                                @foreach($kontaks as $kontak)
                                    <option value="{{ $kontak->nama }}"
                                            data-alamat="{{ $kontak->alamat }}"
                                            {{ old('penerima', $biaya->penerima) == $kontak->nama ? 'selected' : '' }}>
                                        {{ $kontak->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penerima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control @error('alamat_penagihan') is-invalid @enderror" id="alamat-input" name="alamat_penagihan" rows="2">{{ old('alamat_penagihan', $biaya->alamat_penagihan) }}</textarea>
                            @error('alamat_penagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl Transaksi *</label>
                                    <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', $biaya->tgl_transaksi->format('Y-m-d')) }}" required>
                                    @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cara_pembayaran">Cara Pembayaran</label>
                                    <select class="form-control @error('cara_pembayaran') is-invalid @enderror" name="cara_pembayaran">
                                        <option value="Tunai" {{ old('cara_pembayaran', $biaya->cara_pembayaran) == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="Transfer Bank" {{ old('cara_pembayaran', $biaya->cara_pembayaran) == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    </select>
                                    @error('cara_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group"><label>No Biaya</label><input type="text" class="form-control" value="{{ $biaya->custom_number ?? '[Auto]' }}" disabled></div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tag">Tag</label>
                                    <input type="text" class="form-control" id="tag" name="tag" value="{{ old('tag', $biaya->tag) }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Koordinat Lokasi</label>
                                    <input type="text" class="form-control bg-light" name="koordinat" value="{{ old('koordinat', $biaya->koordinat) }}" readonly>
                                    @if($biaya->koordinat)
                                        <small class="text-muted">Koordinat diambil saat transaksi dibuat</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABEL AKUN BIAYA (DESKTOP) --}}
                <div class="table-responsive mt-3 desktop-product-table">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40%;">Akun Biaya</th>
                                <th>Deskripsi</th>
                                <th class="text-right" style="width: 30%;">Jumlah</th>
                                <th style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            {{-- Load data lama (Induk) & Rincian --}}
                            @php
                                $items = old('kategori') ? old('kategori') : $biaya->items;
                            @endphp

                            @foreach($items as $index => $item)
                                @php
                                    // Cek sumber data: Old Input (Array) vs Database (Objek)
                                    $isOld = old('kategori') ? true : false;
                                    
                                    $oldKategori = $isOld ? $item : $item->kategori;
                                    $oldDeskripsi = $isOld ? old('deskripsi_akun.'.$index) : $item->deskripsi;
                                    $oldJumlah = $isOld ? old('total.'.$index) : $item->jumlah;
                                @endphp
                                <tr>
                                    <td><input type="text" class="form-control" name="kategori[]" value="{{ $oldKategori }}" placeholder="Contoh: Biaya Kantor" required></td>
                                    <td><input type="text" class="form-control" name="deskripsi_akun[]" value="{{ $oldDeskripsi }}"></td>
                                    <td><input type="number" class="form-control text-right expense-amount" name="total[]" value="{{ $oldJumlah }}" placeholder="0" required></td>
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
                <div class="mobile-product-cards mt-3" id="mobile-expense-cards">
                    {{-- Cards akan di-generate via JavaScript --}}
                </div>

                <button type="button" class="btn btn-dark btn-sm" id="add-row-btn">+ Tambah Data</button>
                @error('kategori.*') <div class="text-danger small mt-2">Error di baris Kategori: {{ $message }}</div> @enderror
                @error('total.*') <div class="text-danger small mt-2">Error di baris Jumlah: {{ $message }}</div> @enderror

                {{-- BAGIAN BAWAH --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="2">{{ old('memo', $biaya->memo) }}</textarea>
                            @error('memo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="lampiran">Lampiran Tambahan <small class="text-muted">(dapat memilih banyak file)</small></label>
                            @php
                                $allLampiran = [];
                                if($biaya->lampiran_path) {
                                    $allLampiran[] = $biaya->lampiran_path;
                                }
                                if($biaya->lampiran_paths) {
                                    $allLampiran = array_merge($allLampiran, $biaya->lampiran_paths);
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
                            <tbody>
                                <tr>
                                    <td><strong>Subtotal</strong></td>
                                    <td id="subtotal-display">Rp0,00</td>
                                </tr>
                                <tr>
                                    <td><label for="tax_percentage_input" class="mb-0"><strong>Pajak (%)</strong></label></td>
                                    <td style="width: 50%;">
                                        <input type="number" class="form-control text-right @error('tax_percentage') is-invalid @enderror" 
                                               id="tax_percentage_input" name="tax_percentage" value="{{ old('tax_percentage', $biaya->tax_percentage) }}" min="0" step="0.01">
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
                                    <td class="h5" id="grand-total-bottom">Rp0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-right">
            <a href="{{ route('biaya.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('expense-table-body');
    const addRowBtn = document.getElementById('add-row-btn');
    const mobileCardsContainer = document.getElementById('mobile-expense-cards');
    const taxInput = document.getElementById('tax_percentage_input');
    const kontakSelect = document.getElementById('kontak-select');
    const alamatInput = document.getElementById('alamat-input');

    // Autofill Kontak dengan Select2
    if(kontakSelect){
        // Inisialisasi Select2 untuk dropdown Penerima (Kontak)
        $('#kontak-select').select2({
            placeholder: 'Cari kontak...',
            allowClear: true,
            width: '100%'
        }).on('select2:select', function(e) {
            // Gunakan e.params.data.element untuk akses dataset dengan benar di Select2
            const selectedOption = e.params.data.element;
            if (selectedOption) {
                alamatInput.value = selectedOption.dataset.alamat || '';
            }
        }).on('select2:clear', function(e) {
            alamatInput.value = '';
        });
    }

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    const calculateTotalExpense = (skipMobileSync = false) => {
        let subtotal = 0;
        tableBody.querySelectorAll('.expense-amount').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        
        let taxPercentage = parseFloat(taxInput.value) || 0;
        let taxAmount = subtotal * (taxPercentage / 100);
        const total = subtotal + taxAmount;
        
        document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
        document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
        document.getElementById('grand-total-bottom').innerText = formatRupiah(total);
        document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
        
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
            const kategori = row.querySelector('input[name="kategori[]"]').value || '';
            const deskripsi = row.querySelector('input[name="deskripsi_akun[]"]').value || '';
            const jumlah = row.querySelector('.expense-amount').value || 0;
            
            const card = document.createElement('div');
            card.className = 'product-card-mobile';
            card.dataset.rowIndex = index;
            card.innerHTML = `
                <div class="card-header-mobile">
                    <span class="item-number">Item ${index + 1}</span>
                    ${rows.length > 1 ? `<button type="button" class="btn btn-danger btn-sm remove-btn-mobile" data-row="${index}"><i class="fas fa-times"></i></button>` : ''}
                </div>
                <div class="card-body-mobile">
                    <div class="field-group full-width">
                        <span class="field-label">Akun Biaya</span>
                        <input type="text" class="form-control kategori-mobile" data-row="${index}" value="${kategori}" placeholder="Contoh: Biaya Kantor">
                    </div>
                    <div class="field-group full-width">
                        <span class="field-label">Deskripsi</span>
                        <input type="text" class="form-control deskripsi-mobile" data-row="${index}" value="${deskripsi}" placeholder="Deskripsi">
                    </div>
                    <div class="field-group full-width">
                        <span class="field-label">Jumlah</span>
                        <input type="number" class="form-control text-right jumlah-mobile" data-row="${index}" value="${jumlah}">
                    </div>
                </div>
                <div class="total-row">
                    <span class="total-label">Jumlah</span>
                    <span class="total-value">${formatRupiah(jumlah)}</span>
                </div>
            `;
            mobileCardsContainer.appendChild(card);
        });
    }

    // Mobile card event listeners
    if (mobileCardsContainer) {
        mobileCardsContainer.addEventListener('input', function(e) {
            const rowIndex = e.target.dataset.row;
            if (!rowIndex) return;
            const row = tableBody.querySelectorAll('tr')[rowIndex];
            if (!row) return;

            if (e.target.classList.contains('kategori-mobile')) {
                row.querySelector('input[name="kategori[]"]').value = e.target.value;
            }
            if (e.target.classList.contains('deskripsi-mobile')) {
                row.querySelector('input[name="deskripsi_akun[]"]').value = e.target.value;
            }
            if (e.target.classList.contains('jumlah-mobile')) {
                row.querySelector('.expense-amount').value = e.target.value;
                calculateTotalExpense(true); // Skip mobile sync untuk mencegah rebuild
                // Update total di card ini saja
                const card = e.target.closest('.product-card-mobile');
                if (card) {
                    const totalValue = card.querySelector('.total-value');
                    if (totalValue) {
                        totalValue.textContent = formatRupiah(parseFloat(e.target.value) || 0);
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
                    calculateTotalExpense();
                }
            }
        });
    }

    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('expense-amount')) {
            calculateTotalExpense(true); // Skip mobile sync untuk mencegah rebuild
            // Update mobile card total jika ada
            const row = event.target.closest('tr');
            if (row && mobileCardsContainer) {
                const rowIndex = Array.from(tableBody.rows).indexOf(row);
                const card = mobileCardsContainer.querySelector(`.product-card-mobile[data-row-index="${rowIndex}"]`);
                if (card) {
                    const jumlahInput = card.querySelector('.jumlah-mobile');
                    const totalValue = card.querySelector('.total-value');
                    if (jumlahInput) jumlahInput.value = event.target.value;
                    if (totalValue) totalValue.textContent = formatRupiah(parseFloat(event.target.value) || 0);
                }
            }
        }
    });

    taxInput.addEventListener('input', calculateTotalExpense);

    addRowBtn.addEventListener('click', function() {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="kategori[]" placeholder="Contoh: Biaya Internet"></td>
            <td><input type="text" class="form-control" name="deskripsi_akun[]"></td>
            <td><input type="number" class="form-control text-right expense-amount" name="total[]" placeholder="0" required></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
        syncMobileCards();
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateTotalExpense();
        }
    });
    
    // Hitung total saat halaman dimuat (PENTING UNTUK EDIT)
    setTimeout(function() {
        calculateTotalExpense();
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