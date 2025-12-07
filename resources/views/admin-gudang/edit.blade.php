@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Gudang untuk {{ $admin->name }}</h1>
        <a href="{{ route('admin-gudang.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pilih Gudang yang Dikelola</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin-gudang.update', $admin->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="gudangs"><strong>Gudang *</strong></label>
                    <p class="text-muted small mb-3">Admin bisa mengelola lebih dari satu gudang. Centang semua gudang yang ingin diberikan ke admin ini.</p>
                    
                    <div class="row">
                        @foreach($gudangs as $gudang)
                            <div class="col-md-6 mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="gudang_{{ $gudang->id }}" 
                                           name="gudangs[]" 
                                           value="{{ $gudang->id }}"
                                           {{ in_array($gudang->id, $assignedGudangs) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="gudang_{{ $gudang->id }}">
                                        <strong>{{ $gudang->nama_gudang }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $gudang->alamat ?? 'Alamat tidak tersedia' }}</small>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('gudangs')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label><strong>Gudang Aktif</strong></label>
                    <p class="text-muted small">
                        Ketika admin login, gudang berikut akan menjadi gudang aktifnya. 
                        Admin bisa mengubahnya melalui dropdown profile.
                    </p>
                    <select class="form-control" id="defaultGudang" disabled>
                        <option>Akan diatur ke gudang pertama yang dipilih</option>
                    </select>
                </div>

                <hr>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('admin-gudang.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="gudangs[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // Update default gudang jika ada yang di-check
        const checkedBoxes = document.querySelectorAll('input[name="gudangs[]"]:checked');
        if(checkedBoxes.length > 0) {
            const firstCheckedLabel = checkedBoxes[0].closest('.custom-checkbox').querySelector('label strong').textContent;
            document.getElementById('defaultGudang').innerHTML = `<option>${firstCheckedLabel}</option>`;
        }
    });
});

// Trigger initial state
document.querySelector('input[name="gudangs[]"]:checked')?.dispatchEvent(new Event('change'));
</script>
@endsection
