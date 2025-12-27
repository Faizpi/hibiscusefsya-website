@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Manajemen Gudang Spectator</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Spectator dan Gudang yang Dapat Diakses</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nama Spectator</th>
                                <th>Email</th>
                                <th>Gudang yang Dapat Diakses</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($spectators as $spectator)
                                <tr>
                                    <td><strong>{{ $spectator->name }}</strong></td>
                                    <td>{{ $spectator->email }}</td>
                                    <td>
                                        @if($spectator->spectatorGudangs->count() > 0)
                                            <div class="d-flex flex-wrap align-items-center">
                                                @foreach($spectator->spectatorGudangs as $gudang)
                                                    <span class="badge badge-info mr-1 mb-1">{{ $gudang->nama_gudang }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge badge-warning">Belum ada gudang</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('spectator-gudang.edit', $spectator->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Tidak ada spectator yang terdaftar
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection