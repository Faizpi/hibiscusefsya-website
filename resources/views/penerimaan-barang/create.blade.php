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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pembelian_id">Invoice Pembelian *</label>
                                <select class="form-control @error('pembelian_id') is-invalid @enderror" 
                                    id="pembelian_id" name="pembelian_id" required>
                                    <option value="">Pilih Invoice Pembelian...</option>
                                    @foreach($pembelianBelumLunas as $pembelian)
                                        <option value="{{ $pembelian->id }}" 
                                            data-supplier="{{ $pembelian->nama_supplier ?? '-' }}"
                                            {{ old('pembelian_id') == $pembelian->id ? 'selected' : '' }}>
                                            {{ $pembelian->nomor ?? $pembelian->custom_number }} - 
                                            {{ $pembelian->nama_supplier ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pembelian_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Preview Nomor</label>
                                <input type="text" class="form-control" value="{{ $previewNomor }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tgl_penerimaan">Tanggal Penerimaan *</label>
                                <input type="date" class="form-control @error('tgl_penerimaan') is-invalid @enderror"
                                    id="tgl_penerimaan" name="tgl_penerimaan" 
                                    value="{{ old('tgl_penerimaan', date('Y-m-d')) }}" required>
                                @error('tgl_penerimaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_surat_jalan">No. Surat Jalan</label>
                                <input type="text" class="form-control @error('no_surat_jalan') is-invalid @enderror"
                                    id="no_surat_jalan" name="no_surat_jalan" value="{{ old('no_surat_jalan') }}"
                                    placeholder="Contoh: SJ-001">
                                @error('no_surat_jalan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lampiran">Lampiran (Surat Jalan, dll)</label>
                                <input type="file" class="form-control-file" id="lampiran" name="lampiran[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Format: JPG, PNG, PDF. Maks 2MB per file.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Items Table --}}
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-boxes"></i> Detail Barang Diterima
                    </h6>
                    
                    <div id="items-container" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="items-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="10%">Kode</th>
                                        <th width="30%">Nama Produk</th>
                                        <th width="10%">Satuan</th>
                                        <th width="12%">Qty Pesan</th>
                                        <th width="12%">Sudah Diterima</th>
                                        <th width="12%">Sisa</th>
                                        <th width="14%">Qty Diterima *</th>
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
    $('#pembelian_id').change(function() {
        var pembelianId = $(this).val();
        
        if (!pembelianId) {
            $('#items-container').hide();
            $('#no-pembelian-selected').show();
            $('#btn-submit').prop('disabled', true);
            return;
        }

        // Fetch pembelian details via AJAX
        $.ajax({
            url: '/penerimaan-barang/get-pembelian/' + pembelianId,
            type: 'GET',
            success: function(data) {
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
                $('#no-pembelian-selected').hide();
                $('#btn-submit').prop('disabled', false);
            },
            error: function() {
                alert('Gagal memuat data pembelian.');
            }
        });
    });

    // Trigger on page load if there's old value
    if ($('#pembelian_id').val()) {
        $('#pembelian_id').trigger('change');
    }
});
</script>
@endpush
