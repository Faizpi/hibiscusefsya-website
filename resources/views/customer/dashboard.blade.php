@extends('customer.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Selamat Datang, {{ $kontak->nama }}</h4>
        <p class="page-subtitle">Berikut ringkasan data akun dan transaksi Anda.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Kode Kontak</div>
                <div class="stat-value">{{ $kontak->kode_kontak ?? '-' }}</div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Diskon Anda</div>
                <div class="stat-value" style="color: #1e40af;">{{ $kontak->diskon_persen ?? 0 }}%</div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value">{{ $totalTransaksi }}</div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Nilai</div>
                <div class="stat-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="content-grid">
        {{-- Profil --}}
        <div class="card">
            <div class="card-header">Data Profil</div>
            <div class="card-body" style="padding: 0;">
                <table style="width: 100%;">
                    <tr>
                        <td class="profile-label">Nama</td>
                        <td class="profile-value">{{ $kontak->nama }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">No. Telepon</td>
                        <td class="profile-value">{{ $kontak->no_telp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Email</td>
                        <td class="profile-value">{{ $kontak->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Alamat</td>
                        <td class="profile-value">{{ $kontak->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label" style="border-bottom: none;">Diskon Bawaan</td>
                        <td class="profile-value" style="border-bottom: none;"><span
                                class="badge badge-info">{{ $kontak->diskon_persen ?? 0 }}%</span></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Transaksi Terakhir --}}
        <div class="card">
            <div class="card-header" style="justify-content: space-between;">
                <span>Transaksi Terakhir</span>
                <a href="{{ route('customer.history') }}" class="btn btn-sm btn-outline">
                    Lihat Semua &#8594;
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
                        <a href="{{ route('customer.history.detail', $trx->id) }}" class="trx-item">
                            <div>
                                <div class="trx-number">{{ $trx->number }}</div>
                                <div class="trx-date">{{ $trx->tgl_transaksi ? $trx->tgl_transaksi->format('d M Y') : '-' }}</div>
                            </div>
                            <div style="text-align: right;">
                                <div class="trx-amount">Rp {{ number_format($trx->grand_total ?? 0, 0, ',', '.') }}</div>
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
                    <div class="empty-state">
                        <span>Belum ada transaksi</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .page-header {
            margin-bottom: 40px;
            padding-top: 8px;
        }

        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
            margin: 0;
            padding-bottom: 4px;
            line-height: 1.5;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card .card-body {
            padding: 18px 20px;
        }

        .stat-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #111827;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .profile-label {
            padding: 14px 22px;
            color: #6b7280;
            font-size: 0.9rem;
            width: 35%;
            border-bottom: 1px solid #f3f4f6;
        }

        .profile-value {
            padding: 14px 22px;
            font-size: 0.9rem;
            font-weight: 600;
            border-bottom: 1px solid #f3f4f6;
        }

        .trx-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 22px;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s;
        }

        .trx-item:hover {
            background: #f9fafb;
        }

        .trx-number {
            font-size: 0.9rem;
            font-weight: 600;
            color: #111827;
        }

        .trx-date {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 2px;
        }

        .trx-amount {
            font-size: 0.9rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 44px 20px;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush