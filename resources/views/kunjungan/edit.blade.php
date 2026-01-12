@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Kunjungan #{{ $kunjungan->custom_number }}</h1>
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

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('kunjungan.update', $kunjungan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card shadow mb-4">
                <div class="card-body">
                    {{-- BAGIAN ATAS FORM --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. Kunjungan</label>
                                <input type="text" class="form-control" value="{{ $kunjungan->custom_number }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gudang</label>
                                <input type="text" class="form-control" value="{{ optional($kunjungan->gudang)->nama_gudang ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- DETAIL KUNJUNGAN --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kontak_id">Sales/Kontak *</label>
                                <select class="form-control @error('kontak_id') is-invalid @enderror" id="kontak_id"
                                    name="kontak_id" required>
                                    <option value="">Pilih kontak...</option>
                                    @foreach($kontaks as $kontak)
                                        <option value="{{ $kontak->id }}" 
                                            data-nama="{{ $kontak->nama }}"
                                            data-kode="{{ $kontak->kode_kontak }}"
                                            data-email="{{ $kontak->email }}" 
                                            data-alamat="{{ $kontak->alamat }}"
                                            {{ old('kontak_id', $kunjungan->kontak_id) == $kontak->id ? 'selected' : '' }}>
                                            [{{ $kontak->kode_kontak }}] {{ $kontak->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="sales_nama" id="sales_nama_hidden" value="{{ old('sales_nama', $kunjungan->sales_nama) }}">
                                @error('kontak_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label for="sales_email">Email</label>
                                <input type="email" class="form-control @error('sales_email') is-invalid @enderror"
                                    id="sales_email" name="sales_email" 
                                    value="{{ old('sales_email', $kunjungan->sales_email) }}"
                                    placeholder="email@contoh.com">
                                @error('sales_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label for="sales_alamat">Alamat</label>
                                <textarea class="form-control @error('sales_alamat') is-invalid @enderror"
                                    id="sales_alamat" name="sales_alamat"
                                    rows="3">{{ old('sales_alamat', $kunjungan->sales_alamat) }}</textarea>
                                @error('sales_alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Kunjungan</label>
                                        <input type="text" class="form-control" 
                                            value="{{ $kunjungan->tgl_kunjungan->format('d F Y') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <input type="text" class="form-control" value="{{ $kunjungan->status }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="tujuan">Tujuan Kunjungan *</label>
                                <select class="form-control @error('tujuan') is-invalid @enderror" id="tujuan"
                                    name="tujuan" required>
                                    <option value="">Pilih tujuan...</option>
                                    <option value="Pemeriksaan Stock" {{ old('tujuan', $kunjungan->tujuan) == 'Pemeriksaan Stock' ? 'selected' : '' }}>
                                        Kunjungan Pemeriksaan Stock
                                    </option>
                                    <option value="Penagihan" {{ old('tujuan', $kunjungan->tujuan) == 'Penagihan' ? 'selected' : '' }}>
                                        Kunjungan Penagihan
                                    </option>
                                    <option value="Promo" {{ old('tujuan', $kunjungan->tujuan) == 'Promo' ? 'selected' : '' }}>
                                        Kunjungan Promo
                                    </option>
                                </select>
                                @error('tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- KOORDINAT LOKASI --}}
                            <div class="form-group">
                                <label for="koordinat">Koordinat Lokasi</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="koordinat" name="koordinat"
                                        value="{{ old('koordinat', $kunjungan->koordinat) }}" 
                                        placeholder="-6.123456, 106.123456" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" id="btn-get-location"
                                            title="Refresh Lokasi">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </button>
                                        <a href="{{ $kunjungan->koordinat ? 'https://www.google.com/maps?q=' . $kunjungan->koordinat : '#' }}" 
                                            class="btn btn-outline-success" id="btn-open-maps" target="_blank"
                                            title="Buka di Google Maps">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="lampiran">Lampiran Tambahan <small class="text-muted">(dapat memilih banyak file)</small></label>
                                @php
                                    $allLampiran = [];
                                    if($kunjungan->lampiran_path) {
                                        $allLampiran[] = $kunjungan->lampiran_path;
                                    }
                                    if($kunjungan->lampiran_paths) {
                                        $allLampiran = array_merge($allLampiran, $kunjungan->lampiran_paths);
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
                    </div>

                    <hr>

                    {{-- PRODUK ITEMS --}}
                    <h5 class="text-primary mb-3"><i class="fas fa-boxes"></i> Produk Terkait</h5>
                    <div id="produk-container">
                        @forelse($kunjungan->items as $index => $item)
                        <div class="row produk-row mb-2 align-items-center">
                            <div class="col-md-7">
                                <select class="form-control produk-select" name="produk_id[]">
                                    <option value="">Pilih produk...</option>
                                    @foreach($produks as $produk)
                                        <option value="{{ $produk->id }}" 
                                            data-kode="{{ $produk->item_code }}"
                                            {{ $item->produk_id == $produk->id ? 'selected' : '' }}>
                                            [{{ $produk->item_code }}] {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control produk-qty" name="jumlah[]" value="{{ $item->jumlah ?? 1 }}" min="1" placeholder="Qty">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-info btn-sm btn-scan-produk" title="Scan Barcode">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-produk">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="row produk-row mb-2 align-items-center">
                            <div class="col-md-7">
                                <select class="form-control produk-select" name="produk_id[]">
                                    <option value="">Pilih produk...</option>
                                    @foreach($produks as $produk)
                                        <option value="{{ $produk->id }}" data-kode="{{ $produk->item_code }}">
                                            [{{ $produk->item_code }}] {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control produk-qty" name="jumlah[]" value="1" min="1" placeholder="Qty">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-info btn-sm btn-scan-produk" title="Scan Barcode">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-produk" style="display:none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-produk">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>

                    <hr>

                    {{-- MEMO --}}
                    <div class="form-group">
                        <label for="memo">Memo / Catatan</label>
                        <textarea class="form-control" id="memo" name="memo" rows="3">{{ old('memo', $kunjungan->memo) }}</textarea>
                    </div>

                </div>

                <div class="card-footer">
                    <a href="{{ route('kunjungan.show', $kunjungan->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fas fa-save"></i> Update Kunjungan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Predefined elements
        const kontakSelect = document.getElementById('kontak_id');
        const salesNamaHidden = document.getElementById('sales_nama_hidden');
        const salesEmailInput = document.getElementById('sales_email');
        const salesAlamatInput = document.getElementById('sales_alamat');

        // Init Select2 untuk dropdown Kontak (searchable)
        $('#kontak_id').select2({
            placeholder: 'Cari kontak...',
            allowClear: true,
            width: '100%'
        }).on('select2:select', function(e) {
            // Auto-fill email dan alamat saat kontak dipilih
            const selectedOption = this.options[this.selectedIndex];
            salesNamaHidden.value = selectedOption.dataset.nama || '';
            salesEmailInput.value = selectedOption.dataset.email || '';
            salesAlamatInput.value = selectedOption.dataset.alamat || '';
        });

        // Init Select2 untuk produk
        function initProdukSelect2() {
            $('.produk-select').select2({
                placeholder: 'Pilih produk...',
                allowClear: true,
                width: '100%'
            });
        }
        initProdukSelect2();

        // Tambah baris produk
        $('#btn-add-produk').on('click', function () {
            const newRow = `
                <div class="row produk-row mb-2 align-items-center">
                    <div class="col-md-7">
                        <select class="form-control produk-select" name="produk_id[]">
                            <option value="">Pilih produk...</option>
                            @foreach($produks as $produk)
                                <option value="{{ $produk->id }}" data-kode="{{ $produk->item_code }}">
                                    [{{ $produk->item_code }}] {{ $produk->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control produk-qty" name="jumlah[]" value="1" min="1" placeholder="Qty">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-info btn-sm btn-scan-produk" title="Scan Barcode">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-produk">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#produk-container').append(newRow);
            initProdukSelect2();
            updateRemoveButtons();
        });

        // Hapus baris produk
        $(document).on('click', '.btn-remove-produk', function () {
            $(this).closest('.produk-row').remove();
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const rows = $('.produk-row');
            rows.each(function(index) {
                $(this).find('.btn-remove-produk').toggle(rows.length > 1);
            });
        }
        updateRemoveButtons();

        // Refresh location button
        $('#btn-get-location').on('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude.toFixed(6);
                    var lng = position.coords.longitude.toFixed(6);
                    var koordinat = lat + ', ' + lng;
                    $('#koordinat').val(koordinat);
                    $('#btn-open-maps').attr('href', 'https://www.google.com/maps?q=' + lat + ',' + lng);
                });
            }
        });

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
