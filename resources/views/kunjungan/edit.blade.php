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
                                <label for="sales_nama">Sales/Kontak *</label>
                                <select class="form-control @error('sales_nama') is-invalid @enderror" id="sales_nama"
                                    name="sales_nama" required>
                                    <option value="">Pilih kontak...</option>
                                    @foreach($kontaks as $kontak)
                                        <option value="{{ $kontak->nama }}" 
                                            data-email="{{ $kontak->email }}" 
                                            data-alamat="{{ $kontak->alamat }}"
                                            {{ old('sales_nama', $kunjungan->sales_nama) == $kontak->nama ? 'selected' : '' }}>
                                            {{ $kontak->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sales_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    <option value="Penawaran" {{ old('tujuan', $kunjungan->tujuan) == 'Penawaran' ? 'selected' : '' }}>
                                        Kunjungan Penawaran
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
                                <label for="lampiran">Lampiran</label>
                                @if($kunjungan->lampiran_path)
                                    <div class="mb-2">
                                        <a href="{{ asset('storage/' . $kunjungan->lampiran_path) }}" target="_blank"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-file"></i> Lihat Lampiran Saat Ini
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control-file @error('lampiran') is-invalid @enderror"
                                    id="lampiran" name="lampiran" accept=".jpg,.png,.pdf,.zip,.doc,.docx">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah lampiran</small>
                                @error('lampiran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

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
        // Auto-fill email dan alamat saat kontak dipilih
        $('#sales_nama').on('change', function() {
            var selected = $(this).find(':selected');
            var email = selected.data('email') || '';
            var alamat = selected.data('alamat') || '';
            
            $('#sales_email').val(email);
            $('#sales_alamat').val(alamat);
        });

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
    });
</script>
@endpush
