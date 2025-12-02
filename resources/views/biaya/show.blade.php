@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Biaya #{{ $biaya->id }}</h1>
        <div>
            @if(auth()->user()->role == 'admin' && $biaya->status == 'Pending')
                <form action="{{ route('biaya.approve', $biaya->id) }}" method="POST" class="d-inline" title="Setujui data ini">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i> Setujui</button>
                </form>
            @endif
            <a href="{{ route('biaya.print', $biaya->id) }}" target="_blank" class="btn btn-info btn-sm shadow-sm">
                <i class="fas fa-print fa-sm"></i> Cetak Struk
            </a>
            <a href="{{ route('biaya.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm"></i> Kembali
            </a>
        </div>
    </div>

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
                            <td style="width: 30%;"><strong>Pembuat</strong></td>
                            <td>: {{ $biaya->user->name }}</td>
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
                            <td>: <h4 class="font-weight-bold text-primary">Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</h4></td>
                        </tr>
                         <tr>
                            <td><strong>Tag</strong></td>
                            <td>: {{ $biaya->tag ?? '-' }}</td>
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
            <div class="table-responsive">
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
                    @if($biaya->lampiran_path)
                        @php
                            $path = $biaya->lampiran_path;
                            $isImage = in_array(pathinfo($path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp
                        @if($isImage)
                            <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $path) }}" alt="Lampiran" class="img-fluid rounded" style="max-height: 250px;">
                            </a>
                        @else
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="fas fa-file-alt fa-2x mr-3"></i>
                                <div>
                                    <strong>File terlampir:</strong>
                                    <br>
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                        {{ basename($path) }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">Tidak ada lampiran.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection