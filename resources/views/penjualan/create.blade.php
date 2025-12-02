@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Penagihan Penjualan</h1>
        {{-- TOTAL ATAS (ID: grand-total-display) --}}
        <h3 class="font-weight-bold text-right text-primary" id="grand-total-display">Total Rp0,00</h3>
    </div>

    {{-- Penampil Error --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi Kesalahan Validasi:</strong>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('penjualan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-body">
                {{-- BAGIAN ATAS FORM --}}
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            {{-- PELANGGAN (KONTAK) --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pelanggan">Pelanggan *</label>
                                    <select class="form-control @error('pelanggan') is-invalid @enderror" id="kontak-select" name="pelanggan" required>
                                        <option value="">Pilih kontak...</option>
                                        @foreach($kontaks as $kontak)
                                            <option value="{{ $kontak->nama }}"
                                                    data-email="{{ $kontak->email }}"
                                                    data-alamat="{{ $kontak->alamat }}"
                                                    data-diskon="{{ $kontak->diskon_persen }}"
                                                    {{ old('pelanggan') == $kontak->nama ? 'selected' : '' }}>
                                                {{ $kontak->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pelanggan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email-input" name="email" value="{{ old('email') }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="alamat_penagihan">Alamat Penagihan</label>
                            <textarea class="form-control @error('alamat_penagihan') is-invalid @enderror" id="alamat-input" name="alamat_penagihan" rows="2">{{ old('alamat_penagihan') }}</textarea>
                            @error('alamat_penagihan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tgl_transaksi">Tgl. Transaksi *</label>
                                    <input type="date" class="form-control @error('tgl_transaksi') is-invalid @enderror" id="tgl_transaksi" name="tgl_transaksi" value="{{ old('tgl_transaksi', date('Y-m-d')) }}" required>
                                    @error('tgl_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="syarat_pembayaran">Syarat Pembayaran *</label>
                                    <select class="form-control @error('syarat_pembayaran') is-invalid @enderror" id="syarat_pembayaran" name="syarat_pembayaran" required>
                                        <option value="Cash" {{ old('syarat_pembayaran') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="Net 7" {{ old('syarat_pembayaran') == 'Net 7' ? 'selected' : '' }}>Net 7 Days</option>
                                        <option value="Net 14" {{ old('syarat_pembayaran') == 'Net 14' ? 'selected' : '' }}>Net 14 Days</option>
                                        <option value="Net 30" {{ old('syarat_pembayaran') == 'Net 30' ? 'selected' : '' }}>Net 30 Days</option>
                                        <option value="Net 60" {{ old('syarat_pembayaran') == 'Net 60' ? 'selected' : '' }}>Net 60 Days</option>
                                    </select>
                                    @error('syarat_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tgl_jatuh_tempo">Jatuh Tempo (Auto)</label>
                                    <input type="text" class="form-control bg-light" id="tgl_jatuh_tempo_display" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                         <div class="form-group">
                            <label for="no_transaksi">No Transaksi</label>
                            <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" placeholder="[Auto]" disabled>
                        </div>
                        <div class="form-group">
                            <label for="no_referensi">No. Referensi Pelanggan</label>
                            <input type="text" class="form-control @error('no_referensi') is-invalid @enderror" id="no_referensi" name="no_referensi" value="{{ old('no_referensi') }}">
                            @error('no_referensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="koordinat">Koordinat Lokasi</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('koordinat') is-invalid @enderror" id="koordinat" name="koordinat" value="{{ old('koordinat') }}" placeholder="-6.123456, 106.123456">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="btn-get-location" title="Ambil Lokasi Saat Ini">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </button>
                                    <a href="#" class="btn btn-outline-success" id="btn-open-maps" target="_blank" title="Buka di Google Maps">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                            <small class="text-muted">Format: latitude, longitude</small>
                            @error('koordinat') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tag">Tag (Sales)</label>
                            <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" name="tag" value="{{ auth()->user()->name }}" readonly>
                            @error('tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        {{-- GUDANG --}}
                        <div class="form-group">
                            <label for="gudang_id">Gudang *</label>
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id" name="gudang_id" required>
                                    <option value="">Pilih gudang...</option>
                                    @foreach($gudangs as $gudang)
                                        <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                            {{ $gudang->nama_gudang }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="{{ auth()->user()->gudang->nama_gudang ?? 'User tidak terhubung ke gudang' }}" readonly>
                                <input type="hidden" name="gudang_id" value="{{ auth()->user()->gudang_id }}">
                            @endif
                            @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- APPROVER --}}
                        <div class="form-group">
                            <label for="approver_id">Ajukan Kepada (Approver) *</label>
                            <select class="form-control @error('approver_id') is-invalid @enderror" id="approver_id" name="approver_id" required>
                                <option value="">Pilih Atasan...</option>
                                @foreach($approvers as $approver)
                                    <option value="{{ $approver->id }}" {{ old('approver_id') == $approver->id ? 'selected' : '' }}>
                                        {{ $approver->name }} ({{ ucfirst($approver->role) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('approver_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- TABEL PRODUK/JASA --}}
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 25%;">Produk</th>
                                <th>Deskripsi</th>
                                <th style="width: 10%;">Qty</th>
                                <th style="width: 10%;">Unit</th>
                                <th style="width: 15%;">Harga</th>
                                <th style="width: 10%;">Disc%</th>
                                <th class="text-right" style="width: 15%;">Jumlah</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body">
                            @if(old('produk_id'))
                                @foreach(old('produk_id') as $index => $oldPid)
                                <tr>
                                    <td>
                                        <select class="form-control product-select" name="produk_id[]" required>
                                            <option value="">Pilih...</option>
                                            @foreach($produks as $p)
                                                <option value="{{ $p->id }}" 
                                                        data-harga="{{ $p->harga }}" 
                                                        data-deskripsi="{{ $p->deskripsi }}"
                                                        {{ $oldPid == $p->id ? 'selected' : '' }}>
                                                    {{ $p->nama_produk }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control product-desc" name="deskripsi[]" value="{{ old('deskripsi.'.$index) }}"></td>
                                    <td><input type="number" class="form-control product-qty" name="kuantitas[]" value="{{ old('kuantitas.'.$index) }}" min="1" required></td>
                                    <td>
                                        <select class="form-control" name="unit[]">
                                            <option value="Pcs" {{ old('unit.'.$index) == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                            <option value="Box" {{ old('unit.'.$index) == 'Box' ? 'selected' : '' }}>Box</option>
                                            <option value="Karton" {{ old('unit.'.$index) == 'Karton' ? 'selected' : '' }}>Karton</option>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" value="{{ old('harga_satuan.'.$index) }}" required></td>
                                    <td><input type="number" class="form-control text-right product-disc" name="diskon[]" value="{{ old('diskon.'.$index) }}" min="0"></td>
                                    <td><input type="text" class="form-control text-right product-total" readonly></td>
                                    <td>@if($index > 0)<button type="button" class="btn btn-danger btn-sm remove-btn">X</button>@endif</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>
                                        <select class="form-control product-select" name="produk_id[]" required>
                                            <option value="">Pilih...</option>
                                            @foreach($produks as $p) 
                                                <option value="{{ $p->id }}" data-harga="{{ $p->harga }}" data-deskripsi="{{ $p->deskripsi }}">{{ $p->nama_produk }}</option> 
                                            @endforeach
                                        </select>
                                    </td>
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
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-link pl-0" id="add-row-btn">+ Tambah Baris</button>
                @error('produk_id.*') <div class="text-danger small mt-2">Error: Produk wajib dipilih</div> @enderror
                @error('kuantitas.*') <div class="text-danger small mt-2">Error: Kuantitas wajib diisi</div> @enderror

                {{-- BAGIAN BAWAH (MEMO & TOTAL) --}}
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="memo">Memo</label>
                            <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="4">{{ old('memo') }}</textarea>
                            @error('memo') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <label for="diskon_akhir_input" class="mb-0"><strong>Diskon Akhir (Rp)</strong></label>
                                    </td>
                                    <td>
                                        {{-- PERBAIKAN ID INPUT --}}
                                        <input type="number" class="form-control text-right" id="diskon_akhir_input" name="diskon_akhir" value="{{ old('diskon_akhir', 0) }}" min="0">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="tax_percentage_input" class="mb-0"><strong>Pajak (%)</strong></label>
                                    </td>
                                    <td style="width: 50%;">
                                        {{-- PERBAIKAN ID INPUT --}}
                                        <input type="number" class="form-control text-right" id="tax_percentage_input" name="tax_percentage" value="{{ old('tax_percentage', 0) }}" min="0" step="0.01">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jumlah Pajak</td>
                                    <td id="tax-amount-display">Rp0</td>
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
            <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DEFINISI VARIABEL YANG KONSISTEN
    const tableBody = document.getElementById('product-table-body');
    const addRowBtn = document.getElementById('add-row-btn');
    
    // Input Kunci (Perhatikan ID harus sama dengan HTML)
    const taxInput = document.getElementById('tax_percentage_input');
    const discAkhirInput = document.getElementById('diskon_akhir_input');
    
    // Display Elements
    const subtotalDisplay = document.getElementById('subtotal-display');
    const taxDisplay = document.getElementById('tax-amount-display');
    const grandTotalBottom = document.getElementById('grand-total-bottom');
    const grandTotalTop = document.getElementById('grand-total-display');
    
    const kontakSelect = document.getElementById('kontak-select');
    const emailInput = document.getElementById('email-input');
    const alamatInput = document.getElementById('alamat-input');

    // --- 2. LOGIKA KALKULASI (AUTO UPDATE) ---
    function formatRupiah(num) { 
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num); 
    }
    
    function calculateRow(row) {
        const qty = parseFloat(row.querySelector('.product-qty').value) || 0;
        const price = parseFloat(row.querySelector('.product-price').value) || 0;
        const disc = parseFloat(row.querySelector('.product-disc').value) || 0;
        const total = (qty * price) * (1 - (disc / 100));
        row.querySelector('.product-total').value = total.toFixed(0);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let subtotal = 0;
        tableBody.querySelectorAll('.product-total').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });

        let diskonAkhir = parseFloat(discAkhirInput.value) || 0;
        let kenaPajak = Math.max(0, subtotal - diskonAkhir);
        
        let taxPercentage = parseFloat(taxInput.value) || 0;
        let taxAmount = kenaPajak * (taxPercentage / 100);
        
        let grandTotal = kenaPajak + taxAmount;
        
        // UPDATE SEMUA DISPLAY (ATAS & BAWAH)
        subtotalDisplay.innerText = formatRupiah(subtotal);
        taxDisplay.innerText = formatRupiah(taxAmount);
        grandTotalBottom.innerText = formatRupiah(grandTotal);
        grandTotalTop.innerText = `Total ${formatRupiah(grandTotal)}`;
    }

    // --- 3. EVENT LISTENERS (AGAR REALTIME) ---
    
    // Listener untuk Input di Tabel (Qty, Harga, Disc)
    document.addEventListener('input', function(e) {
        if(e.target.matches('.product-qty, .product-price, .product-disc')) {
            calculateRow(e.target.closest('tr'));
        }
        // Listener KHUSUS untuk Diskon Akhir & Pajak
        if(e.target.id === 'diskon_akhir_input' || e.target.id === 'tax_percentage_input') {
            calculateGrandTotal();
        }
    });

    // Explicit Listener untuk Diskon & Pajak (Double check)
    taxInput.addEventListener('input', calculateGrandTotal);
    discAkhirInput.addEventListener('input', calculateGrandTotal);

    // --- 4. AUTOFILL KONTAK ---
    if(kontakSelect){
        kontakSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            emailInput.value = selectedOption.dataset.email || '';
            alamatInput.value = selectedOption.dataset.alamat || '';
            
            const disc = selectedOption.dataset.diskon || 0;
            tableBody.querySelectorAll('.product-disc').forEach(input => {
                input.value = disc;
            });
            // Hitung ulang baris karena diskon berubah
            Array.from(tableBody.rows).forEach(row => calculateRow(row));
        });
    }

    // --- 5. JATUH TEMPO ---
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
    }
    document.getElementById('tgl_transaksi').addEventListener('change', updateDueDate);
    document.getElementById('syarat_pembayaran').addEventListener('change', updateDueDate);
    updateDueDate(); 

    // --- 6. PRODUK CHANGE ---
    tableBody.addEventListener('change', function(e) {
        if(e.target.classList.contains('product-select')) {
            let option = e.target.options[e.target.selectedIndex];
            let row = e.target.closest('tr');
            row.querySelector('.product-price').value = option.dataset.harga || 0;
            row.querySelector('.product-desc').value = option.dataset.deskripsi || '';
            
            // Cek Diskon Kontak
            const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
            if(kontakOption && kontakOption.value) {
                 row.querySelector('.product-disc').value = kontakOption.dataset.diskon || 0;
            }
            calculateRow(row);
        }
    });

    // --- 7. ADD/REMOVE ROW ---
    document.getElementById('add-row-btn').addEventListener('click', function() {
        let row = tableBody.rows[0].cloneNode(true);
        row.querySelectorAll('input').forEach(i => i.value = '');
        row.querySelector('.product-qty').value = 1;
        row.querySelector('.product-price').value = 0;
        
        // Terapkan diskon kontak ke baris baru
        const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
        if(kontakOption && kontakOption.value) {
             row.querySelector('.product-disc').value = kontakOption.dataset.diskon || 0;
        } else {
             row.querySelector('.product-disc').value = 0;
        }

        if(!row.querySelector('.remove-btn')) {
            let td = row.lastElementChild;
            td.innerHTML = '<button type="button" class="btn btn-danger btn-sm remove-btn">X</button>';
        }
        tableBody.appendChild(row);
    });

    tableBody.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-btn')) {
            e.target.closest('tr').remove();
            calculateGrandTotal();
        }
    });
    
    // Init Calc
    tableBody.querySelectorAll('tr').forEach(row => calculateRow(row));

    // --- 8. KOORDINAT LOKASI ---
    const koordinatInput = document.getElementById('koordinat');
    const btnGetLocation = document.getElementById('btn-get-location');
    const btnOpenMaps = document.getElementById('btn-open-maps');

    // Ambil lokasi saat ini
    if(btnGetLocation) {
        btnGetLocation.addEventListener('click', function() {
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
                        alert('Gagal mendapatkan lokasi: ' + error.message);
                        btnGetLocation.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                    }
                );
            } else {
                alert('Browser tidak mendukung Geolocation');
            }
        });
    }

    // Update link Google Maps
    function updateMapsLink() {
        const coords = koordinatInput.value.trim();
        if(coords && coords.includes(',')) {
            btnOpenMaps.href = 'https://www.google.com/maps?q=' + coords.replace(' ', '');
            btnOpenMaps.classList.remove('disabled');
        } else {
            btnOpenMaps.href = '#';
            btnOpenMaps.classList.add('disabled');
        }
    }

    koordinatInput.addEventListener('input', updateMapsLink);
    updateMapsLink();

    // Auto-get location saat halaman load
    if (navigator.geolocation && !koordinatInput.value) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                koordinatInput.value = lat + ', ' + lng;
                updateMapsLink();
            },
            function(error) {
                console.log('Auto-location failed: ' + error.message);
            }
        );
    }
});
</script>
@endpush