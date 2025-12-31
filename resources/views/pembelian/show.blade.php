@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4 page-header-mobile">
            <h1 class="h3 mb-0 text-gray-800">Detail Pembelian #{{ $pembelian->custom_number ?? $pembelian->id }}</h1>
            <div class="show-action-buttons">
                @php $user = auth()->user(); @endphp

                {{-- Tombol Approve (Admin) --}}
                @if($pembelian->status == 'Pending' && ($user->role == 'super_admin' || ($user->role == 'admin' && ($pembelian->approver_id == $user->id || ($pembelian->gudang_id && method_exists($user, 'canAccessGudang') && $user->canAccessGudang($pembelian->gudang_id))))))
                    <form action="{{ route('pembelian.approve', $pembelian->id) }}" method="POST" class="d-inline"
                        title="Setujui data ini">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i>
                            Setujui</button>
                    </form>
                @endif

                {{-- Tombol Cancel (Hanya super_admin bisa cancel Approved/Lunas) --}}
                @if($pembelian->status != 'Canceled')
                    @if($user->role == 'super_admin' || $pembelian->status == 'Pending')
                        @if(in_array($user->role, ['admin', 'super_admin']))
                            <button type="button" class="btn btn-dark btn-sm shadow-sm" data-toggle="modal" data-target="#cancelModal">
                                <i class="fas fa-ban fa-sm"></i> Cancel
                            </button>
                        @endif
                    @endif
                @endif

                {{-- Tombol Print & Kembali --}}
                <button type="button" id="printBluetooth" class="btn btn-primary btn-sm shadow-sm" 
                    data-type="pembelian"
                    data-url="{{ route('bluetooth.pembelian', $pembelian->id) }}">
                    <i class="fab fa-bluetooth-b fa-sm text-white"></i> Print Bluetooth
                </button>
                <a href="{{ route('pembelian.print', $pembelian->id) }}" target="_blank"
                    class="btn btn-info btn-sm shadow-sm">
                    <i class="fas fa-print fa-sm"></i> Cetak Struk
                </a>
                <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#qrModal">
                    <i class="fas fa-qrcode fa-sm"></i> QR Code
                </button>
                <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-sm shadow-sm">
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
                                <td style="width: 30%;"><strong>Pembuat</strong></td>
                                <td>: {{ $pembelian->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Staf Penyetuju</strong></td>
                                <td>: {{ $pembelian->status == 'Pending' ? '-' : ($pembelian->approver->name ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email Penyetuju</strong></td>
                                <td>: {{ $pembelian->status == 'Pending' ? '-' : ($pembelian->approver->email ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Gudang</strong></td>
                                <td>: {{ $pembelian->gudang->nama_gudang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Urgensi</strong></td>
                                <td>: {{ $pembelian->urgensi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tahun Anggaran</strong></td>
                                <td>: {{ $pembelian->tahun_anggaran ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tanggal</strong></td>
                                <td>: {{ $pembelian->tgl_transaksi->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jatuh Tempo</strong></td>
                                <td>: {{ $pembelian->tgl_jatuh_tempo ? $pembelian->tgl_jatuh_tempo->format('d F Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td>: {{ $pembelian->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                            @if($pembelian->updated_at != $pembelian->created_at)
                            <tr>
                                <td><strong>Diupdate</strong></td>
                                <td>: {{ $pembelian->updated_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Syarat Bayar</strong></td>
                                <td>: {{ $pembelian->syarat_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>:
                                    @if($pembelian->status_display == 'Lunas') <span class="badge badge-success">Lunas</span>
                                    @elseif($pembelian->status == 'Approved') <span class="badge badge-info">Approved</span>
                                    @elseif($pembelian->status == 'Pending') <span
                                        class="badge badge-warning">Pending</span>
                                    @elseif($pembelian->status == 'Canceled') <span
                                        class="badge badge-secondary">Canceled</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Koordinat</strong></td>
                                <td>: 
                                    @if($pembelian->koordinat)
                                        {{ $pembelian->koordinat }}
                                        <a href="https://www.google.com/maps?q={{ str_replace(' ', '', $pembelian->koordinat) }}" 
                                           target="_blank" class="ml-2 btn btn-outline-success btn-sm" title="Buka di Google Maps">
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
                                <th>Produk</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Disc%</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotal = 0; @endphp
                            @foreach($pembelian->items as $item)
                                @php 
                                                            $totalRow = ($item->kuantitas * $item->harga_satuan) * (1 - ($item->diskon / 100));
                                    $subtotal += $totalRow;
                                @endphp
                                <tr>
                                    <td>{{ $item->produk->nama_produk }} ({{ $item->produk->item_code }})</td>
                                    <td>{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="text-center">{{ $item->kuantitas }} {{ $item->unit }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->diskon }}%</td>
                                    <td class="text-right">Rp {{ number_format($totalRow, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
            </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS --}}
                <div class="mobile-product-cards">
                    @php $subtotalMobile = 0; @endphp
                    @foreach($pembelian->items as $item)
                        @php 
                            $totalRowMobile = ($item->kuantitas * $item->harga_satuan) * (1 - ($item->diskon / 100));
                            $subtotalMobile += $totalRowMobile;
                        @endphp
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
                                <span class="total-value">Rp {{ number_format($totalRowMobile, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- KALKULASI TOTAL --}}
                <div class="row justify-content-end mt-3">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless text-right">
                            <tr>
                                <td>Subtotal</td>
                                <td class="font-weight-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($pembelian->diskon_akhir > 0)
                                <tr>
                                    <td>Diskon Akhir</td>
                                    <td class="text-danger">- Rp {{ number_format($pembelian->diskon_akhir, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        <tr>
                                @php
                                    $kenaPajak = max(0, $subtotal - $pembelian->diskon_akhir);
                                    $pajakNominal = $kenaPajak * ($pembelian->tax_percentage / 100);
                                @endphp
                                <td>Pajak ({{ $pembelian->tax_percentage }}%)</td>
                                <td>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                            </tr>

                                                           <tr class="border-top">
                                <td class="h4 font-weight-bold">Grand Total</td>
                                <td class="h4 font-weight-bold text-primary">Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">


                                <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Memo</h6></div>
                    <div class="card-body">{{ $pembelian->memo ?? 'Tidak ada memo.' }}</div>
               </div>
            </div>


            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lampiran</h6>
                    </div>
                    <div class="card-body">
                        @if($pembelian->lampiran_path)
                            @php
                                $path = $pembelian->lampiran_path;
                                $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            @if($isImage)
                                <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $path) }}" alt="Lampiran" class="img-fluid rounded" style="max-height: 250px;">
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
                    <form action="{{ route('pembelian.cancel', $pembelian->id) }}" method="POST">
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
                    <h5 class="modal-title"><i class="fas fa-qrcode mr-2"></i>QR Code Dokumen</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3">Scan QR Code di bawah untuk melihat dokumen:</p>
                    @php
                        $publicUrl = route('public.invoice.pembelian', $pembelian->uuid);
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($publicUrl);
                    @endphp
                    <img src="{{ $qrUrl }}" alt="QR Code Dokumen" class="img-fluid mb-3" style="max-width: 300px;">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> QR Code ini bisa di-scan untuk melihat dokumen tanpa login</small>
                    </div>
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" id="publicUrlInput" value="{{ $publicUrl }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPublicUrl()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bluetooth Print JS -->
    <script src="{{ asset('js/bluetooth-print.js') }}"></script>
    <script>
    function copyPublicUrl() {
        const input = document.getElementById('publicUrlInput');
        input.select();
        document.execCommand('copy');
        
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    }

    // Bluetooth Print Function (using new client-side solution)
    document.getElementById('printBluetooth')?.addEventListener('click', function() {
        const type = this.dataset.type;
        const jsonUrl = this.dataset.url;
        // Disable QR & Logo - printer BLE sering glitch dengan image
        printViaBluetooth(this, type, jsonUrl, { printLogo: false, printQR: false });
    });
    </script>
@endsection