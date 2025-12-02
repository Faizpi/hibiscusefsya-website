@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Gudang: {{ $gudang->nama_gudang }}</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('gudang.update', $gudang->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nama_gudang">Nama Gudang *</label>
                    <input type="text" class="form-control @error('nama_gudang') is-invalid @enderror" id="nama_gudang" name="nama_gudang" value="{{ old('nama_gudang', $gudang->nama_gudang) }}" required>
                    @error('nama_gudang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="alamat_gudang">Alamat</label>
                    <textarea class="form-control @error('alamat_gudang') is-invalid @enderror" id="alamat_gudang" name="alamat_gudang" rows="3">{{ old('alamat_gudang', $gudang->alamat_gudang) }}</textarea>
                    @error('alamat_gudang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="text-right">
                    <a href="{{ route('gudang.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection