@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Detail Kunjungan #{{ $kunjungan->custom_number ?? $kunjungan->id }}</h1>
            <div>
                @php $user = auth()->user(); @endphp

                {{-- Tombol Approve (Super Admin atau Admin yang ditunjuk) --}}
                @if($kunjungan->status == 'Pending')
                    @if($user->role == 'super_admin' || ($user->role == 'admin' && $kunjungan->approver_id == $user->id))
                        <form action="{{ route('kunjungan.approve', $kunjungan->id) }}" method="POST" class="d-inline"
                            title="Setujui data ini">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm shadow-sm"><i class="fas fa-check fa-sm"></i>
                                Setujui</button>
                        </form>
                    @endif
                @endif

                {{-- Tombol Cancel (Hanya super_admin bisa cancel Approved) --}}
                @if($kunjungan->status != 'Canceled')
                    @if($user->role == 'super_admin' || $kunjungan->status == 'Pending')
                        @if(in_array($user->role, ['admin', 'super_admin']))
                            <button type="button" class="btn btn-dark btn-sm shadow-sm" data-toggle="modal" data-target="#cancelModal">
                                <i class="fas fa-ban fa-sm"></i> Cancel
                            </button>
                        @endif
                    @endif
                @endif

                <button type="button" id="printBluetooth" class="btn btn-primary btn-sm shadow-sm" data-type="kunjungan"
                    data-url="{{ route('bluetooth.kunjungan', $kunjungan->id) }}">
                    <i class="fab fa-bluetooth-b fa-sm text-white"></i> Print Bluetooth
                </button>
                <a href="{{ route('kunjungan.print', $kunjungan->id) }}" target="_blank"
                    class="btn btn-info btn-sm shadow-sm">
                    <i class="fas fa-print fa-sm"></i> Cetak Struk
                </a>
                <button type="button" class="btn btn-success btn-sm shadow-sm" data-toggle="modal" data-target="#qrModal">
                    <i class="fas fa-qrcode fa-sm"></i> QR Code
                </button>
                <a href="{{ route('kunjungan.index') }}" class="btn btn-secondary btn-sm shadow-sm">
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
                <h6 class="m-0 font-weight-bold text-primary">Info Kunjungan</h6>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    {{-- KOLOM KIRI (INFO UTAMA) --}}
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Tujuan Kunjungan</strong></td>
                                <td>:
                                    @if($kunjungan->tujuan == 'Pemeriksaan Stock')
                                        <span class="badge badge-info">{{ $kunjungan->tujuan }}</span>
                                    @elseif($kunjungan->tujuan == 'Penagihan')
                                        <span class="badge badge-warning">{{ $kunjungan->tujuan }}</span>
                                    @else
                                        <span class="badge badge-success">{{ $kunjungan->tujuan }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>No. Kunjungan</strong></td>
                                <td>: {{ $kunjungan->custom_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pembuat</strong></td>
                                <td>: {{ $kunjungan->user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Approver</strong></td>
                                <td>: {{ $kunjungan->status == 'Pending' ? '-' : ($kunjungan->approver->name ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Gudang</strong></td>
                                <td>: {{ optional($kunjungan->gudang)->nama_gudang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tgl. Kunjungan</strong></td>
                                <td>: {{ $kunjungan->tgl_kunjungan->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td>: {{ $kunjungan->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                        </table>
                    </div>

                    {{-- KOLOM KANAN (INFO STATUS) --}}
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 30%;"><strong>Status</strong></td>
                                <td>:
                                    @if($kunjungan->status == 'Approved')
                                        <span class="badge badge-success">{{ $kunjungan->status }}</span>
                                    @elseif($kunjungan->status == 'Pending')
                                        <span class="badge badge-warning">{{ $kunjungan->status }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ $kunjungan->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Sales/Kontak</strong></td>
                                <td>: {{ $kunjungan->sales_nama }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $kunjungan->sales_email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Alamat</strong></td>
                                <td>: {{ $kunjungan->sales_alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Koordinat</strong></td>
                                <td>:
                                    @if($kunjungan->koordinat)
                                        <a href="https://www.google.com/maps?q={{ $kunjungan->koordinat }}" target="_blank"
                                            class="text-primary">
                                            {{ $kunjungan->koordinat }} <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Lampiran</strong></td>
                                <td>:
                                    @if($kunjungan->lampiran_path)
                                        <a href="{{ asset('storage/' . $kunjungan->lampiran_path) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- MEMO --}}
                @if($kunjungan->memo)
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">Memo / Catatan:</h6>
                                    <p class="mb-0">{{ $kunjungan->memo }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Cancel --}}
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Pembatalan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('kunjungan.cancel', $kunjungan->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin membatalkan kunjungan ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">QR Code Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qr-code-container">
                        {!! QrCode::size(200)->generate(route('public.invoice.kunjungan', $kunjungan->uuid)) !!}
                    </div>
                    <p class="mt-3 small text-muted">Scan untuk melihat invoice</p>
                    <a href="{{ route('public.invoice.kunjungan', $kunjungan->uuid) }}" target="_blank"
                        class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i> Buka Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection