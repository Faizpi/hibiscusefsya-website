@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Tambah User Baru</h1>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_telp">No. Telepon</label>
                                <input type="text" class="form-control @error('no_telp') is-invalid @enderror" id="no_telp"
                                    name="no_telp" value="{{ old('no_telp') }}">
                                @error('no_telp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat"
                                    name="alamat" value="{{ old('alamat') }}">
                                @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select class="form-control @error('role') is-invalid @enderror" id="role" name="role"
                                    required>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>{{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Single gudang selection for user -->
                            <div id="single_gudang_section">
                                <div class="form-group">
                                    <label for="gudang_id">Gudang <span id="gudang_required" class="text-danger"
                                            style="display:none;">*</span></label>
                                    <select class="form-control @error('gudang_id') is-invalid @enderror" id="gudang_id"
                                        name="gudang_id">
                                        <option value="">-- Pilih Gudang --</option>
                                        @foreach($gudangs as $gudang)
                                            <option value="{{ $gudang->id }}" {{ old('gudang_id') == $gudang->id ? 'selected' : '' }}>
                                                {{ $gudang->nama_gudang }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted" id="gudang_help">Wajib untuk role User dan Admin (untuk admin dapat diedit di menu Admin Gudang).</small>
                                    @error('gudang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <!-- Multi gudang selection for admin/spectator -->
                            <div id="multi_gudang_section" style="display:none;">
                                <div class="form-group">
                                    <label id="multi_gudang_label">Gudang <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-2" id="multi_gudang_help"></p>
                                    @error('gudangs') <div class="alert alert-danger small mb-2">{{ $message }}</div> @enderror
                                    <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                                        @foreach($gudangs as $gudang)
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" class="custom-control-input multi-gudang-checkbox" 
                                                    id="gudang_{{ $gudang->id }}" name="gudangs[]" value="{{ $gudang->id }}"
                                                    {{ in_array($gudang->id, old('gudangs', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="gudang_{{ $gudang->id }}">
                                                    {{ $gudang->nama_gudang }}
                                                    <small class="text-muted d-block">{{ $gudang->alamat ?? '-' }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="form-text text-danger" id="no_gudang_error" style="display:none;">
                                        Pilih minimal satu gudang
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Konfirmasi Password *</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            function toggleGudangFields() {
                var role = $('#role').val();
                
                if (role === 'admin' || role === 'spectator') {
                    // Show multi-select for admin and spectator
                    $('#single_gudang_section').hide();
                    $('#multi_gudang_section').show();
                    $('#gudang_id').prop('required', false);
                    
                    // Update label and help text
                    if (role === 'admin') {
                        $('#multi_gudang_label').text('Gudang yang Dikelola');
                        $('#multi_gudang_help').text('Pilih satu atau lebih gudang yang akan dikelola admin ini. Admin dapat mengelola stock dan transaksi di gudang yang dipilih.');
                    } else {
                        $('#multi_gudang_label').text('Gudang yang Dapat Diakses');
                        $('#multi_gudang_help').text('Pilih satu atau lebih gudang untuk spectator ini. Spectator hanya dapat melihat (read-only) data dari gudang yang dipilih.');
                    }
                } else if (role === 'user') {
                    // Show single select for user
                    $('#single_gudang_section').show();
                    $('#multi_gudang_section').hide();
                    $('#gudang_id').prop('required', true);
                    $('#gudang_required').show();
                } else {
                    // Hide both for other roles
                    $('#single_gudang_section').show();
                    $('#multi_gudang_section').hide();
                    $('#gudang_id').prop('required', false);
                    $('#gudang_required').hide();
                }
            }

            // Validate at least one gudang for admin/spectator
            function validateMultiGudang() {
                var role = $('#role').val();
                if (role === 'admin' || role === 'spectator') {
                    var checkedCount = $('input[name="gudangs[]"]:checked').length;
                    if (checkedCount === 0) {
                        $('#no_gudang_error').show();
                        return false;
                    } else {
                        $('#no_gudang_error').hide();
                        return true;
                    }
                }
                return true;
            }

            // Initial check
            toggleGudangFields();

            // On role change
            $('#role').on('change', toggleGudangFields);

            // On gudang checkbox change
            $(document).on('change', 'input[name="gudangs[]"]', function() {
                validateMultiGudang();
            });

            // Form submission validation
            $('form').on('submit', function(e) {
                if (!validateMultiGudang()) {
                    e.preventDefault();
                    return false;
                }

                // For admin/spectator without single gudang_id, unset it
                var role = $('#role').val();
                if (role === 'admin' || role === 'spectator') {
                    $('#gudang_id').prop('disabled', true);
                }
            });
        });
    </script>
@endpush