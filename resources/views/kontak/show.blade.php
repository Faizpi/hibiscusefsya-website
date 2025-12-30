@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4 page-header-mobile">
            <h1 class="h3 mb-0 text-gray-800">Detail Kontak</h1>
            <div class="show-action-buttons">
                <a href="{{ route('kontak.edit', $kontak->id) }}" class="btn btn-warning btn-sm shadow-sm">
                    <i class="fas fa-edit fa-sm"></i> Edit
                </a>
                <a href="{{ route('kontak.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            {{-- Kolom Kiri: Info Kontak --}}
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user"></i> Informasi Kontak
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Kode Kontak</strong></td>
                                        <td width="5%">:</td>
                                        <td><span class="badge badge-dark font-weight-bold"
                                                style="font-size: 1rem;">{{ $kontak->kode_kontak }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama</strong></td>
                                        <td>:</td>
                                        <td>{{ $kontak->nama }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>:</td>
                                        <td>{{ $kontak->email ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>No. Telepon</strong></td>
                                        <td>:</td>
                                        <td>{{ $kontak->no_telp ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Alamat</strong></td>
                                        <td width="5%">:</td>
                                        <td>{{ $kontak->alamat ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Diskon</strong></td>
                                        <td>:</td>
                                        <td><span class="badge badge-success">{{ $kontak->diskon_persen ?? 0 }}%</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dibuat</strong></td>
                                        <td>:</td>
                                        <td>{{ $kontak->created_at ? $kontak->created_at->format('d M Y, H:i') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Diupdate</strong></td>
                                        <td>:</td>
                                        <td>{{ $kontak->updated_at ? $kontak->updated_at->format('d M Y, H:i') : '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
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
                            $barcodeUrl = 'https://barcodeapi.org/api/128/' . urlencode($kontak->kode_kontak);
                        @endphp
                        <img src="{{ $barcodeUrl }}" alt="Barcode {{ $kontak->kode_kontak }}" class="img-fluid mb-3"
                            style="max-width: 250px;">
                        <p class="font-weight-bold mb-1">{{ $kontak->kode_kontak }}</p>
                        <small class="text-muted">{{ $kontak->nama }}</small>

                        <hr>

                        <div class="btn-group">
                            <a href="{{ route('kontak.download', $kontak->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                            <a href="{{ route('kontak.print', $kontak->id) }}" target="_blank"
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
                            $qrData = "KONTAK\nKode: {$kontak->kode_kontak}\nNama: {$kontak->nama}\nTelp: {$kontak->no_telp}";
                            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrData);
                        @endphp
                        <img src="{{ $qrUrl }}" alt="QR Code" class="img-fluid mb-2" style="max-width: 150px;">
                        <p class="text-muted small mb-0">Scan untuk info kontak</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection