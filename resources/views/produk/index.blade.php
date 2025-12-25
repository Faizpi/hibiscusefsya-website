@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Master Produk</h1>
            <a href="{{ route('produk.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Produk Baru
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Produk</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($produks as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('produk.show', $item->id) }}" class="badge badge-dark" title="Lihat Detail & Barcode">
                                            <i class="fas fa-barcode fa-sm"></i> {{ $item->item_kode ?? $item->item_code ?? 'PRD'.$item->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('produk.show', $item->id) }}">
                                            {{ $item->item_nama ?? $item->nama_produk }}
                                        </a>
                                    </td>
                                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm dropdown-toggle no-caret" type="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                <a class="dropdown-item" href="{{ route('produk.show', $item->id) }}">
                                                    <i class="fas fa-eye fa-fw mr-2 text-info"></i> Lihat Detail
                                                </a>
                                                <a class="dropdown-item" href="{{ route('produk.edit', $item->id) }}">
                                                    <i class="fas fa-pen fa-fw mr-2 text-warning"></i> Edit
                                                </a>
                                                <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                    data-target="#deleteModal"
                                                    data-action="{{ route('produk.destroy', $item->id) }}">
                                                    <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>menghapus</strong> data ini?</p>
                    <p class="text-muted mb-0"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var action = button.data('action');
            $(this).find('#deleteForm').attr('action', action);
        });
    </script>
@endpush