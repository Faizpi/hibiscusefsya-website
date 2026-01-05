@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4 page-header-mobile">
            <h1 class="h3 mb-0 text-gray-800">Detail Produk</h1>
            <div class="show-action-buttons">
                <a href="{{ route('produk.edit', $produk->id) }}" class="btn btn-warning btn-sm shadow-sm">
                    <i class="fas fa-edit fa-sm"></i> Edit
                </a>
                <a href="{{ route('produk.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            {{-- Kolom Kiri: Info Produk --}}
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-box"></i> Informasi Produk
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Kode Produk</strong></td>
                                        <td width="5%">:</td>
                                        <td><span class="badge badge-dark font-weight-bold"
                                                style="font-size: 1rem;">{{ $produk->item_kode ?? $produk->item_code ?? '-' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama Produk</strong></td>
                                        <td>:</td>
                                        <td>{{ $produk->item_nama ?? $produk->nama_produk }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Harga</strong></td>
                                        <td>:</td>
                                        <td><span class="text-success font-weight-bold">Rp
                                                {{ number_format($produk->harga ?? 0, 0, ',', '.') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Satuan</strong></td>
                                        <td>:</td>
                                        <td><span class="badge badge-info">{{ $produk->satuan ?? 'Pcs' }}</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Deskripsi</strong></td>
                                        <td width="5%">:</td>
                                        <td>{{ $produk->deskripsi ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dibuat</strong></td>
                                        <td>:</td>
                                        <td>{{ $produk->created_at ? $produk->created_at->format('d M Y, H:i') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Diupdate</strong></td>
                                        <td>:</td>
                                        <td>{{ $produk->updated_at ? $produk->updated_at->format('d M Y, H:i') : '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stok per Gudang --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-warehouse"></i> Stok per Gudang
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($produk->gudangProduks && $produk->gudangProduks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Gudang</th>
                                            <th class="text-center">Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalStok = 0; @endphp
                                        @foreach($produk->gudangProduks as $gp)
                                            @php $totalStok += $gp->stok; @endphp
                                            <tr>
                                                <td>{{ optional($gp->gudang)->nama_gudang ?? 'Gudang #' . $gp->gudang_id }}</td>
                                                <td class="text-center">
                                                    @if($gp->stok > 10)
                                                        <span class="badge badge-success">{{ $gp->stok }}</span>
                                                    @elseif($gp->stok > 0)
                                                        <span class="badge badge-warning">{{ $gp->stok }}</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ $gp->stok }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-secondary">
                                            <td><strong>Total Stok</strong></td>
                                            <td class="text-center"><strong>{{ $totalStok }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">Produk ini belum memiliki stok di gudang manapun.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Barcode --}}
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-barcode"></i> Barcode
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            use Milon\Barcode\DNS1D;
                            $itemKode = $produk->item_kode ?? $produk->item_code ?? 'PRD' . $produk->id;
                            // Normalisasi hanya digit untuk EAN-13
                            $eanData = preg_replace('/\D/', '', $itemKode);
                            // Library EAN13 menerima 12 atau 13 digit (12 = auto checksum)
                            $barcodeSvg = null;
                            if (strlen($eanData) === 12 || strlen($eanData) === 13) {
                                // Scale minimal 2, tinggi 80 untuk keterbacaan; showCode=false (kita tampilkan teks terpisah)
                                $dns1d = new DNS1D();
                                $barcodeSvg = $dns1d->getBarcodeSVG($eanData, 'EAN13', 2, 80, 'black', false);
                            }
                        @endphp
                        @if($barcodeSvg)
                            <div class="d-inline-block bg-white" style="padding:10px;">
                                {!! $barcodeSvg !!}
                            </div>
                        @else
                            <div class="alert alert-warning p-2">
                                <small>Barcode EAN-13 hanya untuk kode numerik 12/13 digit. Kode saat ini:
                                    {{ $itemKode }}</small>
                            </div>
                        @endif
                        <p class="font-weight-bold mb-1">{{ $itemKode }}</p>
                        <small class="text-muted">{{ $produk->item_nama ?? $produk->nama_produk }}</small>

                        <hr>

                        <div class="btn-group">
                            <a href="{{ route('produk.download', $produk->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                            <a href="{{ route('produk.print', $produk->id) }}" target="_blank"
                                class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </div>
                    </div>
                </div>

                {{-- QR Code --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-qrcode"></i> QR Code
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $qrData = "PRODUK\nKode: {$itemKode}\nNama: " . ($produk->item_nama ?? $produk->nama_produk) . "\nHarga: Rp " . number_format($produk->harga ?? 0, 0, ',', '.');
                            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
                        @endphp
                        <img src="{{ $qrUrl }}" alt="QR Code" class="img-fluid mb-2" style="max-width: 150px;">
                        <p class="text-muted small mb-0">Scan untuk info produk</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection