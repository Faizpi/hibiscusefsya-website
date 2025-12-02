@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Biaya</h1>
        {{-- TOTAL ATAS (ID: grand-total-display) --}}
        <h3 class="font-weight-bold text-right text-primary" id="grand-total-display">Total Rp0,00</h3>
    </div>

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

    <form action="{{ route('biaya.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-body">
                {{-- BAGIAN ATAS FORM --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bayar_dari">Bayar Dari *</label>
                            <select class="form-control @error('bayar_dari') is-invalid @enderror" id="bayar_dari" name="bayar_dari" required>
                                <option value="Kas (1-10001)" {{ old('bayar_dari') == 'Kas (1-10001)' ? 'selected' : '' }}>Kas (1-10001)</option>
                                <option value="Bank (1-10002)" {{ old('bayar_dari') == 'Bank (1-10002)' ? 'selected' : '' }}>Bank (1-10002)</option>
                            </select>
                            @error('bayar_dari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    {{-- APPROVER (ADMIN) --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="approver_id">Ajukan Kepada (Admin) *</label>
                            <select class="form-control @error('approver_id') is-invalid @enderror" id="approver_id" name="approver_id" required>
                                <option value="">Pilih Atasan...</option>
                                @foreach($approvers as $admin)
                                    <option value="{{ $admin->id }}" 
                                            data-email="{{ $admin->email }}"
                                            {{ old('approver_id') == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }} ({{ ucfirst($admin->role) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('approver_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    {{-- EMAIL APPROVER (AUTOFILL) --}}
                    <div class="col-md-4">
                         <div class="form-group">
                             <label for="email_approver">Email Approver</label>
                             <input type="text" class="form-control" id="email_approver" readonly>
                         </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 pt-2 pb-3">
                         <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="bayar_nanti">
                            <label class="custom-control-label" for="bayar_nanti">Bayar Nanti</label>
                        </div>
                    </div>
                </div>

                <hr>
                
                {{-- DETAIL BIAYA --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penerima">Penerima (Kontak)</label>
                            {{-- Dropdown Kontak --}}
                            <select class="form-control @error('penerima') is-invalid @enderror" id="penerima" name="penerima">
                                <option value="">Pilih kontak...</option>
                                @foreach($kontaks as $kontak)
                                    <option value="{{ $kontak->nama }}" 
                                            data-alamat="{{ $kontak->alamat }}" 
                                            {{ old('penerima') == $kontak->nama ? 'selected' : '' }}>
                                        {{ $kontak->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penerima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control @error('alamat_penagihan') is-invalid @enderror" id="alamat_penagihan" name="alamat_penagihan" rows="2">{{ old('alamat_penagihan') }}</textarea>
                            @error('alamat_penagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl Transaksi *</label>
                                    <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                                    @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cara_pembayaran">Cara Pembayaran</label>
                                    <select class="form-control @error('cara_pembayaran') is-invalid @enderror" id="cara_pembayaran" name="cara_pembayaran">
                                        <option value="Tunai" {{ old('cara_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="Transfer Bank" {{ old('cara_pembayaran') == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                        <option value="Cek & Giro" {{ old('cara_pembayaran') == 'Cek & Giro' ? 'selected' : '' }}>Cek & Giro</option>
                                    </select>
                                    @error('cara_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Biaya</label>
                                    <input type="text" class="form-control" placeholder="[Auto Generated]" disabled>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tag">Tag (Pembuat)</label>
                                    <input type="text" class="form-control" id="tag" name="tag" value="{{ auth()->user()->name }}" readonly>
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
                                <th width="40%">Akun Biaya (Kategori)</th>
                                <th>Deskripsi</th>
                                <th width="30%" class="text-right">Jumlah</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            @if(old('kategori'))
                                @foreach(old('kategori') as $index => $oldKategori)
                                    <tr>
                                        <td><input type="text" class="form-control" name="kategori[]" value="{{ $oldKategori }}" placeholder="Contoh: Biaya Listrik" required></td>
                                        <td><input type="text" class="form-control" name="deskripsi_akun[]" value="{{ old('deskripsi_akun.'.$index) }}"></td>
                                        <td><input type="number" class="form-control text-right expense-amount" name="total[]" value="{{ old('total.'.$index) }}" placeholder="0" required></td>
                                        <td>
                                            @if($index > 0)
                                                <button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Baris Default --}}
                                <tr>
                                    <td><input type="text" class="form-control" name="kategori[]" placeholder="Contoh: Biaya Listrik" required></td>
                                    <td><input type="text" class="form-control" name="deskripsi_akun[]"></td>
                                    <td><input type="number" class="form-control text-right expense-amount" name="total[]" value="0" required></td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-dark btn-sm" id="add-row-btn">+ Tambah Data</button>
                @error('kategori.*') <div class="text-danger small mt-2">Error: Kategori wajib diisi</div> @enderror
                @error('total.*') <div class="text-danger small mt-2">Error: Jumlah wajib diisi</div> @enderror

                {{-- TOTAL & PAJAK --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="2">{{ old('memo') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="lampiran">Lampiran</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror" id="lampiran" name="lampiran">
                                <label class="custom-file-label" for="lampiran">Pilih file...</label>
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
                                        <label for="tax_percentage_input" class="mb-0"><strong>Pajak (%)</strong></label>
                                    </td>
                                    <td style="width: 50%;">
                                        <input type="number" class="form-control text-right @error('tax_percentage') is-invalid @enderror" 
                                               id="tax_percentage_input" name="tax_percentage" value="{{ old('tax_percentage', 0) }}" min="0" step="0.01">
                                        @error('tax_percentage') <div class="invalid-feedback d-block text-right">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jumlah Pajak</td>
                                    <td id="tax-amount-display">Rp0</td>
                                </tr>
                                {{-- TOTAL BAWAH (ID: grand-total-bottom) --}}
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
            <button type="submit" class="btn btn-success">Buat Biaya Baru</button>
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
    
    // Display Elements
    const subtotalDisplay = document.getElementById('subtotal-display');
    const taxDisplay = document.getElementById('tax-amount-display');
    const grandTotalBottom = document.getElementById('grand-total-bottom');
    const grandTotalTop = document.getElementById('grand-total-display');

    // Autofill Elements
    const kontakSelect = document.getElementById('penerima');
    const alamatInput = document.getElementById('alamat_penagihan');
    const approverSelect = document.getElementById('approver_id');
    const emailApproverInput = document.getElementById('email_approver');

    // --- AUTOFILL KONTAK (Penerima) ---
    if(kontakSelect){
        kontakSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            alamatInput.value = selectedOption.dataset.alamat || '';
        });
    }

    // --- AUTOFILL APPROVER (Admin) ---
    if(approverSelect){
        approverSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            emailApproverInput.value = selectedOption.dataset.email || '';
        });
        // Trigger on load if old value present
        if(approverSelect.value) {
            const selectedOption = approverSelect.options[approverSelect.selectedIndex];
            emailApproverInput.value = selectedOption.dataset.email || '';
        }
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
        
        // Update Tampilan
        subtotalDisplay.innerText = formatRupiah(subtotal);
        taxDisplay.innerText = formatRupiah(taxAmount);
        
        // Update Total Bawah & Atas
        grandTotalBottom.innerText = formatRupiah(total);
        grandTotalTop.innerText = `Total ${formatRupiah(total)}`;
    };

    // Event Listener Input
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('expense-amount') || e.target.id === 'tax_percentage_input') {
            calculateTotalExpense();
        }
    });

    // Tambah Baris
    addRowBtn.addEventListener('click', function() {
        const newRow = tableBody.insertRow();
        newRow.innerHTML = `
            <td><input type="text" class="form-control" name="kategori[]" placeholder="Contoh: Biaya Listrik"></td>
            <td><input type="text" class="form-control" name="deskripsi_akun[]"></td>
            <td><input type="number" class="form-control text-right expense-amount" name="total[]" value="0" required></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
        `;
    });

    // Hapus Baris
    tableBody.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-row-btn')) {
            event.target.closest('tr').remove();
            calculateTotalExpense();
        }
    });
    
    // Hitung total saat halaman dimuat
    calculateTotalExpense();

    // Nama File
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