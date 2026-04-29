@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center mb-4">
        <div class="mr-3" style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#3B82F6,#8B5CF6);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-user-circle" style="color:#fff;font-size:1.25rem;"></i>
        </div>
        <div>
            <h1 class="mb-0" style="font-size:1.35rem;font-weight:700;color:var(--text-primary);">Profil Saya</h1>
            <p class="mb-0" style="font-size:0.8rem;color:var(--text-muted);">Kelola informasi akun Anda</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert"
             style="border-radius:10px;border:none;background:rgba(16,185,129,0.1);color:#065f46;border-left:4px solid #10b981;">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close ml-auto" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert"
             style="border-radius:10px;border:none;background:rgba(239,68,68,0.1);color:#7f1d1d;border-left:4px solid #ef4444;">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="close ml-auto" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        {{-- Kolom Kiri: Kartu Identitas --}}
        <div class="col-lg-4 mb-4">

            {{-- Avatar Card --}}
            <div class="card mb-3" style="border-radius:16px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);height:80px;"></div>
                <div class="card-body text-center" style="padding-top:0;position:relative;">
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#8B5CF6);border:4px solid #fff;
                                display:flex;align-items:center;justify-content:center;margin:-40px auto 12px;
                                box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                        <span style="color:#fff;font-size:2rem;font-weight:700;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
                    </div>
                    <h5 class="font-weight-700 mb-0" style="color:var(--text-primary);">{{ auth()->user()->name }}</h5>
                    <p class="text-muted mb-2" style="font-size:0.8rem;">{{ auth()->user()->email }}</p>
                    <span class="badge badge-pill" style="background:rgba(59,130,246,0.12);color:#3B82F6;font-size:0.75rem;padding:6px 14px;font-weight:600;">
                        {{ ucfirst(str_replace('_',' ', auth()->user()->role)) }}
                    </span>
                </div>
            </div>

            {{-- Info Ringkas --}}
            <div class="card" style="border-radius:16px;">
                <div class="card-body" style="padding:1.25rem;">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        <strong style="font-size:0.875rem;">Informasi Akun</strong>
                    </div>
                    <div class="info-row">
                        <small class="text-muted d-block" style="font-size:0.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">Nama</small>
                        <span style="font-size:0.875rem;color:var(--text-primary);">{{ auth()->user()->name }}</span>
                    </div>
                    <hr style="margin:.75rem 0;border-color:var(--border-color);">
                    <div class="info-row">
                        <small class="text-muted d-block" style="font-size:0.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">Email</small>
                        <span style="font-size:0.875rem;color:var(--text-primary);">{{ auth()->user()->email }}</span>
                    </div>
                    <hr style="margin:.75rem 0;border-color:var(--border-color);">
                    <div class="info-row">
                        <small class="text-muted d-block" style="font-size:0.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">No. Telepon</small>
                        <span style="font-size:0.875rem;color:var(--text-primary);">{{ auth()->user()->no_telp ?? '-' }}</span>
                    </div>
                    <hr style="margin:.75rem 0;border-color:var(--border-color);">
                    <div class="info-row">
                        <small class="text-muted d-block" style="font-size:0.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">Alamat</small>
                        <span style="font-size:0.875rem;color:var(--text-primary);">{{ auth()->user()->alamat ?? '-' }}</span>
                    </div>
                    @if(auth()->user()->getCurrentGudang())
                        <hr style="margin:.75rem 0;border-color:var(--border-color);">
                        <div class="info-row">
                            <small class="text-muted d-block" style="font-size:0.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">Gudang Aktif</small>
                            <span style="font-size:0.875rem;color:var(--text-primary);">{{ auth()->user()->getCurrentGudang()->nama_gudang }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Edit --}}
        <div class="col-lg-8">

            {{-- Form Edit Profil --}}
            <div class="card mb-4" style="border-radius:16px;">
                <div class="card-header" style="border-radius:16px 16px 0 0;background:#fff;">
                    <div class="d-flex align-items-center">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;margin-right:.75rem;">
                            <i class="fas fa-edit" style="color:#3B82F6;font-size:0.875rem;"></i>
                        </div>
                        <strong>Edit Profil</strong>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('profil.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama (readonly) --}}
                        <div class="form-group">
                            <label for="name">
                                Nama
                                <span class="badge badge-sm ml-1" style="background:rgba(107,114,128,0.1);color:#6b7280;font-size:0.65rem;padding:2px 6px;border-radius:4px;font-weight:600;">
                                    <i class="fas fa-lock" style="font-size:0.55rem;"></i> Hanya Baca
                                </span>
                            </label>
                            <input type="text" id="name" class="form-control" value="{{ auth()->user()->name }}"
                                   readonly style="background:#f9fafb;cursor:not-allowed;color:var(--text-muted);">
                            <small class="form-text text-muted">Nama tidak dapat diubah. Hubungi Super Admin jika perlu perubahan.</small>
                        </div>

                        {{-- Email (readonly) --}}
                        <div class="form-group">
                            <label for="email">
                                Email
                                <span class="badge badge-sm ml-1" style="background:rgba(107,114,128,0.1);color:#6b7280;font-size:0.65rem;padding:2px 6px;border-radius:4px;font-weight:600;">
                                    <i class="fas fa-lock" style="font-size:0.55rem;"></i> Hanya Baca
                                </span>
                            </label>
                            <input type="email" id="email" class="form-control" value="{{ auth()->user()->email }}"
                                   readonly style="background:#f9fafb;cursor:not-allowed;color:var(--text-muted);">
                            <small class="form-text text-muted">Email tidak dapat diubah.</small>
                        </div>

                        {{-- No Telp --}}
                        <div class="form-group">
                            <label for="no_telp">No. Telepon</label>
                            <input type="tel" id="no_telp" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror"
                                   value="{{ old('no_telp', auth()->user()->no_telp) }}"
                                   placeholder="Contoh: 08123456789" maxlength="20">
                            @error('no_telp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                                      rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', auth()->user()->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Ubah Password --}}
            <div class="card" style="border-radius:16px;">
                <div class="card-header" style="border-radius:16px 16px 0 0;background:#fff;">
                    <div class="d-flex align-items-center">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;margin-right:.75rem;">
                            <i class="fas fa-lock" style="color:#f59e0b;font-size:0.875rem;"></i>
                        </div>
                        <strong>Ubah Password</strong>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('profil.change-password') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" id="current_password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       placeholder="Masukkan password saat ini">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="current_password"
                                            style="border-radius:0 8px 8px 0;border-color:var(--border-color);">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <div class="input-group">
                                <input type="password" id="new_password" name="new_password"
                                       class="form-control @error('new_password') is-invalid @enderror"
                                       placeholder="Minimal 8 karakter">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="new_password"
                                            style="border-radius:0 8px 8px 0;border-color:var(--border-color);">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                       class="form-control"
                                       placeholder="Ulangi password baru">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="new_password_confirmation"
                                            style="border-radius:0 8px 8px 0;border-color:var(--border-color);">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="fas fa-key mr-1"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Toggle show/hide password
    document.querySelectorAll('.toggle-pass').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>
@endsection
