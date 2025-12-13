@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Detail Penjualan #{{ $penjualan->custom_number ?? $penjualan->id }}</h1>
            <div>
                @php $user = auth()->user(); @endphp

                {{-- Tombol Approve (Admin) --}}
                @if($penjualan->status == 'Pending')
                    @if($user->role == 'super_admin' || ($user->role == 'admin' && $penjualan->approver_id == $user->id))
                        <form action="{{ route('penjualan.approve', $penjualan->id) }}" method="POST" class="d-inline"
                            title="Setujui data ini">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i>
                                Setujui</button>
                        </form>
                    @endif
                @endif

                {{-- Tombol Mark Paid (Admin) --}}
                @if($penjualan->status == 'Approved' && in_array($user->role, ['admin', 'super_admin']))
                    <form action="{{ route('penjualan.markAsPaid', $penjualan->id) }}" method="POST" class="d-inline"
                        title="Tandai Lunas">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-dollar-sign fa-sm"></i>
                            Lunas</button>
                    </form>
                @endif

                {{-- Tombol Cancel (Admin) --}}
                @if($penjualan->status != 'Canceled' && in_array($user->role, ['admin', 'super_admin']))
                    <button type="button" class="btn btn-dark btn-sm shadow-sm" data-toggle="modal" data-target="#cancelModal">
                        <i class="fas fa-ban fa-sm"></i> Cancel
                    </button>
                @endif

                {{-- Tombol Print & Kembali --}}
                <a href="{{ route('penjualan.printRich', $penjualan->id) }}" target="_blank"
                    class="btn btn-info btn-sm shadow-sm">
                    <i class="fas fa-print fa-sm"></i> Cetak Struk
                </a>
                <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#qrModal">
                    <i class="fas fa-qrcode fa-sm"></i> QR Code
                </button>
                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div> @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Info Utama</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Sales</strong></td>
                                <td>: {{ $penjualan->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pelanggan</strong></td>
                                <td>: {{ $penjualan->pelanggan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $penjualan->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tgl. Transaksi</strong></td>
                                <td>: {{ $penjualan->tgl_transaksi->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jatuh Tempo</strong></td>
                                <td>: {{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d F Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td>: {{ $penjualan->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                            @if($penjualan->updated_at != $penjualan->created_at)
                                <tr>
                                    <td><strong>Diupdate</strong></td>
                                    <td>: {{ $penjualan->updated_at->format('d M Y, H:i') }} WIB</td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Gudang</strong></td>
                                <td>: {{ $penjualan->gudang->nama_gudang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Approver</strong></td>
                                <td>: {{ $penjualan->status == 'Pending' ? '-' : ($penjualan->approver->name ?? '-') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        {{-- Siapkan variabel kalkulasi --}}
                        @php
                            $subtotal = $penjualan->items->sum('jumlah_baris');
                            $taxAmount = 0;
                            // Hitung ulang pajak agar akurat di view
                            $kenaPajak = max(0, $subtotal - $penjualan->diskon_akhir);
                            $taxAmount = $kenaPajak * ($penjualan->tax_percentage / 100);
                        @endphp
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Status</strong></td>
                                <td>:
                                    @if($penjualan->status_display == 'Lunas') <span
                                        class="badge badge-success">Lunas</span>
                                    @elseif($penjualan->status == 'Approved') <span class="badge badge-info">Belum
                                        Bayar</span>
                                    @elseif($penjualan->status == 'Pending') <span
                                        class="badge badge-warning">Pending</span>
                                    @elseif($penjualan->status == 'Canceled') <span
                                        class="badge badge-secondary">Canceled</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Subtotal</strong></td>
                                <td>: Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($penjualan->diskon_akhir > 0)
                                <tr>
                                    <td><strong>Diskon Akhir</strong></td>
                                    <td class="text-danger">: - Rp {{ number_format($penjualan->diskon_akhir, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Pajak ({{ $penjualan->tax_percentage }}%)</strong></td>
                                <td>: Rp {{ number_format($taxAmount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Grand Total</strong></td>
                                <td>: <span class="font-weight-bold text-primary" style="font-size: 1.25rem;">Rp
                                        {{ number_format($penjualan->grand_total, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>No. Referensi</strong></td>
                                <td>: {{ $penjualan->no_referensi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tag</strong></td>
                                <td>: {{ $penjualan->tag ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Koordinat</strong></td>
                                <td>:
                                    @if($penjualan->koordinat)
                                        {{ $penjualan->koordinat }}
                                        <a href="https://www.google.com/maps?q={{ str_replace(' ', '', $penjualan->koordinat) }}"
                                            target="_blank" class="ml-2 btn btn-outline-success btn-sm"
                                            title="Buka di Google Maps">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Rincian Produk</h6>
            </div>
            <div class="card-body">
                {{-- DESKTOP TABLE --}}
                <div class="table-responsive desktop-product-table">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Item Code</th>
                                <th>Produk</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Disc%</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penjualan->items as $item)
                                <tr>
                                    <td>{{ $item->produk->item_code ?? '-' }}</td>
                                    <td>{{ $item->produk->nama_produk }}</td>
                                    <td>{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="text-center">{{ $item->kuantitas }} {{ $item->unit }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->diskon }}%</td>
                                    <td class="text-right">Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS --}}
                <div class="mobile-product-cards">
                    @foreach($penjualan->items as $item)
                        <div class="show-product-card">
                            <div class="item-name">{{ $item->produk->nama_produk }}</div>
                            <div class="item-code">{{ $item->produk->item_code ?? '-' }}</div>
                            @if($item->deskripsi)
                                <div class="item-desc">{{ $item->deskripsi }}</div>
                            @endif
                            <div class="item-details">
                                <div class="detail-item">
                                    <div class="label">Qty</div>
                                    <div class="value">{{ $item->kuantitas }} {{ $item->unit }}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="label">Harga</div>
                                    <div class="value">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div>
                                </div>
                                <div class="detail-item">
                                    <div class="label">Disc</div>
                                    <div class="value">{{ $item->diskon }}%</div>
                                </div>
                            </div>
                            <div class="item-total">
                                <span class="total-label">Total</span>
                                <span class="total-value">Rp {{ number_format($item->jumlah_baris, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Memo</h6>
                    </div>
                    <div class="card-body">{{ $penjualan->memo ?? 'Tidak ada memo.' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lampiran</h6>
                    </div>
                    <div class="card-body">
                        @if($penjualan->lampiran_path)
                            @php
                                $path = $penjualan->lampiran_path;
                                $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if($isImage)
                                <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $path) }}" alt="Lampiran" class="img-fluid rounded"
                                        style="max-height: 250px;">
                                </a>
                            @else
                                <div class="alert alert-info d-flex align-items-center mb-0">
                                    <i class="fas fa-file-alt fa-2x mr-3"></i>
                                    <div>
                                        <strong>File terlampir:</strong><br>
                                        <a href="{{ asset('storage/' . $path) }}" target="_blank">{{ basename($path) }}</a>
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">Tidak ada lampiran.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Pembatalan</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>membatalkan</strong> transaksi ini?</p>
                    <p class="text-muted mb-0"><small>Transaksi yang dibatalkan tidak dapat diproses kembali.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Tidak</button>
                    <form action="{{ route('penjualan.cancel', $penjualan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">Ya, Batalkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-qrcode mr-2"></i>QR Code Print Struk</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3">Scan QR Code di bawah dengan aplikasi <strong>iWare</strong> untuk print:</p>
                    @php
                        $printUrl = route('penjualan.printRich', $penjualan->id);
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($printUrl);
                    @endphp
                    <img src="{{ $qrUrl }}" alt="QR Code Print" class="img-fluid mb-3" style="max-width: 300px;">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Buka iWare > Rich Text > Scan QR Code ini</small>
                    </div>
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" id="printUrlInput" value="{{ $printUrl }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPrintUrl()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyPrintUrl() {
        const input = document.getElementById('printUrlInput');
        input.select();
        document.execCommand('copy');
        
        // Show feedback
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }
    </script>
@endsection