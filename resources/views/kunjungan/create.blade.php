@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Buat Kunjungan Baru</h1>
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

        <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card shadow mb-4">
                <div class="card-body">
                    {{-- BAGIAN ATAS FORM --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> Approver akan ditentukan otomatis berdasarkan gudang Anda
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gudang</label>
                                <input type="text" class="form-control"
                                    value="{{ $gudang ? $gudang->nama_gudang : 'Tidak ada gudang' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- DETAIL KUNJUNGAN --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kontak_id">Sales/Kontak *</label>
                                <div class="input-group">
                                    <select class="form-control @error('kontak_id') is-invalid @enderror" id="kontak_id"
                                        name="kontak_id" required>
                                        <option value="">Pilih kontak...</option>
                                        @foreach($kontaks as $kontak)
                                            <option value="{{ $kontak->id }}" 
                                                data-nama="{{ $kontak->nama }}"
                                                data-kode="{{ $kontak->kode_kontak }}"
                                                data-email="{{ $kontak->email }}"
                                                data-alamat="{{ $kontak->alamat }}" 
                                                {{ old('kontak_id') == $kontak->id ? 'selected' : '' }}>
                                                [{{ $kontak->kode_kontak }}] {{ $kontak->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-info" onclick="scanKontak(document.getElementById('kontak_id'))" title="Scan Barcode/QR Kontak">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="sales_nama" id="sales_nama_hidden" value="{{ old('sales_nama') }}">
                                @error('kontak_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label for="sales_email">Email</label>
                                <input type="email" class="form-control @error('sales_email') is-invalid @enderror"
                                    id="sales_email" name="sales_email" value="{{ old('sales_email') }}"
                                    placeholder="email@contoh.com">
                                @error('sales_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group">
                                <label for="sales_alamat">Alamat</label>
                                <textarea class="form-control @error('sales_alamat') is-invalid @enderror" id="sales_alamat"
                                    name="sales_alamat" rows="3">{{ old('sales_alamat') }}</textarea>
                                @error('sales_alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tgl_kunjungan_display">Tanggal Kunjungan *</label>
                                        <input type="text" class="form-control" id="tgl_kunjungan_display"
                                            value="{{ date('d F Y') }}" readonly>
                                        <input type="hidden" name="tgl_kunjungan" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No Kunjungan (Preview)</label>
                                        <input type="text" class="form-control bg-light text-primary font-weight-bold" value="{{ $previewNomor ?? '[Auto]' }}" readonly>
                                        <small class="text-muted">Nomor yang akan digenerate</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="tujuan">Tujuan Kunjungan *</label>
                                <select class="form-control @error('tujuan') is-invalid @enderror" id="tujuan" name="tujuan"
                                    required>
                                    <option value="">Pilih tujuan...</option>
                                    <option value="Pemeriksaan Stock" {{ old('tujuan') == 'Pemeriksaan Stock' ? 'selected' : '' }}>
                                        Kunjungan Pemeriksaan Stock
                                    </option>
                                    <option value="Penagihan" {{ old('tujuan') == 'Penagihan' ? 'selected' : '' }}>
                                        Kunjungan Penagihan
                                    </option>
                                    <option value="Promo" {{ old('tujuan') == 'Promo' ? 'selected' : '' }}>
                                        Kunjungan Promo
                                    </option>
                                </select>
                                @error('tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                            <div class="form-group">
                                <label for="lampiran">Lampiran <small class="text-muted">(dapat memilih banyak file)</small></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror @error('lampiran.*') is-invalid @enderror"
                                        id="lampiran" name="lampiran[]" multiple accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx" data-preview-nomor="{{ $previewNomor ?? '' }}">
                                    <label class="custom-file-label" for="lampiran">Pilih file...</label>
                                </div>
                                <div id="lampiran-list" class="mt-2" style="display: none;">
                                    <small class="text-muted">File terpilih:</small>
                                    <ul id="lampiran-file-list" class="list-unstyled mb-0 mt-1"></ul>
                                </div>
                                <small class="text-muted">Format: jpg, jpeg, png, pdf, zip, doc, docx (max 10MB per file)</small>
                                @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @error('lampiran.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- PRODUK ITEMS --}}
                    <div id="produk-section">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-boxes"></i> Produk Terkait 
                            <span id="produk-required-badge" class="badge badge-danger" style="display: none;">Wajib</span>
                            <span id="produk-optional-badge" class="badge badge-secondary" style="display: none;">Opsional</span>
                        </h5>
                        <small class="text-muted d-block mb-3" id="produk-help-text"></small>
                        <div id="produk-container">
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
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-produk">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                    </div> {{-- End produk-section --}}

                    <hr>

                    {{-- MEMO --}}
                    <div class="form-group">
                        <label for="memo">Memo / Catatan</label>
                        <textarea class="form-control" id="memo" name="memo" rows="3">{{ old('memo') }}</textarea>
                    </div>

                </div>

                <div class="card-footer">
                    <a href="{{ route('kunjungan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fas fa-save"></i> Simpan Kunjungan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Predefined elements
            const kontakSelect = document.getElementById('kontak_id');
            const salesNamaHidden = document.getElementById('sales_nama_hidden');
            const salesEmailInput = document.getElementById('sales_email');
            const salesAlamatInput = document.getElementById('sales_alamat');
            const tujuanSelect = document.getElementById('tujuan');

            // Handle tujuan change - update produk requirement
            function updateProdukRequirement() {
                const tujuan = $('#tujuan').val();
                const isPemeriksaanStock = tujuan === 'Pemeriksaan Stock';
                
                // Update badges
                $('#produk-required-badge').toggle(isPemeriksaanStock);
                $('#produk-optional-badge').toggle(!isPemeriksaanStock && tujuan !== '');
                
                // Update help text
                if (isPemeriksaanStock) {
                    $('#produk-help-text').text('Untuk kunjungan Pemeriksaan Stock, minimal 1 produk wajib diisi.');
                } else if (tujuan) {
                    $('#produk-help-text').text('Produk bersifat opsional untuk tujuan ' + tujuan + '.');
                } else {
                    $('#produk-help-text').text('');
                }

                // Update select required state
                if (isPemeriksaanStock) {
                    $('.produk-select').first().attr('required', true);
                } else {
                    $('.produk-select').removeAttr('required');
                }
            }

            // Listen for tujuan changes
            $('#tujuan').on('change', function() {
                updateProdukRequirement();
            });

            // Initial call
            updateProdukRequirement();

            // Init Select2 untuk dropdown Kontak (searchable)
            $('#kontak_id').select2({
                placeholder: 'Cari kontak...',
                allowClear: true,
                width: '100%'
            }).on('select2:select', function (e) {
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
                // Re-apply required state
                updateProdukRequirement();
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

            // Scan barcode produk
            $(document).on('click', '.btn-scan-produk', function () {
                const row = $(this).closest('.produk-row');
                const select = row.find('.produk-select')[0];
                scanProduk(select);
            });

            function updateRemoveButtons() {
                const rows = $('.produk-row');
                rows.each(function(index) {
                    $(this).find('.btn-remove-produk').toggle(rows.length > 1);
                });
            }

            function getLocation() {
                if (navigator.geolocation) {
                    $('#btn-get-location').html('<i class="fas fa-spinner fa-spin"></i>');
                    navigator.geolocation.getCurrentPosition(function (position) {
                        var lat = position.coords.latitude.toFixed(6);
                        var lng = position.coords.longitude.toFixed(6);
                        var koordinat = lat + ', ' + lng;
                        $('#koordinat').val(koordinat);
                        $('#btn-open-maps').attr('href', 'https://www.google.com/maps?q=' + lat + ',' + lng);
                        $('#btn-get-location').html('<i class="fas fa-map-marker-alt"></i>');
                    }, function (error) {
                        console.log('Geolocation error:', error);
                        alert('Tidak dapat mengambil lokasi. Pastikan Anda mengizinkan akses lokasi.');
                        $('#btn-get-location').html('<i class="fas fa-map-marker-alt"></i>');
                    });
                } else {
                    alert('Browser Anda tidak mendukung geolocation.');
                }
            }

            // Get location on page load
            getLocation();

            // Refresh location button
            $('#btn-get-location').on('click', function () {
                getLocation();
            });

            // Update maps link when koordinat changes
            $('#koordinat').on('change', function () {
                var val = $(this).val();
                if (val) {
                    var parts = val.split(',');
                    if (parts.length == 2) {
                        $('#btn-open-maps').attr('href', 'https://www.google.com/maps?q=' + parts[0].trim() + ',' + parts[1].trim());
                    }
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
                            label.textContent = 'Pilih file...';
                        }
                    }
                });
            }
        });
    </script>
@endpush

@section('modals')
    @include('partials.barcode-scanner-modal')
@endsection