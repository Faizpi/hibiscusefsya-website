@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Master Stok</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tambah / Update Stok Awal</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('stok.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="gudang_id">Pilih Gudang *</label>
                            <select name="gudang_id" id="gudang_id" class="form-control @error('gudang_id') is-invalid @enderror" required>
                                <option value="">Pilih...</option>
                                @foreach($gudangs as $gudang)
                                    <option value="{{ $gudang->id }}">{{ $gudang->nama_gudang }}</option>
                                @endforeach
                            </select>
                            @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="produk_id">Pilih Produk *</label>
                            <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror" required>
                                <option value="">Pilih...</option>
                                @foreach($produks as $produk)
                                    <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                                @endforeach
                            </select>
                            @error('produk_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="stok">Jumlah Stok *</label>
                            <input type="number" name="stok" id="stok" class="form-control @error('stok') is-invalid @enderror" value="0" min="0" required>
                            @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Stok</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Stok per Gudang</h6>
                </div>
                <div class="card-body">
                    {{-- Wrapper untuk accordion --}}
                    <div id="stokAccordion">
                        @forelse ($gudangsWithStok as $gudang)
                            <div class="card mb-1">
                                {{-- Header Gudang (Tombol Toggle) --}}
                                <div class="card-header py-3" id="heading-{{ $gudang->id }}">
                                    <h6 class="m-0 font-weight-bold d-flex justify-content-between align-items-center">
                                        <a href="#" class="text-primary" data-toggle="collapse" data-target="#collapse-{{ $gudang->id }}" aria-expanded="false" aria-controls="collapse-{{ $gudang->id }}">
                                            {{ $gudang->nama_gudang }}
                                        </a>
                                        {{-- Hitung total stok di gudang ini --}}
                                        <span class="badge badge-light badge-pill">{{ $gudang->produkStok->sum('stok') }} Total Item</span>
                                    </h6>
                                </div>
                                
                                {{-- Konten Rincian Produk (Collapsible) --}}
                                <div id="collapse-{{ $gudang->id }}" class="collapse" aria-labelledby="heading-{{ $gudang->id }}" data-parent="#stokAccordion">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0">
                                                <thead>
                                                    <tr class="bg-light">
                                                        <th class="pl-4">Produk</th>
                                                        <th>Item Code</th>
                                                        <th class="text-right pr-4">Stok</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @forelse($gudang->produkStok as $stokItem)
                                                    {{-- Tampilkan hanya jika produk ada (menghindari error) --}}
                                                    @if($stokItem->produk) 
                                                        <tr>
                                                            <td class="pl-4">{{ $stokItem->produk->nama_produk }}</td>
                                                            <td>{{ $stokItem->produk->item_code }}</td>
                                                            <td class="text-right font-weight-bold pr-4">{{ $stokItem->stok }}</td>
                                                        </tr>
                                                    @endif
                                                @empty
                                                    <tr><td colspan="3" class="text-center p-3">Belum ada stok produk di gudang ini.</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">Belum ada gudang yang dibuat. Silakan tambahkan gudang di menu "Master Gudang".</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection