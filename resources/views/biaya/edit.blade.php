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
                    <div class="col-md-8 pt-4">
                         <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bayar_nanti">
                            <label class="custom-control-label" for="bayar_nanti">Bayar Nanti</label>
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
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABEL AKUN BIAYA --}}
                <div class="table-responsive mt-3">
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
                            <label for="lampiran">Lampiran (Kosongkan jika tidak ingin mengubah)</label>
                            @if($biaya->lampiran_path)
                                <div class="mb-2 small">File saat ini: <a href="{{ asset('storage/' . $biaya->lampiran_path) }}" target="_blank">{{ basename($biaya->lampiran_path) }}</a></div>
                            @endif
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror" id="lampiran" name="lampiran">
                                <label class="custom-file-label" for="lampiran">Pilih file baru...</label>
                            </div>
                            @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                                    <td class="h5" id="grand-total-display">Rp0,00</td>
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
    const taxInput = document.getElementById('tax_percentage_input');
    const kontakSelect = document.getElementById('kontak-select');
    const alamatInput = document.getElementById('alamat-input');

    // Autofill Kontak
    if(kontakSelect){
        kontakSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            alamatInput.value = selectedOption.dataset.alamat || '';
        });
    }

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    const calculateTotalExpense = () => {
        let subtotal = 0;
        tableBody.querySelectorAll('.expense-amount').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        
        let taxPercentage = parseFloat(taxInput.value) || 0;
        let taxAmount = subtotal * (taxPercentage / 100);
        const total = subtotal + taxAmount;
        
        document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
        document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
        document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
    };

    tableBody.addEventListener('input', function(event) {
        if (event.target.classList.contains('expense-amount')) {
            calculateTotalExpense();
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
    });

    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateTotalExpense();
        }
    });
    
    // Hitung total saat halaman dimuat (PENTING UNTUK EDIT)
    calculateTotalExpense();

    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                var nextSibling = e.target.nextElementSibling;
                nextSibling.innerText = fileName;
            }
        });
    });
});
</script>
@endpush