@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Buat Penerimaan Barang</h1>
        </div>

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

        <form action="{{ route('penerimaan-barang.store') }}" method="POST" enctype="multipart/form-data" id="formPenerimaan">
            @csrf
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-truck-loading"></i> Form Penerimaan Barang
                    </h6>
                </div>
                <div class="card-body">
                    {{-- ROW 1: Gudang & Preview Nomor --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gudang_id">Gudang *</label>
                                @if(auth()->user()->role === 'super_admin' && $gudangs->count() > 0)
                                    <select class="form-control @error('gudang_id') is-invalid @enderror" 
                                        id="gudang_id" name="gudang_id" required>
                                        @foreach($gudangs as $gudang)
                                            <option value="{{ $gudang->id }}" 
                                                {{ $selectedGudang && $selectedGudang->id == $gudang->id ? 'selected' : '' }}>
                                                {{ $gudang->nama_gudang }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="gudang_id" value="{{ $selectedGudang->id ?? '' }}">
                                    <input type="text" class="form-control bg-light" 
                                        value="{{ $selectedGudang->nama_gudang ?? 'Tidak ada gudang' }}" readonly>
                                @endif
                                @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Preview Nomor</label>
                                <input type="text" class="form-control bg-light text-primary font-weight-bold" 
                                    value="{{ $previewNomor }}" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: Invoice Pembelian --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pembelian_id">Invoice Pembelian *</label>
                                <select class="form-control @error('pembelian_id') is-invalid @enderror" 
                                    id="pembelian_id" name="pembelian_id" required>
                                    <option value="">-- Pilih Invoice Pembelian --</option>
                                </select>
                                @error('pembelian_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tgl_penerimaan">Tanggal Penerimaan *</label>
                                <input type="date" class="form-control @error('tgl_penerimaan') is-invalid @enderror"
                                    id="tgl_penerimaan" name="tgl_penerimaan" 
                                    value="{{ old('tgl_penerimaan', date('Y-m-d')) }}" required>
                                @error('tgl_penerimaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="no_surat_jalan">No. Surat Jalan</label>
                                <input type="text" class="form-control @error('no_surat_jalan') is-invalid @enderror"
                                    id="no_surat_jalan" name="no_surat_jalan" value="{{ old('no_surat_jalan') }}"
                                    placeholder="Contoh: SJ-001">
                                @error('no_surat_jalan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ROW 3: Keterangan & Lampiran --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lampiran (Surat Jalan, dll)</label>
                                <div class="custom-file-container">
                                    <input type="file" class="form-control-file" id="lampiran" name="lampiran[]" 
                                        multiple accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted d-block">Format: JPG, PNG, PDF. Maks 2MB per file. Bisa upload multiple.</small>
                                </div>
                                <div id="lampiran-preview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Items Table --}}
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-boxes"></i> Detail Barang Diterima
                    </h6>
                    
                    <div id="items-loading" class="text-center py-4" style="display: none;">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 mb-0">Memuat data barang...</p>
                    </div>

                    <div id="items-container" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="items-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="12%">Kode</th>
                                        <th width="30%">Nama Produk</th>
                                        <th width="10%">Satuan</th>
                                        <th width="12%" class="text-center">Qty Pesan</th>
                                        <th width="12%" class="text-center">Sudah Diterima</th>
                                        <th width="12%" class="text-center">Sisa</th>
                                        <th width="12%">Qty Diterima *</th>
                                    </tr>
                                </thead>
                                <tbody id="items-body">
                                    <!-- Items will be loaded via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="no-pembelian-selected" class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Pilih invoice pembelian terlebih dahulu untuk melihat daftar barang.
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('penerimaan-barang.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right" id="btn-submit" disabled>
                        <i class="fas fa-save"></i> Simpan Penerimaan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function loadPembelianList(gudangId) {
        if (!gudangId) {
            $('#pembelian_id').html('<option value="">-- Pilih Invoice Pembelian --</option>');
            return;
        }

        $.ajax({
            url: '/penerimaan-barang/get-pembelian-by-gudang/' + gudangId,
            type: 'GET',
            success: function(data) {
                var html = '<option value="">-- Pilih Invoice Pembelian --</option>';
                data.forEach(function(item) {
                    html += '<option value="' + item.id + '">' + item.nomor + ' - ' + item.nama_supplier + '</option>';
                });
                $('#pembelian_id').html(html);
            },
            error: function() {
                alert('Gagal memuat daftar pembelian.');
            }
        });
    }

    function loadPembelianDetail(pembelianId) {
        if (!pembelianId) {
            $('#items-container').hide();
            $('#no-pembelian-selected').show();
            $('#btn-submit').prop('disabled', true);
            return;
        }

        $('#items-loading').show();
        $('#items-container').hide();
        $('#no-pembelian-selected').hide();

        $.ajax({
            url: '/penerimaan-barang/get-pembelian/' + pembelianId,
            type: 'GET',
            success: function(data) {
                $('#items-loading').hide();
                
                if (!data.items || data.items.length === 0) {
                    $('#no-pembelian-selected').html('<i class="fas fa-exclamation-triangle text-warning"></i> Tidak ada item dalam invoice ini.').show();
                    $('#btn-submit').prop('disabled', true);
                    return;
                }

                var html = '';
                data.items.forEach(function(item, index) {
                    html += '<tr>';
                    html += '<td>' + item.produk_kode + '</td>';
                    html += '<td>' + item.produk_nama + '</td>';
                    html += '<td>' + item.satuan + '</td>';
                    html += '<td class="text-center">' + item.qty_pesan + '</td>';
                    html += '<td class="text-center">' + item.qty_diterima + '</td>';
                    html += '<td class="text-center text-primary font-weight-bold">' + item.qty_sisa + '</td>';
                    html += '<td>';
                    html += '<input type="hidden" name="items[' + index + '][produk_id]" value="' + item.produk_id + '">';
                    html += '<input type="number" class="form-control form-control-sm qty-input" ';
                    html += 'name="items[' + index + '][qty_diterima]" value="' + item.qty_sisa + '" ';
                    html += 'min="0" max="' + item.qty_sisa + '">';
                    html += '</td>';
                    html += '</tr>';
                });

                $('#items-body').html(html);
                $('#items-container').show();
                $('#btn-submit').prop('disabled', false);
            },
            error: function() {
                $('#items-loading').hide();
                $('#no-pembelian-selected').html('<i class="fas fa-exclamation-triangle text-danger"></i> Gagal memuat data pembelian.').show();
            }
        });
    }

    // Event: Gudang change
    $('#gudang_id').on('change', function() {
        loadPembelianList($(this).val());
        // Reset pembelian selection
        $('#pembelian_id').val('').trigger('change');
    });

    // Event: Pembelian change
    $('#pembelian_id').on('change', function() {
        loadPembelianDetail($(this).val());
    });

    // Initial load
    var initialGudang = $('#gudang_id').val() || $('input[name="gudang_id"]').val();
    if (initialGudang) {
        loadPembelianList(initialGudang);
    }

    // Lampiran preview
    $('#lampiran').on('change', function() {
        var files = this.files;
        var previewHtml = '';
        
        for (var i = 0; i < files.length; i++) {
            previewHtml += '<span class="badge badge-info mr-2 mb-1">';
            previewHtml += '<i class="fas fa-file mr-1"></i>' + files[i].name;
            previewHtml += '</span>';
        }
        
        $('#lampiran-preview').html(previewHtml);
    });
});
</script>
@endpush
