@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex align-items-center justify-content-between mb-4 page-header-mobile">
            <h1 class="h3 mb-0 text-gray-800">Detail Biaya #{{ $biaya->custom_number ?? $biaya->id }}</h1>
            <div class="show-action-buttons">
                @php $user = auth()->user(); @endphp

                {{-- Tombol Approve (Super Admin atau Admin yang ditunjuk) --}}
                @if($biaya->status == 'Pending')
                    @if($user->role == 'super_admin' || ($user->role == 'admin' && ($biaya->approver_id == $user->id || ($biaya->gudang_id && method_exists($user, 'canAccessGudang') && $user->canAccessGudang($biaya->gudang_id)))))
                        <form action="{{ route('biaya.approve', $biaya->id) }}" method="POST" class="d-inline"
                            title="Setujui data ini">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i>
                                Setujui</button>
                        </form>
                    @endif
                @endif

                {{-- Tombol Cancel (Hanya super_admin bisa cancel Approved) --}}
                @if($biaya->status != 'Canceled')
                    @if($user->role == 'super_admin' || $biaya->status == 'Pending')
                        @if(in_array($user->role, ['admin', 'super_admin']))
                            <button type="button" class="btn btn-dark btn-sm shadow-sm" data-toggle="modal" data-target="#cancelModal">
                                <i class="fas fa-ban fa-sm"></i> Cancel
                            </button>
                        @endif
                    @endif
                @endif

                <button type="button" id="printBluetooth" class="btn btn-primary btn-sm shadow-sm" data-type="biaya"
                    data-url="{{ route('bluetooth.biaya', $biaya->id) }}">
                    <i class="fab fa-bluetooth-b fa-sm text-white"></i> Print Bluetooth
                </button>
                <a href="{{ route('biaya.print', $biaya->id) }}" target="_blank" class="btn btn-info btn-sm shadow-sm">
                    <i class="fas fa-print fa-sm"></i> Cetak Struk
                </a>
                <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#qrModal">
                    <i class="fas fa-qrcode fa-sm"></i> QR Code
                </button>
                <a href="{{ route('biaya.index') }}" class="btn btn-secondary btn-sm shadow-sm">
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
                <div class="row mb-4">
                    {{-- KOLOM KIRI (INFO UTAMA) --}}
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Jenis Biaya</strong></td>
                                <td>:
                                    @if($biaya->jenis_biaya == 'masuk')
                                        <span class="badge badge-success">Biaya Masuk</span>
                                    @else
                                        <span class="badge badge-danger">Biaya Keluar</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%;"><strong>Pembuat</strong></td>
                                <td>: {{ $biaya->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Approver</strong></td>
                                <td>: {{ $biaya->status == 'Pending' ? '-' : ($biaya->approver->name ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Penerima</strong></td>
                                <td>: {{ $biaya->penerima }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tgl. Transaksi</strong></td>
                                <td>: {{ $biaya->tgl_transaksi->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td>: {{ $biaya->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                            @if($biaya->updated_at != $biaya->created_at)
                                <tr>
                                    <td><strong>Diupdate</strong></td>
                                    <td>: {{ $biaya->updated_at->format('d M Y, H:i') }} WIB</td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Bayar Dari</strong></td>
                                <td>: {{ $biaya->bayar_dari }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cara Pembayaran</strong></td>
                                <td>: {{ $biaya->cara_pembayaran }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- KOLOM KANAN (INFO STATUS & TOTAL) --}}
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Status</strong></td>
                                <td>:
                                    @if($biaya->status == 'Approved')
                                        <span class="badge badge-success">{{ $biaya->status }}</span>
                                    @elseif($biaya->status == 'Pending')
                                        <span class="badge badge-warning">{{ $biaya->status }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ $biaya->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @php
                                $subtotal = $biaya->items->sum('jumlah');
                                $taxAmount = $subtotal * ($biaya->tax_percentage / 100);
                            @endphp
                            <tr>
                                <td><strong>Subtotal</strong></td>
                                <td>: Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pajak ({{ $biaya->tax_percentage }}%)</strong></td>
                                <td>: Rp {{ number_format($taxAmount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Grand Total</strong></td>
                                <td>: <span class="font-weight-bold text-primary" style="font-size: 1.25rem;">Rp
                                        {{ number_format($biaya->grand_total, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Tag</strong></td>
                                <td>: {{ $biaya->tag ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Koordinat</strong></td>
                                <td>:
                                    @if($biaya->koordinat)
                                        {{ $biaya->koordinat }}
                                        <a href="https://www.google.com/maps?q={{ str_replace(' ', '', $biaya->koordinat) }}"
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
                <h6 class="m-0 font-weight-bold text-primary">Rincian Biaya</h6>
            </div>
            <div class="card-body">
                {{-- DESKTOP TABLE --}}
                <div class="table-responsive desktop-product-table">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Akun Biaya (Kategori)</th>
                                <th>Deskripsi</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($biaya->items as $item)
                                <tr>
                                    <td>{{ $item->kategori }}</td>
                                    <td>{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS --}}
                <div class="mobile-product-cards">
                    @foreach($biaya->items as $item)
                        <div class="show-product-card">
                            <div class="item-name">{{ $item->kategori }}</div>
                            @if($item->deskripsi)
                                <div class="item-desc">{{ $item->deskripsi }}</div>
                            @endif
                            <div class="item-total">
                                <span class="total-label">Jumlah</span>
                                <span class="total-value">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
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
                    <div class="card-body">
                        {{ $biaya->memo ?? 'Tidak ada memo.' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lampiran</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $allLampiran = [];
                            // Backward compatibility: gabungkan lampiran_path lama ke array
                            if ($biaya->lampiran_path) {
                                $allLampiran[] = $biaya->lampiran_path;
                            }
                            // Tambahkan lampiran_paths
                            if (is_array($biaya->lampiran_paths)) {
                                $allLampiran = array_merge($allLampiran, $biaya->lampiran_paths);
                            }
                        @endphp
                        @if(count($allLampiran) > 0)
                            <div class="row">
                                @foreach($allLampiran as $index => $path)
                                    @php
                                        $isImage = in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    @endphp
                                    <div class="col-6 col-md-4 mb-3 text-center lampiran-item" data-path="{{ $path }}">
                                        @if($isImage)
                                            <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $path) }}" alt="Lampiran" class="img-fluid rounded"
                                                    style="max-height: 120px; object-fit: cover;">
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $path) }}" target="_blank"
                                                class="d-block p-3 bg-light rounded">
                                                <i class="fas fa-file-alt fa-3x text-primary"></i>
                                            </a>
                                        @endif
                                        <small class="d-block text-truncate mt-1"
                                            title="{{ basename($path) }}">{{ basename($path) }}</small>
                                        @if(Auth::user()->role === 'super_admin')
                                            <form action="{{ route('biaya.deleteLampiran', $biaya->id) }}" method="POST"
                                                class="mt-1 d-inline delete-lampiran-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="lampiran_path" value="{{ $path }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Hapus lampiran ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
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
                    <form action="{{ route('biaya.cancel', $biaya->id) }}" method="POST">
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
                        $publicUrl = route('public.invoice.biaya', $biaya->uuid);
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($publicUrl);
                    @endphp
                    <img src="{{ $qrUrl }}" alt="QR Code Dokumen" class="img-fluid mb-3" style="max-width: 300px;">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> QR Code ini bisa di-scan untuk melihat dokumen tanpa
                            login</small>
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
        document.getElementById('printBluetooth')?.addEventListener('click', function () {
            const type = this.dataset.type;
            const jsonUrl = this.dataset.url;
            // Disable QR & Logo untuk biaya - printer BLE sering glitch dengan image
            printViaBluetooth(this, type, jsonUrl, { printLogo: false, printQR: false });
        });
    </script>
@endsection