@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Penagihan Penjualan #{{ $penjualan->custom_number ?? $penjualan->id }}
            </h1>
            <h3 class="font-weight-bold text-right" id="grand-total-display">Total Rp0,00</h3>
        </div>

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
                                                    {{ $kontak->nama }}</option>
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
                            <div class="form-group">
                                <label>Koordinat (GPS)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="koordinat" name="koordinat"
                                        value="{{ old('koordinat', $penjualan->koordinat) }}"
                                        placeholder="-6.123456, 106.123456" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" id="btn-get-location"
                                            title="Ambil Lokasi Saat Ini">
                                            <i class="fa fa-map-marker-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" id="btn-open-maps"
                                            title="Buka di Google Maps">
                                            <i class="fa fa-external-link-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group"><label>Tag</label><input type="text" class="form-control" name="tag"
                                    value="{{ old('tag', $penjualan->tag) }}" readonly></div>

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

                            <div class="form-group">
                                <label>Approver (Admin) *</label>
                                <select class="form-control" name="approver_id" required>
                                    @foreach($approvers as $a) <option value="{{ $a->id }}" {{ old('approver_id', $penjualan->approver_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL PRODUK --}}
                    <div class="table-responsive mt-3">
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
                                                    data-harga="{{ $p->harga }}" data-deskripsi="{{ $p->deskripsi }}" {{ $oldProd == $p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control product-description" name="deskripsi[]"
                                                value="{{ $oldDesc }}"></td>
                                        <td><input type="number" class="form-control product-quantity" name="kuantitas[]"
                                                value="{{ $oldQty }}" min="1" required></td>
                                        <td><select class="form-control" name="unit[]">
                                                <option {{ $oldUnit == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                                <option {{ $oldUnit == 'Box' ? 'selected' : '' }}>Box</option>
                                            </select></td>
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
                    <button type="button" class="btn btn-link pl-0" id="add-product-row">+ Tambah Data</button>

                    {{-- TOTAL --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group"><label>Memo</label><textarea class="form-control" name="memo"
                                    rows="3">{{ old('memo', $penjualan->memo) }}</textarea></div>
                            <div class="form-group"><label>Lampiran</label><input type="file" class="form-control-file"
                                    name="lampiran"></div>
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
            const taxInput = document.getElementById('tax_percentage_input');
            const discAkhirInput = document.getElementById('diskon_akhir_input');
            const kontakSelect = document.getElementById('kontak-select');
            const emailInput = document.getElementById('email-input');
            const alamatInput = document.getElementById('alamat-input');

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
                    <option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" data-deskripsi="{{ $produk->deskripsi }}">{{ $produk->nama_produk }}</option>
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
                let kenaPajak = Math.max(0, subtotal - diskonAkhir);
                let taxPercentage = parseFloat(taxInput.value) || 0;
                let taxAmount = kenaPajak * (taxPercentage / 100);
                const total = kenaPajak + taxAmount;
                document.getElementById('subtotal-display').innerText = formatRupiah(subtotal);
                document.getElementById('tax-amount-display').innerText = formatRupiah(taxAmount);
                document.getElementById('grand-total-display').innerText = `Total ${formatRupiah(total)}`;
                document.getElementById('grand-total-bottom').innerText = formatRupiah(total);
            };

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
                calculateRow(row);
            };

            tableBody.addEventListener('input', function (event) {
                if (event.target.matches('.product-quantity, .product-price, .product-discount')) calculateRow(event.target.closest('tr'));
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
                <td><select class="form-control" name="unit[]"><option>Pcs</option><option>Karton</option></select></td>
                <td><input type="number" class="form-control text-right product-price" name="harga_satuan[]" placeholder="0" required></td>
                <td><input type="number" class="form-control text-right product-discount" name="diskon[]" placeholder="0" min="0" max="100"></td>
                <td><input type="text" class="form-control text-right product-line-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row-btn">X</button></td>
            `;
                const kontakOption = kontakSelect.options[kontakSelect.selectedIndex];
                if (kontakOption) newRow.querySelector('.product-discount').value = kontakOption.dataset.diskon || 0;
            });

            tableBody.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-row-btn')) {
                    event.target.closest('tr').remove();
                    calculateGrandTotal();
                }
            });

            // INIT: Hitung semua baris saat halaman dimuat
            setTimeout(function () {
                tableBody.querySelectorAll('tr').forEach(row => calculateRow(row));
            }, 100);

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
                    btnGetLocation.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude.toFixed(6);
                            const lng = position.coords.longitude.toFixed(6);
                            koordinatInput.value = lat + ', ' + lng;
                            btnGetLocation.innerHTML = '<i class="fa fa-map-marker-alt"></i>';
                            updateMapsLink();
                        },
                        function(error) {
                            console.log('Location error: ' + error.message);
                            btnGetLocation.innerHTML = '<i class="fa fa-map-marker-alt"></i>';
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