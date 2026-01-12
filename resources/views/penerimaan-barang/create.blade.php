@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Buat Penerimaan Barang</h1>
            <h3 class="font-weight-bold text-right text-primary" id="total-display">Total: 0 Item</h3>
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

        <form action="{{ route('penerimaan-barang.store') }}" method="POST" enctype="multipart/form-data"
            id="formPenerimaan">
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
                                    id="preview-nomor" value="{{ $previewNomor }}" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: Tanggal & Surat Jalan --}}
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
                                <label>Total Item Diterima</label>
                                <input type="text" class="form-control bg-light font-weight-bold text-success"
                                    id="total-items-display" value="0 item" readonly>
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
                                    Format: jpg, jpeg, png, pdf, zip, doc, docx (max 10MB per file)
                                </small>
                                <div id="lampiran-list" class="mt-2"></div>
                                @error('lampiran') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @error('lampiran.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Invoice Selection (Multiple) --}}
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-file-invoice"></i> Pilih Invoice Pembelian yang Barangnya Diterima
                    </h6>

                    <div id="invoice-container">
                        <div id="invoice-loading" class="text-center py-4" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2 mb-0">Memuat data invoice...</p>
                        </div>

                        <div id="invoice-empty" class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Pilih gudang untuk melihat daftar invoice pembelian.
                        </div>

                        <div id="invoice-table-wrapper" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="invoice-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%" class="text-center">
                                                <input type="checkbox" id="select-all-invoices">
                                            </th>
                                            <th width="25%">No. Invoice Pembelian</th>
                                            <th width="25%">Supplier</th>
                                            <th width="15%">Tgl Transaksi</th>
                                            <th width="15%">Status</th>
                                            <th width="15%" class="text-right">Total Item</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoice-body">
                                        <!-- Data akan dimuat via AJAX -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="5" class="text-right">Total Invoice Terpilih:</td>
                                            <td class="text-right text-primary" id="total-selected">0 invoice</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Items Detail Table --}}
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-boxes"></i> Detail Barang yang Akan Diterima
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
                                        <th width="8%">Kode</th>
                                        <th width="20%">Nama Produk</th>
                                        <th width="12%">Invoice</th>
                                        <th width="6%">Satuan</th>
                                        <th width="8%" class="text-center">Qty Pesan</th>
                                        <th width="8%" class="text-center">Sudah Diterima</th>
                                        <th width="8%" class="text-center">Sisa</th>
                                        <th width="15%">Qty Diterima *</th>
                                        <th width="15%">Qty Reject</th>
                                    </tr>
                                </thead>
                                <tbody id="items-body">
                                    <!-- Items akan dimuat via JS -->
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="7" class="text-right">Total Qty Diterima / Reject:</td>
                                        <td class="text-success" id="total-qty-diterima">0</td>
                                        <td class="text-danger" id="total-qty-reject">0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-muted"><i class="fas fa-info-circle"></i> <strong>Qty Reject</strong> adalah barang yang tidak lolos quality control dan tidak akan masuk ke stok gudang.</small>
                    </div>

                    <div id="no-invoice-selected" class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Pilih minimal satu invoice pembelian untuk melihat daftar barang.
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
        $(document).ready(function () {
            let allPembelianData = [];
            let selectedPembelianIds = [];

            function loadPembelianList(gudangId) {
                if (!gudangId) {
                    $('#invoice-empty').show();
                    $('#invoice-table-wrapper').hide();
                    $('#items-container').hide();
                    $('#no-invoice-selected').show();
                    $('#btn-submit').prop('disabled', true);
                    return;
                }

                $('#invoice-loading').show();
                $('#invoice-empty').hide();
                $('#invoice-table-wrapper').hide();

                $.ajax({
                    url: '/penerimaan-barang/get-pembelian-by-gudang/' + gudangId,
                    type: 'GET',
                    success: function (data) {
                        $('#invoice-loading').hide();
                        allPembelianData = data;

                        if (data.length === 0) {
                            $('#invoice-empty').html('<i class="fas fa-check-circle text-success"></i> Tidak ada invoice pembelian yang perlu diterima barangnya di gudang ini.').show();
                            $('#btn-submit').prop('disabled', true);
                            return;
                        }

                        var html = '';
                        data.forEach(function (inv) {
                            html += '<tr>';
                            html += '<td class="text-center">';
                            html += '<input type="checkbox" class="invoice-checkbox" name="pembelian_ids[]" ';
                            html += 'value="' + inv.id + '">';
                            html += '</td>';
                            html += '<td><strong>' + inv.nomor + '</strong></td>';
                            html += '<td>' + inv.nama_supplier + '</td>';
                            html += '<td>' + inv.tgl_transaksi + '</td>';
                            html += '<td><span class="badge badge-' + (inv.status === 'Approved' ? 'success' : 'warning') + '">' + inv.status + '</span></td>';
                            html += '<td class="text-right">' + inv.total_items + ' item</td>';
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
                    updateSelectedInvoices();
                });

                // Individual checkbox
                $('.invoice-checkbox').off('change').on('change', function () {
                    updateSelectedInvoices();

                    // Update select all state
                    var total = $('.invoice-checkbox').length;
                    var checked = $('.invoice-checkbox:checked').length;
                    $('#select-all-invoices').prop('checked', total === checked);
                });
            }

            function updateSelectedInvoices() {
                selectedPembelianIds = [];
                $('.invoice-checkbox:checked').each(function () {
                    selectedPembelianIds.push($(this).val());
                });

                var count = selectedPembelianIds.length;
                $('#total-selected').text(count + ' invoice');

                if (count > 0) {
                    loadItemsForSelectedInvoices();
                } else {
                    $('#items-container').hide();
                    $('#no-invoice-selected').show();
                    $('#btn-submit').prop('disabled', true);
                    updateTotalDisplay(0);
                }
            }

            function loadItemsForSelectedInvoices() {
                if (selectedPembelianIds.length === 0) {
                    $('#items-container').hide();
                    $('#no-invoice-selected').show();
                    return;
                }

                $('#items-loading').show();
                $('#items-container').hide();
                $('#no-invoice-selected').hide();

                // Load items for all selected pembelians
                var promises = selectedPembelianIds.map(function (id) {
                    return $.ajax({
                        url: '/penerimaan-barang/get-pembelian/' + id,
                        type: 'GET'
                    });
                });

                $.when.apply($, promises).then(function () {
                    $('#items-loading').hide();

                    // Handle single vs multiple responses
                    // Single promise: arguments = [data, textStatus, jqXHR]
                    // Multiple promises: arguments = [[data, textStatus, jqXHR], [data, textStatus, jqXHR], ...]
                    var responses;
                    if (selectedPembelianIds.length === 1) {
                        // Single request - arguments[0] is the data directly
                        responses = [arguments[0]];
                    } else {
                        // Multiple requests - each argument is [data, textStatus, jqXHR]
                        responses = Array.from(arguments).map(function(arg) {
                            return arg[0]; // Get data from each response
                        });
                    }
                    
                    console.log('Responses:', responses); // Debug

                    var allItems = [];
                    var itemIndex = 0;

                    responses.forEach(function (data) {
                        console.log('Processing data:', data); // Debug
                        if (data && data.items) {
                            data.items.forEach(function (item) {
                                console.log('Item:', item, 'qty_sisa:', item.qty_sisa); // Debug
                                if (item.qty_sisa > 0) { // Hanya tampilkan item yang masih ada sisa
                                    allItems.push({
                                        ...item,
                                        pembelian_id: data.id,
                                        pembelian_nomor: data.nomor,
                                        index: itemIndex++
                                    });
                                }
                            });
                        }
                    });

                    console.log('All items:', allItems); // Debug

                    if (allItems.length === 0) {
                        $('#no-invoice-selected').html('<i class="fas fa-exclamation-triangle text-warning"></i> Semua barang dari invoice terpilih sudah diterima.').show();
                        $('#btn-submit').prop('disabled', true);
                        return;
                    }

                    var html = '';
                    allItems.forEach(function (item) {
                        html += '<tr>';
                        html += '<td>' + item.produk_kode + '</td>';
                        html += '<td>' + item.produk_nama + '</td>';
                        html += '<td><small class="text-muted">' + item.pembelian_nomor + '</small></td>';
                        html += '<td>' + item.satuan + '</td>';
                        html += '<td class="text-center">' + item.qty_pesan + '</td>';
                        html += '<td class="text-center">' + item.qty_diterima + '</td>';
                        html += '<td class="text-center text-primary font-weight-bold">' + item.qty_sisa + '</td>';
                        html += '<td>';
                        html += '<input type="hidden" name="items[' + item.index + '][pembelian_id]" value="' + item.pembelian_id + '">';
                        html += '<input type="hidden" name="items[' + item.index + '][produk_id]" value="' + item.produk_id + '">';
                        html += '<input type="number" class="form-control form-control-sm qty-input qty-diterima" ';
                        html += 'name="items[' + item.index + '][qty_diterima]" value="' + item.qty_sisa + '" ';
                        html += 'min="0" max="' + item.qty_sisa + '" data-max="' + item.qty_sisa + '">';
                        html += '</td>';
                        html += '<td>';
                        html += '<input type="number" class="form-control form-control-sm qty-input qty-reject" ';
                        html += 'name="items[' + item.index + '][qty_reject]" value="0" ';
                        html += 'min="0" max="' + item.qty_sisa + '" data-max="' + item.qty_sisa + '">';
                        html += '</td>';
                        html += '</tr>';
                    });

                    $('#items-body').html(html);
                    $('#items-container').show();
                    $('#btn-submit').prop('disabled', false);

                    // Bind qty input events
                    $('.qty-input').on('input', function () {
                        var row = $(this).closest('tr');
                        var maxQty = parseInt(row.find('.qty-diterima').data('max')) || 0;
                        var qtyDiterima = parseInt(row.find('.qty-diterima').val()) || 0;
                        var qtyReject = parseInt(row.find('.qty-reject').val()) || 0;
                        
                        // Validasi total tidak melebihi max
                        if (qtyDiterima + qtyReject > maxQty) {
                            if ($(this).hasClass('qty-diterima')) {
                                row.find('.qty-diterima').val(maxQty - qtyReject);
                            } else {
                                row.find('.qty-reject').val(maxQty - qtyDiterima);
                            }
                        }
                        updateTotalQty();
                    });

                    updateTotalQty();
                }).fail(function () {
                    $('#items-loading').hide();
                    $('#no-invoice-selected').html('<i class="fas fa-exclamation-triangle text-danger"></i> Gagal memuat data barang.').show();
                });
            }

            function updateTotalQty() {
                var totalDiterima = 0;
                var totalReject = 0;
                $('.qty-diterima').each(function () {
                    totalDiterima += parseInt($(this).val()) || 0;
                });
                $('.qty-reject').each(function () {
                    totalReject += parseInt($(this).val()) || 0;
                });
                $('#total-qty-diterima').text(totalDiterima);
                $('#total-qty-reject').text(totalReject);
                $('#total-items-display').val(totalDiterima + ' diterima, ' + totalReject + ' reject');
                updateTotalDisplay(totalDiterima, totalReject);
            }

            function updateTotalDisplay(totalDiterima, totalReject) {
                var text = 'Total: ' + totalDiterima + ' Diterima';
                if (totalReject > 0) {
                    text += ', ' + totalReject + ' Reject';
                }
                $('#total-display').text(text);
            }

            // Event: Gudang change
            $('#gudang_id').on('change', function () {
                loadPembelianList($(this).val());
                // Reset selections
                selectedPembelianIds = [];
                $('#items-container').hide();
                $('#no-invoice-selected').show();
            });

            // Initial load
            var initialGudang = $('#gudang_id').val() || $('input[name="gudang_id"]').val();
            if (initialGudang) {
                loadPembelianList(initialGudang);
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
            $('#formPenerimaan').on('submit', function (e) {
                var checked = $('.invoice-checkbox:checked').length;
                var totalQty = 0;
                $('.qty-input').each(function () {
                    totalQty += parseInt($(this).val()) || 0;
                });

                if (checked === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu invoice pembelian.');
                    return false;
                }

                if (totalQty <= 0) {
                    e.preventDefault();
                    alert('Total qty diterima harus lebih dari 0.');
                    return false;
                }

                return true;
            });
        });
    </script>
@endpush