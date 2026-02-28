@extends('customer.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div style="margin-bottom: 24px;">
        <h4 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 4px;">
            Selamat Datang, {{ $kontak->nama }}
        </h4>
        <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Berikut ringkasan data akun dan transaksi Anda.</p>
    </div>

    {{-- Stat Cards --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px;">
        <div class="card">
            <div class="card-body" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Kode Kontak</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #111827;">{{ $kontak->kode_kontak ?? '-' }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Diskon Anda</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #1e40af;">{{ $kontak->diskon_persen ?? 0 }}%</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Total Transaksi</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #111827;">{{ $totalTransaksi }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding: 16px;">
                <div style="font-size: 0.72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Total Nilai</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #111827;">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        {{-- Profil --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-user"></i> Data Profil</div>
            <div class="card-body" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 12px 20px; color: #6b7280; font-size: 0.82rem; width: 35%; border-bottom: 1px solid #f3f4f6;">Nama</td>
                        <td style="padding: 12px 20px; font-size: 0.82rem; font-weight: 600; border-bottom: 1px solid #f3f4f6;">{{ $kontak->nama }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 20px; color: #6b7280; font-size: 0.82rem; border-bottom: 1px solid #f3f4f6;">No. Telepon</td>
                        <td style="padding: 12px 20px; font-size: 0.82rem; border-bottom: 1px solid #f3f4f6;">{{ $kontak->no_telp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 20px; color: #6b7280; font-size: 0.82rem; border-bottom: 1px solid #f3f4f6;">Email</td>
                        <td style="padding: 12px 20px; font-size: 0.82rem; border-bottom: 1px solid #f3f4f6;">{{ $kontak->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 20px; color: #6b7280; font-size: 0.82rem; border-bottom: 1px solid #f3f4f6;">Alamat</td>
                        <td style="padding: 12px 20px; font-size: 0.82rem; border-bottom: 1px solid #f3f4f6;">{{ $kontak->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 20px; color: #6b7280; font-size: 0.82rem;">Diskon Bawaan</td>
                        <td style="padding: 12px 20px; font-size: 0.82rem;"><span class="badge badge-info">{{ $kontak->diskon_persen ?? 0 }}%</span></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Transaksi Terakhir --}}
        <div class="card">
            <div class="card-header" style="justify-content: space-between;">
                <span><i class="fas fa-clock"></i> Transaksi Terakhir</span>
                <a href="{{ route('customer.history') }}" class="btn btn-sm btn-outline">
                    Lihat Semua <i class="fas fa-arrow-right" style="font-size: 0.65rem;"></i>
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                @php
                    $recentTransaksi = \App\Penjualan::where('pelanggan', $kontak->nama)
                        ->whereIn('status', ['Approved', 'Lunas', 'Pending'])
                        ->orderBy('tgl_transaksi', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                @if($recentTransaksi->count() > 0)
                    @foreach($recentTransaksi as $trx)
                        <a href="{{ route('customer.history.detail', $trx->id) }}" 
                           style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; text-decoration: none; color: inherit; border-bottom: 1px solid #f3f4f6; transition: background 0.15s;">
                            <div>
                                <div style="font-size: 0.82rem; font-weight: 600; color: #111827;">{{ $trx->number }}</div>
                                <div style="font-size: 0.72rem; color: #6b7280; margin-top: 2px;">
                                    {{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.82rem; font-weight: 600; color: #111827;">
                                    Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}
                                </div>
                                @if($trx->status == 'Lunas')
                                    <span class="badge badge-success">Lunas</span>
                                @elseif($trx->status == 'Approved')
                                    <span class="badge badge-info">Approved</span>
                                @elseif($trx->status == 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                        <i class="fas fa-inbox" style="font-size: 1.5rem; margin-bottom: 8px; display: block;"></i>
                        <span style="font-size: 0.82rem;">Belum ada transaksi</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: repeat(4"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
