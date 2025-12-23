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
                                <label for="sales_nama">Sales/Kontak *</label>
                                <select class="form-control @error('sales_nama') is-invalid @enderror" id="sales_nama"
                                    name="sales_nama" required>
                                    <option value="">Pilih kontak...</option>
                                    @foreach($kontaks as $kontak)
                                        <option value="{{ $kontak->nama }}" data-email="{{ $kontak->email }}"
                                            data-alamat="{{ $kontak->alamat }}" {{ old('sales_nama') == $kontak->nama ? 'selected' : '' }}>
                                            {{ $kontak->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sales_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <label>No Kunjungan</label>
                                        <input type="text" class="form-control" placeholder="[Auto Generated]" disabled>
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
                                    <option value="Penawaran" {{ old('tujuan') == 'Penawaran' ? 'selected' : '' }}>
                                        Kunjungan Penawaran
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
                                <label for="lampiran">Lampiran</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('lampiran') is-invalid @enderror"
                                        id="lampiran" name="lampiran" accept=".jpg,.png,.pdf,.zip,.doc,.docx">
                                    <label class="custom-file-label" for="lampiran">Pilih file...</label>
                                </div>
                                <small class="text-muted">Format: jpg, png, pdf, zip, doc, docx (max 2MB)</small>
                                @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

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
            const salesNamaSelect = document.getElementById('sales_nama');
            const salesEmailInput = document.getElementById('sales_email');
            const salesAlamatInput = document.getElementById('sales_alamat');

            // Init Select2 untuk dropdown Kontak (searchable)
            $('#sales_nama').select2({
                placeholder: 'Cari kontak...',
                allowClear: true,
                width: '100%'
            }).on('select2:select', function(e) {
                // Auto-fill email dan alamat saat kontak dipilih
                const selectedOption = this.options[this.selectedIndex];
                salesEmailInput.value = selectedOption.dataset.email || '';
                salesAlamatInput.value = selectedOption.dataset.alamat || '';
            });

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
        });
    </script>
@endpush