@extends('customer.layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
    <div class="mb-4">
        <a href="{{ route('customer.history') }}" class="btn btn-sm btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <h4 class="font-weight-bold text-dark mb-1">
            <i class="fas fa-file-invoice text-primary mr-1"></i> {{ $penjualan->number }}
        </h4>
        <p class="text-muted mb-0">
            {{ $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d F Y') : '-' }}
            @if($penjualan->status == 'Lunas')
                <span class="badge badge-success ml-2">Lunas</span>
            @elseif($penjualan->status == 'Approved')
                <span class="badge badge-info ml-2">Approved</span>
            @elseif($penjualan->status == 'Pending')
                <span class="badge badge-warning ml-2">Pending</span>
            @else
                <span class="badge badge-secondary ml-2">{{ $penjualan->status }}</span>
            @endif
        </p>
    </div>

    <div class="row">
        {{-- Info Transaksi --}}
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i> Info Transaksi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Invoice</td>
                            <td><strong>{{ $penjualan->number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $penjualan->tgl_transaksi ? $penjualan->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Gudang</td>
                            <td>{{ $penjualan->gudang->nama_gudang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sales</td>
                            <td>{{ $penjualan->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pajak</td>
                            <td>{{ $penjualan->tax_percentage ?? 0 }}%</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Memo</td>
                            <td>{{ $penjualan->memo ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Detail Produk --}}
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-boxes mr-1"></i> Detail Produk
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th>Satuan</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Diskon</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @forelse($penjualan->items as $item)
                                    @php
                                        $lineTotal = $item->jumlah_baris ?? (($item->harga_satuan ?? 0) * ($item->kuantitas ?? 0));
                                        $subtotal += $lineTotal;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $item->produk->nama_produk ?? $item->deskripsi ?? '-' }}</strong>
                                            @if($item->produk && $item->produk->item_code)
                                                <br><small class="text-muted">{{ $item->produk->item_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->kuantitas ?? 0 }}</td>
                                        <td>{{ $item->unit ?? ($item->produk->satuan ?? 'Pcs') }}</td>
                                        <td class="text-right">Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-right">
                                            @if($item->diskon > 0)
                                                <span class="text-danger">{{ $item->diskon }}%</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Tidak ada item.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-top">
                                    <td colspan="5" class="text-right text-muted">Subtotal</td>
                                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if(($penjualan->tax_percentage ?? 0) > 0)
                                    <tr>
                                        <td colspan="5" class="text-right text-muted">
                                            Pajak ({{ $penjualan->tax_percentage }}%)
                                        </td>
                                        <td class="text-right">
                                            Rp {{ number_format(($penjualan->grand_total ?? 0) - $subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr class="bg-light">
                                    <td colspan="5" class="text-right font-weight-bold">Grand Total</td>
                                    <td class="text-right font-weight-bold" style="font-size: 1.1rem; color: var(--primary);">
                                        Rp {{ number_format($penjualan->grand_total ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
