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
            <button type="button" class="close ml-auto" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert"
             style="border-radius:10px;border:none;background:rgba(239,68,68,0.1);color:#7f1d1d;border-left:4px solid #ef4444;">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            <button type="button" class="close ml-auto" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        {{-- Kolom Kiri --}}
        <div class="col-lg-4 mb-4">

            {{-- Avatar Card --}}
            <div class="card mb-3" style="border-radius:16px;overflow:hidden;">
                {{-- Banner gradient (dekoratif saja, tidak menutup avatar) --}}
                <div style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);height:70px;"></div>

                <div class="card-body text-center" style="padding-top:0;position:relative;">
                    {{-- Avatar circle -- ditarik ke atas overlap banner --}}
                    <div class="profile-avatar-wrapper" style="position:relative;display:inline-block;margin:-44px auto 12px;">
                        <div id="avatarCircle" style="
                            width:88px;height:88px;border-radius:50%;
                            overflow:hidden;
                            border:4px solid #fff;
                            box-shadow:0 4px 14px rgba(59,130,246,0.25);
                            background:linear-gradient(135deg,#3B82F6,#8B5CF6);
                            display:flex;align-items:center;justify-content:center;
                            position:relative;
                        ">
                            @if($user->avatar)
                                <img id="avatarImg" src="{{ $user->avatarUrl() }}" alt="Avatar"
                                     style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                <span id="avatarInitial" style="color:#fff;font-size:2.25rem;font-weight:700;line-height:1;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>

                        {{-- Tombol kamera overlay --}}
                        <label for="avatarFileInput" title="Ganti Foto" style="
                            position:absolute;bottom:2px;right:2px;
                            width:28px;height:28px;border-radius:50%;
                            background:#3B82F6;border:2px solid #fff;
                            display:flex;align-items:center;justify-content:center;
                            cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.2);
                            transition:background .15s;
                        " onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#3B82F6'">
                            <i class="fas fa-camera" style="color:#fff;font-size:0.65rem;"></i>
                        </label>
                    </div>

                    <h5 class="mb-0" style="font-weight:700;color:var(--text-primary);">{{ $user->name }}</h5>
                    <p class="text-muted mb-2" style="font-size:0.8rem;">{{ $user->email }}</p>
                    <span class="badge badge-pill" style="background:rgba(59,130,246,0.12);color:#3B82F6;font-size:0.75rem;padding:6px 14px;font-weight:600;">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>

                    {{-- Upload form (hidden, triggered by label) --}}
                    <form id="avatarForm" action="{{ route('profil.avatar') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <input type="file" id="avatarFileInput" name="avatar" accept="image/*" style="display:none;">
                        <div id="avatarPreviewBar" style="display:none;" class="mt-2">
                            <img id="avatarPreviewImg" src="" alt="Preview" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #3B82F6;margin-bottom:8px;">
                            <br>
                            <button type="submit" class="btn btn-sm btn-primary mr-1">
                                <i class="fas fa-upload mr-1"></i> Simpan Foto
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelAvatarPreview()">
                                Batal
                            </button>
                        </div>
                    </form>

                    {{-- Hapus foto --}}
                    @if($user->avatar)
                        <form action="{{ route('profil.avatar.delete') }}" method="POST" class="mt-1" id="deleteAvatarForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" style="font-size:0.75rem;"
                                    onclick="if(confirm('Hapus foto profil?')) document.getElementById('deleteAvatarForm').submit();">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Info Ringkas --}}
            <div class="card" style="border-radius:16px;">
                <div class="card-body" style="padding:1.25rem;">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        <strong style="font-size:0.875rem;">Informasi Akun</strong>
                    </div>
                    @php
                        $infoItems = [
                            ['label' => 'Nama',        'value' => $user->name],
                            ['label' => 'Email',       'value' => $user->email],
                            ['label' => 'No. Telepon', 'value' => $user->no_telp ?? '-'],
                            ['label' => 'Alamat',      'value' => $user->alamat  ?? '-'],
                        ];
                        $gudang = $user->getCurrentGudang();
                        if ($gudang) $infoItems[] = ['label' => 'Gudang Aktif', 'value' => $gudang->nama_gudang];
                    @endphp
                    @foreach($infoItems as $i => $info)
                        @if($i > 0)<hr style="margin:.75rem 0;border-color:var(--border-color);">@endif
                        <div>
                            <small class="text-muted d-block" style="font-size:0.7rem;text-transform:uppercase;font-weight:600;letter-spacing:.5px;">{{ $info['label'] }}</small>
                            <span style="font-size:0.875rem;color:var(--text-primary);">{{ $info['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kolom Kanan --}}
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
                                <span style="background:rgba(107,114,128,0.1);color:#6b7280;font-size:0.65rem;padding:2px 7px;border-radius:4px;font-weight:600;margin-left:4px;">
                                    <i class="fas fa-lock" style="font-size:0.55rem;"></i> Hanya Baca
                                </span>
                            </label>
                            <input type="text" id="name" class="form-control" value="{{ $user->name }}"
                                   readonly style="background:#f9fafb;cursor:not-allowed;color:var(--text-muted);">
                            <small class="form-text text-muted">Nama tidak dapat diubah. Hubungi Super Admin jika perlu perubahan.</small>
                        </div>

                        {{-- Email (readonly) --}}
                        <div class="form-group">
                            <label for="email">
                                Email
                                <span style="background:rgba(107,114,128,0.1);color:#6b7280;font-size:0.65rem;padding:2px 7px;border-radius:4px;font-weight:600;margin-left:4px;">
                                    <i class="fas fa-lock" style="font-size:0.55rem;"></i> Hanya Baca
                                </span>
                            </label>
                            <input type="email" id="email" class="form-control" value="{{ $user->email }}"
                                   readonly style="background:#f9fafb;cursor:not-allowed;color:var(--text-muted);">
                        </div>

                        {{-- No Telp --}}
                        <div class="form-group">
                            <label for="no_telp">No. Telepon</label>
                            <input type="tel" id="no_telp" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror"
                                   value="{{ old('no_telp', $user->no_telp) }}"
                                   placeholder="Contoh: 08123456789" maxlength="20">
                            @error('no_telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                                      rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        @foreach([
                            ['id' => 'current_password', 'name' => 'current_password', 'label' => 'Password Saat Ini',       'placeholder' => 'Masukkan password saat ini'],
                            ['id' => 'new_password',     'name' => 'new_password',     'label' => 'Password Baru',            'placeholder' => 'Minimal 8 karakter'],
                            ['id' => 'confirm_password', 'name' => 'new_password_confirmation', 'label' => 'Konfirmasi Password Baru', 'placeholder' => 'Ulangi password baru'],
                        ] as $field)
                            <div class="form-group">
                                <label for="{{ $field['id'] }}">{{ $field['label'] }}</label>
                                <div class="input-group">
                                    <input type="password" id="{{ $field['id'] }}" name="{{ $field['name'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           placeholder="{{ $field['placeholder'] }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-pass" type="button"
                                                data-target="{{ $field['id'] }}"
                                                style="border-radius:0 8px 8px 0;border-color:var(--border-color);">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error($field['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endforeach

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
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    // Avatar file preview before upload
    document.getElementById('avatarFileInput').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreviewImg').src = e.target.result;
            document.getElementById('avatarPreviewBar').style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    function cancelAvatarPreview() {
        document.getElementById('avatarFileInput').value = '';
        document.getElementById('avatarPreviewBar').style.display = 'none';
    }
</script>
@endsection
