@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Edit Produk: {{ $produk->nama_produk }}</h1>
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('produk.update', $produk->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk *</label>
                        <input type="text" class="form-control @error('nama_produk') is-invalid @enderror" id="nama_produk"
                            name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                        @error('nama_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="item_code">Item Code (SKU)</label>
                                <input type="text" class="form-control @error('item_code') is-invalid @enderror"
                                    id="item_code" name="item_code" value="{{ old('item_code', $produk->item_code) }}">
                                @error('item_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="harga">Harga Retail *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control @error('harga') is-invalid @enderror"
                                        id="harga" name="harga" value="{{ old('harga', $produk->harga) }}" required>
                                </div>
                                <small class="text-muted d-block mt-1" id="harga-preview">Preview: {{ format_rupiah(old('harga', $produk->harga)) }}</small>
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="harga_grosir">Harga Grosir</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control @error('harga_grosir') is-invalid @enderror"
                                        id="harga_grosir" name="harga_grosir" value="{{ old('harga_grosir', $produk->harga_grosir) }}">
                                </div>
                                <small class="text-muted d-block mt-1" id="harga-grosir-preview">Preview: {{ format_rupiah(old('harga_grosir', $produk->harga_grosir ?? 0)) }}</small>
                                @error('harga_grosir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="satuan">Satuan *</label>
                        <select class="form-control @error('satuan') is-invalid @enderror" id="satuan" name="satuan"
                            required>
                            <option value="Pcs" {{ old('satuan', $produk->satuan) == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                            <option value="Lusin" {{ old('satuan', $produk->satuan) == 'Lusin' ? 'selected' : '' }}>Lusin
                            </option>
                            <option value="Karton" {{ old('satuan', $produk->satuan) == 'Karton' ? 'selected' : '' }}>Karton
                            </option>
                        </select>
                        @error('satuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi"
                            name="deskripsi" rows="3">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-right">
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const currencyFormatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            const updatePreview = (inputId, previewId) => {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                if (!input || !preview) return;

                const value = parseFloat(input.value) || 0;
                preview.textContent = 'Preview: ' + currencyFormatter.format(value).replace(/\u00A0/g, ' ');
            };

            ['harga', 'harga_grosir'].forEach(function (field) {
                const input = document.getElementById(field);
                const previewId = field === 'harga' ? 'harga-preview' : 'harga-grosir-preview';
                if (!input) return;

                updatePreview(field, previewId);
                input.addEventListener('input', function () {
                    updatePreview(field, previewId);
                });
            });
        });
    </script>
@endpush