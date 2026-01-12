@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Buat Pembayaran</h1>
            <h3 class="font-weight-bold text-right text-primary" id="total-display">Total: Rp 0</h3>
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

        <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data" id="formPembayaran">
            @csrf
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave"></i> Form Tandai Lunas Invoice
                    </h6>
                </div>
                <div class="card-body">
                    {{-- ROW 1: Gudang & Preview Nomor --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gudang_id">Gudang *</label>
                                @if(auth()->user()->role === 'super_admin' && $gudangs->count() > 0)
                                    <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id"
                                        name="gudang_id" required>
                                        @foreach($gudangs as $gudang)
                                            <option value="{{ $gudang->id }}" {{ $selectedGudang && $selectedGudang->id == $gudang->id ? 'selected' : '' }}>
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

                    <hr>

                    {{-- ROW 2: Tanggal & Metode Pembayaran --}}
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
                                    <option value="Cash" {{ old('metode_pembayaran') == 'Cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="Transfer Bank" {{ old('metode_pembayaran') == 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="Giro" {{ old('metode_pembayaran') == 'Giro' ? 'selected' : '' }}>Giro
                                    </option>
                                    <option value="QRIS" {{ old('metode_pembayaran') == 'QRIS' ? 'selected' : '' }}>QRIS
                                    </option>
                                    <option value="Lainnya" {{ old('metode_pembayaran') == 'Lainnya' ? 'selected' : '' }}>
                                        Lainnya</option>
                                </select>
                                @error('metode_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jumlah_bayar">Jumlah Bayar *</label>
                                <input type="number" class="form-control @error('jumlah_bayar') is-invalid @enderror"
                                    id="jumlah_bayar" name="jumlah_bayar" value="{{ old('jumlah_bayar') }}" min="1"
                                    required>
                                @error('jumlah_bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Total piutang terpilih: <span id="total-piutang">Rp
                                        0</span></small>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 3: Keterangan & Lampiran --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan"
                                    rows="2">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lampiran">Lampiran</label>
                                <div class="custom-file">
                                    <input type="file"
                                        class="custom-file-input @error('lampiran') is-invalid @enderror @error('lampiran.*') is-invalid @enderror"
                                        id="lampiran" name="lampiran[]" multiple accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx"
                                        data-preview-nomor="{{ $previewNomor }}">
                                    <label class="custom-file-label" for="lampiran">Pilih file (bisa pilih
                                        banyak)...</label>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Format: jpg, jpeg, png, pdf, zip, doc, docx (max 2MB per file)
                                </small>
                                <div id="lampiran-list" class="mt-2"></div>
                                @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @error('lampiran.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Invoice Selection --}}
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-file-invoice"></i> Pilih Invoice yang Akan Ditandai Lunas
                        <small class="text-muted">(Invoice yang sudah di-approve)</small>
                    </h6>

                    <div id="invoice-container">
                        <div id="invoice-loading" class="text-center py-4" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2 mb-0">Memuat data invoice...</p>
                        </div>

                        <div id="invoice-empty" class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Pilih gudang untuk melihat daftar invoice yang sudah approved
                            dan belum lunas.
                        </div>

                        <div id="invoice-table-wrapper" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="invoice-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%" class="text-center">
                                                <input type="checkbox" id="select-all-invoices">
                                            </th>
                                            <th width="18%">No. Invoice</th>
                                            <th width="18%">Pelanggan</th>
                                            <th width="10%">Tgl Transaksi</th>
                                            <th width="10%">Syarat Bayar</th>
                                            <th width="12%" class="text-right">Total</th>
                                            <th width="12%" class="text-right">Sudah Bayar</th>
                                            <th width="15%" class="text-right">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoice-body">
                                        <!-- Data akan dimuat via AJAX -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="6" class="text-right">Total Piutang Terpilih:</td>
                                            <td class="text-right text-primary" id="total-selected">Rp 0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right" id="btn-submit" disabled>
                        <i class="fas fa-save"></i> Simpan Pembayaran
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            function formatRupiah(angka) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
            }

            function loadInvoices(gudangId) {
                if (!gudangId) {
                    $('#invoice-empty').show();
                    $('#invoice-table-wrapper').hide();
                    $('#btn-submit').prop('disabled', true);
                    return;
                }

                $('#invoice-loading').show();
                $('#invoice-empty').hide();
                $('#invoice-table-wrapper').hide();

                $.ajax({
                    url: '/pembayaran/get-penjualan-by-gudang/' + gudangId,
                    type: 'GET',
                    success: function (data) {
                        $('#invoice-loading').hide();

                        if (data.length === 0) {
                            $('#invoice-empty').html('<i class="fas fa-check-circle text-success"></i> Semua invoice sudah lunas di gudang ini.').show();
                            $('#btn-submit').prop('disabled', true);
                            return;
                        }

                        var html = '';
                        data.forEach(function (inv) {
                            html += '<tr>';
                            html += '<td class="text-center">';
                            html += '<input type="checkbox" class="invoice-checkbox" name="penjualan_ids[]" ';
                            html += 'value="' + inv.id + '" data-sisa="' + inv.sisa + '">';
                            html += '</td>';
                            html += '<td>' + inv.nomor + '</td>';
                            html += '<td>' + inv.pelanggan + '</td>';
                            html += '<td>' + inv.tgl_transaksi + '</td>';
                            html += '<td><span class="badge badge-' + (inv.syarat_pembayaran === 'Cash' ? 'success' : 'warning') + '">' + inv.syarat_pembayaran + '</span></td>';
                            html += '<td class="text-right">' + formatRupiah(inv.grand_total) + '</td>';
                            html += '<td class="text-right text-muted">' + formatRupiah(inv.total_bayar) + '</td>';
                            html += '<td class="text-right text-primary font-weight-bold">' + formatRupiah(inv.sisa) + '</td>';
                            html += '</tr>';
                        });

                        $('#invoice-body').html(html);
                        $('#invoice-table-wrapper').show();

                        // Bind checkbox events
                        bindCheckboxEvents();
                    },
                    error: function () {
                        $('#invoice-loading').hide();
                        $('#invoice-empty').html('<i class="fas fa-exclamation-triangle text-danger"></i> Gagal memuat data invoice.').show();
                    }
                });
            }

            function bindCheckboxEvents() {
                // Select all checkbox
                $('#select-all-invoices').off('change').on('change', function () {
                    $('.invoice-checkbox').prop('checked', $(this).is(':checked'));
                    updateTotalSelected();
                });

                // Individual checkbox
                $('.invoice-checkbox').off('change').on('change', function () {
                    updateTotalSelected();

                    // Update select all state
                    var total = $('.invoice-checkbox').length;
                    var checked = $('.invoice-checkbox:checked').length;
                    $('#select-all-invoices').prop('checked', total === checked);
                });
            }

            function updateTotalSelected() {
                var total = 0;
                $('.invoice-checkbox:checked').each(function () {
                    total += parseFloat($(this).data('sisa'));
                });

                $('#total-selected').text(formatRupiah(total));
                $('#total-piutang').text(formatRupiah(total));
                $('#total-display').text('Total: ' + formatRupiah(total));

                // Auto-fill jumlah bayar dengan total sisa
                if (total > 0) {
                    $('#jumlah_bayar').val(total);
                    $('#btn-submit').prop('disabled', false);
                } else {
                    $('#btn-submit').prop('disabled', true);
                }
            }

            // Event: Gudang change
            $('#gudang_id').on('change', function () {
                loadInvoices($(this).val());
            });

            // Initial load
            var initialGudang = $('#gudang_id').val() || $('input[name="gudang_id"]').val();
            if (initialGudang) {
                loadInvoices(initialGudang);
            }

            // Lampiran upload feedback (multiple files)
            const lampiranInput = document.getElementById('lampiran');
            const lampiranList = document.getElementById('lampiran-list');
            const previewNomor = lampiranInput ? lampiranInput.dataset.previewNomor : '';

            if (lampiranInput) {
                lampiranInput.addEventListener('change', function () {
                    lampiranList.innerHTML = '';

                    if (this.files && this.files.length > 0) {
                        // Update label
                        const label = this.nextElementSibling;
                        if (label) {
                            label.textContent = this.files.length + ' file dipilih';
                        }

                        // Show file list with preview names
                        let html = '<div class="alert alert-info py-2"><small><strong>File yang akan diupload:</strong></small><ul class="mb-0 pl-3 mt-1">';
                        for (let i = 0; i < this.files.length; i++) {
                            const file = this.files[i];
                            const extension = file.name.split('.').pop().toLowerCase();
                            const expectedFilename = previewNomor + '-' + (i + 1) + '.' + extension;
                            html += '<li><small>' + file.name + ' → <strong>' + expectedFilename + '</strong></small></li>';
                        }
                        html += '</ul></div>';
                        lampiranList.innerHTML = html;
                    } else {
                        const label = this.nextElementSibling;
                        if (label) {
                            label.textContent = 'Pilih file (bisa pilih banyak)...';
                        }
                    }
                });
            }

            // Form validation before submit
            $('#formPembayaran').on('submit', function (e) {
                var checked = $('.invoice-checkbox:checked').length;
                var jumlah = parseFloat($('#jumlah_bayar').val()) || 0;

                if (checked === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu invoice untuk dibayar.');
                    return false;
                }

                if (jumlah <= 0) {
                    e.preventDefault();
                    alert('Jumlah bayar harus lebih dari 0.');
                    return false;
                }

                return true;
            });
        });
    </script>
@endpush