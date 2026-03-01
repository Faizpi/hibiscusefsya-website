@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Edit Kontak: {{ $kontak->nama }}</h1>
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('kontak.update', $kontak->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode_kontak">Kode Kontak</label>
                                <input type="text" class="form-control @error('kode_kontak') is-invalid @enderror"
                                    id="kode_kontak" name="kode_kontak"
                                    value="{{ old('kode_kontak', $kontak->kode_kontak) }}">
                                <small class="form-text text-muted">Kode untuk barcode/QR code</small>
                                @error('kode_kontak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nama">Nama Kontak *</label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                                    name="nama" value="{{ old('nama', $kontak->nama) }}" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email', $kontak->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_telp">No. Telepon</label>
                                <input type="text" class="form-control @error('no_telp') is-invalid @enderror" id="no_telp"
                                    name="no_telp" value="{{ old('no_telp', $kontak->no_telp) }}">
                                <small class="form-text text-muted">No. Telp akan menjadi username login customer</small>
                                @error('no_telp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pin">PIN Customer (6 digit)</label>
                                <input type="text" class="form-control @error('pin') is-invalid @enderror" id="pin"
                                    name="pin" value="{{ old('pin', $kontak->pin) }}" maxlength="6"
                                    placeholder="Contoh: 123456">
                                <small class="form-text text-muted">PIN untuk login portal customer. Kosongkan jika belum
                                    perlu.</small>
                                @error('pin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat"
                            rows="3">{{ old('alamat', $kontak->alamat) }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="diskon_persen">Diskon Bawaan (%)</label>
                        <input type="number" class="form-control @error('diskon_persen') is-invalid @enderror"
                            id="diskon_persen" name="diskon_persen"
                            value="{{ old('diskon_persen', $kontak->diskon_persen) }}" min="0" max="100" step="0.01">
                        @error('diskon_persen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="gudang_id">Gudang</label>
                        <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id" name="gudang_id">
                            <option value="">-- Tidak Ada Gudang --</option>
                            @foreach($gudangs as $g)
                                <option value="{{ $g->id }}" {{ old('gudang_id', $kontak->gudang_id) == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama_gudang }}
                                </option>
                            @endforeach
                        </select>
                        @error('gudang_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-right">
                        <a href="{{ route('kontak.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection