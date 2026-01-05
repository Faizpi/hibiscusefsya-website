@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Buat Pembayaran</h1>
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

        <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave"></i> Form Pembayaran
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="penjualan_id">Invoice Penjualan (Belum Lunas) *</label>
                                <select class="form-control @error('penjualan_id') is-invalid @enderror" 
                                    id="penjualan_id" name="penjualan_id" required>
                                    <option value="">Pilih Invoice...</option>
                                    @foreach($penjualanBelumLunas as $penjualan)
                                        @php
                                            $totalBayar = \App\Pembayaran::where('penjualan_id', $penjualan->id)
                                                ->where('status', 'Approved')
                                                ->sum('jumlah_bayar');
                                            $sisa = $penjualan->grand_total - $totalBayar;
                                        @endphp
                                        <option value="{{ $penjualan->id }}" 
                                            data-total="{{ $penjualan->grand_total }}"
                                            data-bayar="{{ $totalBayar }}"
                                            data-sisa="{{ $sisa }}"
                                            data-kontak="{{ $penjualan->pelanggan ?? '-' }}"
                                            {{ old('penjualan_id') == $penjualan->id ? 'selected' : '' }}>
                                            {{ $penjualan->nomor ?? $penjualan->custom_number }} - 
                                            {{ $penjualan->pelanggan ?? '-' }} - 
                                            Sisa: Rp {{ number_format($sisa, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('penjualan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Preview Nomor</label>
                                <input type="text" class="form-control" value="{{ $previewNomor }}" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Invoice --}}
                    <div class="row" id="invoice-detail" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Total Invoice:</strong> <span id="info-total">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Sudah Dibayar:</strong> <span id="info-bayar">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Sisa Hutang:</strong> <span id="info-sisa" class="text-danger font-weight-bold">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tgl_pembayaran">Tanggal Pembayaran *</label>
                                <input type="date" class="form-control @error('tgl_pembayaran') is-invalid @enderror"
                                    id="tgl_pembayaran" name="tgl_pembayaran" 
                                    value="{{ old('tgl_pembayaran', date('Y-m-d')) }}" required>
                                @error('tgl_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="metode_pembayaran">Metode Pembayaran *</label>
                                <select class="form-control @error('metode_pembayaran') is-invalid @enderror"
                                    id="metode_pembayaran" name="metode_pembayaran" required>
                                    <option value="Cash" {{ old('metode_pembayaran') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Transfer Bank" {{ old('metode_pembayaran') == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="Giro" {{ old('metode_pembayaran') == 'Giro' ? 'selected' : '' }}>Giro</option>
                                    <option value="QRIS" {{ old('metode_pembayaran') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                                    <option value="Lainnya" {{ old('metode_pembayaran') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('metode_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jumlah_bayar">Jumlah Bayar *</label>
                                <input type="number" class="form-control @error('jumlah_bayar') is-invalid @enderror"
                                    id="jumlah_bayar" name="jumlah_bayar" 
                                    value="{{ old('jumlah_bayar') }}" min="1" required>
                                @error('jumlah_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lampiran">Bukti Pembayaran</label>
                                <input type="file" class="form-control-file" id="lampiran" name="lampiran[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Format: JPG, PNG, PDF. Maks 2MB per file.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fas fa-save"></i> Simpan Pembayaran
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
    }

    $('#penjualan_id').change(function() {
        var selected = $(this).find(':selected');
        if (selected.val()) {
            var total = selected.data('total');
            var bayar = selected.data('bayar');
            var sisa = selected.data('sisa');

            $('#info-total').text(formatRupiah(total));
            $('#info-bayar').text(formatRupiah(bayar));
            $('#info-sisa').text(formatRupiah(sisa));
            $('#invoice-detail').show();

            // Auto-fill jumlah bayar dengan sisa hutang
            $('#jumlah_bayar').val(sisa).attr('max', sisa);
        } else {
            $('#invoice-detail').hide();
        }
    });

    // Trigger on page load if there's old value
    if ($('#penjualan_id').val()) {
        $('#penjualan_id').trigger('change');
    }
});
</script>
@endpush
