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
                            {{-- 1. APPROVER (ADMIN) --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="approver_id">Staf Penyetuju (Admin) *</label>
                                    <select class="form-control @error('approver_id') is-invalid @enderror" id="approver_id" name="approver_id" required>
                                        <option value="">Pilih Atasan...</option>
                                        @foreach($approvers as $admin)
                                            <option value="{{ $admin->id }}" 
                                                    data-email="{{ $admin->email }}"
                                                    {{ old('approver_id', $pembelian->approver_id) == $admin->id ? 'selected' : '' }}>
                                                {{ $admin->name }} ({{ ucfirst($admin->role) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('approver_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            {{-- 2. EMAIL (AUTOFILL) --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email_penyetuju">Email Penyetuju</label>
                                    <input type="email" class="form-control @error('email_penyetuju') is-invalid @enderror" id="email_penyetuju" name="email_penyetuju" value="{{ old('email_penyetuju', $pembelian->email_penyetuju) }}" readonly>
                                </div>
                            </div>
                        </div>
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
                        
                        {{-- KOORDINAT LOKASI --}}
                        <div class="form-group">
                            <label for="koordinat">Koordinat Lokasi</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="koordinat" name="koordinat" value="{{ old('koordinat', $pembelian->koordinat) }}" placeholder="-6.123456, 106.123456" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="btn-get-location" title="Refresh Lokasi">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btn-open-maps" title="Buka di Google Maps">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABEL PRODUK/JASA --}}
                <div class="table-responsive mt-3">
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
                            <label for="lampiran">Lampiran (Kosongkan jika tidak ingin diubah)</label>
                            @if($pembelian->lampiran_path)
                                <div class="mb-2 small">File saat ini: <a href="{{ asset('storage/' . $pembelian->lampiran_path) }}" target="_blank">{{ basename($pembelian->lampiran_path) }}</a></div>
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
    const taxInput = document.getElementById('tax_percentage_input');
    const discAkhirInput = document.getElementById('diskon_akhir_input');

    // 1. AUTOFILL APPROVER
    const approverSelect = document.getElementById('approver_id');
    const emailInput = document.getElementById('email_penyetuju');
    
    if(approverSelect){
        approverSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            emailInput.value = selectedOption.dataset.email || '';
        });
        // Trigger jika old value ada
        if(approverSelect.value) {
            const selectedOption = approverSelect.options[approverSelect.selectedIndex];
            emailInput.value = selectedOption.dataset.email || '';
        }
    }

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

    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

    const calculateRow = (row) => {
        const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.product-price').value) || 0;
        const discount = parseFloat(row.querySelector('.product-discount').value) || 0;
        const total = quantity * price * (1 - (discount / 100));
        row.querySelector('.product-line-total').value = total.toFixed(0);
        calculateGrandTotal();
    };

    const calculateGrandTotal = () => {
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
        
        // Update total bawah jika ada
        const grandTotalBottom = document.getElementById('grand-total-bottom');
        if(grandTotalBottom) grandTotalBottom.innerText = formatRupiah(total);
    };

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
            calculateRow(event.target.closest('tr'));
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
    }, 100);

    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                var nextSibling = e.target.nextElementSibling;
                nextSibling.innerText = fileName;
            }
        });
    });

    // --- KOORDINAT LOKASI ---
    const koordinatInput = document.getElementById('koordinat');
    const btnGetLocation = document.getElementById('btn-get-location');
    const btnOpenMaps = document.getElementById('btn-open-maps');

    function updateMapsLink() {
        const coords = koordinatInput.value.trim();
        if(coords && coords.includes(',')) {
            btnOpenMaps.onclick = function() {
                window.open('https://www.google.com/maps?q=' + coords.replace(' ', ''), '_blank');
            };
            btnOpenMaps.classList.remove('disabled');
        } else {
            btnOpenMaps.onclick = null;
            btnOpenMaps.classList.add('disabled');
        }
    }

    function getLocation() {
        if (navigator.geolocation) {
            btnGetLocation.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude.toFixed(6);
                    const lng = position.coords.longitude.toFixed(6);
                    koordinatInput.value = lat + ', ' + lng;
                    btnGetLocation.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                    updateMapsLink();
                },
                function(error) {
                    console.log('Location error: ' + error.message);
                    btnGetLocation.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                }
            );
        }
    }

    if(btnGetLocation) {
        btnGetLocation.addEventListener('click', getLocation);
    }

    koordinatInput.addEventListener('input', updateMapsLink);
    updateMapsLink();
});
</script>
@endpush