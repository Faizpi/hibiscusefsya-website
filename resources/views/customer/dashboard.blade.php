@extends('customer.layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Promo Banner --}}
    <a href="https://bodycare.hibiscusefsya.com/" target="_blank" class="promo-banner">
        <div class="promo-glow"></div>
        <div class="promo-content">
            <div class="promo-icon-wrap">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="5" x2="5" y2="19"></line>
                    <circle cx="6.5" cy="6.5" r="2.5"></circle>
                    <circle cx="17.5" cy="17.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="promo-text">
                <span class="promo-title">Promo Spesial!</span>
                <span class="promo-desc">Cek katalog & penawaran terbaik kami</span>
            </div>
        </div>
        <div class="promo-cta">
            <span class="promo-btn"><span class="promo-percent">%</span> Promo</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </div>
    </a>

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
                        <td class="profile-label" style="border-bottom: none;">Lokasi Gudang</td>
                        <td class="profile-value" style="border-bottom: none;">{{ optional($kontak->gudang)->nama_gudang ?? '-' }}</td>
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
        /* Promo Banner */
        .promo-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #dc2626 0%, #e11d48 50%, #be123c 100%);
            border-radius: 16px;
            padding: 18px 24px;
            margin-bottom: 28px;
            text-decoration: none;
            color: #fff;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.3);
        }

        .promo-banner:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(220, 38, 38, 0.45);
            color: #fff;
            text-decoration: none;
        }

        .promo-glow {
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .promo-content {
            display: flex;
            align-items: center;
            gap: 16px;
            z-index: 1;
        }

        .promo-icon-wrap {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            backdrop-filter: blur(8px);
        }

        .promo-title {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .promo-desc {
            display: block;
            font-size: 0.82rem;
            opacity: 0.85;
            margin-top: 2px;
        }

        .promo-cta {
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 1;
            flex-shrink: 0;
        }

        .promo-btn {
            background: rgba(255,255,255,0.22);
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            backdrop-filter: blur(8px);
            letter-spacing: 0.3px;
        }

        .promo-percent {
            display: inline-block;
            background: #fff;
            color: #dc2626;
            width: 22px;
            height: 22px;
            line-height: 22px;
            text-align: center;
            border-radius: 6px;
            font-weight: 800;
            font-size: 0.78rem;
            margin-right: 6px;
            vertical-align: middle;
        }

        @media (max-width: 600px) {
            .promo-banner {
                flex-direction: column;
                gap: 14px;
                padding: 18px 20px;
                align-items: flex-start;
            }

            .promo-cta {
                align-self: flex-end;
            }

            .promo-icon-wrap {
                width: 40px;
                height: 40px;
            }

            .promo-icon-wrap svg {
                width: 22px;
                height: 22px;
            }
        }

        .page-header {
            margin-bottom: 40px;
            padding-top: 8px;
        }

        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: #6b7280;
            margin: 0;
            padding-bottom: 12px;
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